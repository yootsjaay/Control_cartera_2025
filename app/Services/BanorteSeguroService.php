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
        $compania = Compania::where('slug', $slug)->firstOrFail();

    return Seguro::where('compania_id', $compania->id)->get(['id', 'nombre']);
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
        //dd($text);

    } catch (Exception $e) {
        \Log::error("Error al procesar el PDF: " . $e->getMessage());
        throw new InvalidArgumentException("No se pudo procesar el archivo PDF.");
    }
}



    private function procesarTexto(string $text, Ramo $ramo): array
    {
        switch (strtolower($ramo->slug)) {
            case 'gastos-medicos-mayores':
                return $this->procesarGastosMedicos($text);
            case 'automoviles-residentes':
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
private function procesarAutos(string $text): array
{
    $datos = [
        'numero_poliza'   => null,
        'vigencia_inicio' => null,
        'vigencia_fin'    => null,
        'forma_pago'      => null,
        'total_a_pagar'   => null,
        'archivo_pdf'     => null, // Este se llenará con el nombre del archivo en otro punto del proceso
        'status'          => 'ACTIVO', // Asumo que si se está procesando es porque está activo
        'cliente_id'      => null,
        'compania_id'     => null,
        'seguro_id'       => null,
    ];

    // Número de póliza
    if (preg_match('/NO\.\s*DE\s*PÓLIZA[\s:]+(\d+)/i', $text, $match)) {
        $datosExtraidos['numero_poliza'] = $match[1];
    }


    // Vigencia inicio
    if (preg_match('/Vigencia\s*Inicio:\s*([\d\/-]+)/i', $text, $match)) {
        $datos['vigencia_inicio'] = $match[1] ?? null;
    }

    // Vigencia fin
    if (preg_match('/Vigencia\s*Fin:\s*([\d\/-]+)/i', $text, $match)) {
        $datos['vigencia_fin'] = $match[1] ?? null;
    }

    // Forma de pago
    if (preg_match('/Forma de Pago:\s*([^\n]+)/i', $text, $match)) {
        $datos['forma_pago'] = trim($match[1]) ?? null;
    }

    // Total a pagar
    if (preg_match('/Total a Pagar:\s*\$?([\d,]+\.\d{2})/i', $text, $match)) {
        $datos['total_a_pagar'] = (float) str_replace(',', '', $match[1]) ?? null;
    }

    // Cliente ID (Podría requerir una búsqueda en la BD según el nombre del contratante)
    if (preg_match('/Nombre del Contratante:\s*([^\n]+)/', $text, $match)) {
        $datos['cliente_id'] = trim($match[1])?? null;
    }

    // Compañía de seguros (Podría requerir búsqueda en la BD)
    if (preg_match('/Compañía de Seguros:\s*([^\n]+)/', $text, $match)) {
        $datos['compania_id'] = trim($match[1])?? null;
    }

    // Tipo de seguro (Podría requerir búsqueda en la BD)
    if (preg_match('/Tipo de Seguro:\s*([^\n]+)/', $text, $match)) {
        $datos['seguro_id'] = trim($match[1]) ?? null;
    }

    return $datos;
}



    
}
