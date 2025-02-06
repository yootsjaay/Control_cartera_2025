<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Carbon\Carbon;
use Exception;

class BanorteSeguroService implements SeguroServiceInterface
{
    /**
     * Extrae los datos específicos de un archivo PDF para Banorte,
     * validando que el seguro y el ramo correspondan a esta compañía.
     *
     * @param UploadedFile $archivo Archivo PDF subido.
     * @param Seguro $seguro Datos del seguro seleccionado.
     * @param Ramo $ramo Datos del ramo seleccionado.
     * @return array Datos extraídos del PDF.
     *
     * @throws InvalidArgumentException Si las validaciones fallan.
     */
    /**
     * Obtiene los seguros disponibles para Banorte.
     */
    public function getSeguros()
    {
        return Seguro::where('compania_id', 1)  // Asumiendo que Banorte tiene ID 1
            ->get(['id', 'nombre']);
    }

    /**
     * Obtiene los ramos disponibles para un seguro específico de Banorte.
     */
    public function getRamos($seguroId)
    {
        return Ramo::where('id_seguros', $seguroId)
            ->get(['id', 'nombre_ramo']);
    }
    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
{
    if ($seguro->compania->slug !== 'banorte') {
        throw new InvalidArgumentException("El seguro seleccionado no pertenece a Banorte.");
    }

    if ($ramo->id_seguros != $seguro->id) {
        throw new InvalidArgumentException("El ramo seleccionado no corresponde al seguro proporcionado.");
    }

    try {
        // Procesar el PDF
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($archivo->getPathname());

        // Validar si el PDF tiene contenido
        $text = trim($pdf->getText());
        if (empty($text)) {
            throw new InvalidArgumentException("El PDF no contiene texto legible.");
        }

        \Log::info("Texto extraído del PDF:", ['data' => substr($text, 0, 500)]);

        // **Llamamos al método específico según el ramo**
        return $this->procesarTexto($text, $ramo);

    } catch (Exception $e) {
        \Log::error("Error al procesar el PDF: " . $e->getMessage());
        throw new InvalidArgumentException("No se pudo procesar el archivo PDF.");
    }
}



    private function procesarTexto(string $text, Ramo $ramo): array
    {
        switch (strtolower($ramo->slug)) {
            case 'gastos-medicos':
                return $this->procesarGastosMedicos($text);
            case 'autos':
                return $this->procesarAutos($text);
            case 'danios':
                return $this->procesarDanios($text);
            default:
                throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }
    


private function procesarGastosMedicos(string $text): array
{
    $datosExtraidos = [];

    //  Extraer número de póliza
    if (preg_match('/NO\.\s*DE\s*PÓLIZA[\s:]+(\d+)/i', $text, $match)) {
        $datosExtraidos['numero_poliza'] = $match[1];
    }

    //  Extraer fechas de vigencia
    if (preg_match('/VIGENCIA\s+DESDE.*?(\d{2}\/\d{2}\/\d{4}).*?(\d{2}\/\d{2}\/\d{4})/s', $text, $match)) {
        try {
            $datosExtraidos['fecha_inicio'] = Carbon::createFromFormat('d/m/Y', $match[1])->format('Y-m-d');
            $datosExtraidos['fecha_fin'] = Carbon::createFromFormat('d/m/Y', $match[2])->format('Y-m-d');
        } catch (Exception $e) {
            $datosExtraidos['fecha_inicio'] = null;
            $datosExtraidos['fecha_fin'] = null;
        }
    }

    //  Extraer información del asegurado
    if (preg_match_all('/([A-ZÁÉÍÓÚÑ\s]+)\t(\d{2}-\d{2}-\d{4})\s+(\d+)\s+([\d,]+)\s+M\.N\./', $text, $matches, PREG_SET_ORDER)) {
        $asegurados = [];
        foreach ($matches as $match) {
            $asegurados[] = [
                'nombre' => trim($match[1] ?? ''),
                'fecha_nacimiento' => $match[2] ?? null,
                'edad' => isset($match[3]) ? (int) $match[3] : null,
                'suma_asegurada' => isset($match[4]) ? str_replace(',', '', $match[4]) : null
            ];
        }
        $datosExtraidos['asegurados'] = $asegurados;
    }

    //  Verifica si se extrajo algo, si no, lanza error
    if (empty($datosExtraidos)) {
        throw new InvalidArgumentException("No se encontraron datos relevantes en el PDF.");
    }

    return $datosExtraidos;
}

    
}
