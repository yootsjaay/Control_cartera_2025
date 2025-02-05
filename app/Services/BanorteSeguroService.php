<?php
namespace App\Services;

use App\Models\Seguro;
use App\Models\Ramo;
use Smalot\PdfParser\Parser;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Exception;


class BanorteSeguroService implements SeguroServiceInterface
{
    /**
     * Extrae datos del archivo PDF proporcionado.
     */
 
    public function extractToData(UploadedFile $pdfFile): ?array
    {
        try {
            // Parsear el PDF y convertir el contenido a minúsculas
            $pdf  = $this->pdfParser->parseFile($pdfFile->getPathname());
            $text = strtolower($pdf->getText());
    
            // Obtener los seguros disponibles de la base de datos (id => nombre)
            $segurosDisponibles = Seguro::pluck('nombre', 'id')->toArray();
            \Log::info('Seguros en BD:', $segurosDisponibles);
    
            // Buscar en el texto el seguro que coincida con alguno de los disponibles
            $seguroId = $this->buscarEnTexto($text, $segurosDisponibles);
    
            if (!$seguroId) {
                \Log::warning("No se encontró un seguro válido en el PDF.");
                return null;
            }
    
            // Aquí puedes continuar procesando otros datos extraídos del PDF
            return [
                'seguro_id' => $seguroId,
                // Agrega aquí otros datos extraídos
            ];
        } catch (Exception $e) {
            \Log::error("Error extrayendo datos del PDF: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Recorre los seguros disponibles y busca si alguno se menciona en el texto.
     *
     * @param string $text
     * @param array $segurosDisponibles (formato: [id => nombre])
     * @return int|null El ID del seguro encontrado o null si no se encuentra ninguno.
     */
    private function buscarEnTexto(string $text, array $segurosDisponibles): ?int
    {
        foreach ($segurosDisponibles as $id => $nombre) {
            // Compara en minúsculas para mayor robustez
            if (strpos($text, strtolower($nombre)) !== false) {
                return $id;
            }
        }
        return null;
    }
    

    private function esGastoMedicoMayor($text)
    {
        return preg_match('/GASTOS MÉDICOS MAYORES/i', $text);
    }

    private function procesarGastosMedicosMayores($text)
    {
        dd(  [
            'tipo_poliza' => $this->extraerTipoPoliza($text),
            'numero_poliza' => $this->extraerNumeroPoliza($text),
            'vigencia' => $this->extraerVigencia($text),
            'asegurados' => $this->extraerAsegurados($text),
            'suma_asegurada' => $this->extraerSumaAsegurada($text),
            'prima_total' => $this->extraerPrimaTotal($text),
            'coberturas_especiales' => $this->extraerCoberturasEspeciales($text)
        ]);
    }

    // Métodos de extracción específicos para Banorte
    private function extraerTipoPoliza($text)
{
    preg_match('/GASTOS MÉDICOS MAYORES/', $text, $matches);
    return $matches[0] ?? 'Gastos Médicos Mayores';
}

    private function extraerNumeroPoliza($text)
    {
        preg_match('/NO\.\s*DE\s*PÓLIZA[\s:]+(\d+)/i', $text, $matches);
        return $matches[1] ?? null;
    }

    private function extraerVigencia($text)
    {
        preg_match('/VIGENCIA\s+DESDE.*?(\d{2}\/\d{2}\/\d{4}).*?(\d{2}\/\d{2}\/\d{4})/s', $text, $matches);
        
        try {
            $inicio = Carbon::createFromFormat('d/m/Y', $matches[1])->format('Y-m-d');
        } catch (\Exception $e) {
            $inicio = null;
        }
        return [
            'inicio' => $matches[1] ?? null,
            'fin' => $matches[2] ?? null
        ];
    }

    private function extraerAsegurados($text)
    {
        preg_match_all('/([A-ZÁÉÍÓÚÑ\s]+)\t(\d{2}-\d{2}-\d{4})\s+(\d+)\s+([\d,]+)\s+M\.N\./', $text, $matches, PREG_SET_ORDER); 
        
        $asegurados = [];
        foreach ($matches as $match) {
            $asegurados[] = [
                'nombre' => trim($match[1] ?? ''),
                'fecha_nacimiento' => $match[2] ?? null,
                'edad' => isset($match[3]) ? (int) $match[3] : null,
                'suma_asegurada' => isset($match[4]) ? str_replace(',', '', $match[4]) : null
            ];
        }
        return $asegurados;
    }

    private function extraerSumaAsegurada($text)
{
    preg_match('/SUMA ASEGURADA.*?([\d,]+)\s+M\.N\./s', $text, $matches);
    return isset($matches[1]) ? str_replace(',', '', $matches[1]) : null;
}

    private function extraerPrimaTotal($text)
    {
        preg_match('/Prima Total[\s:\$]+([\d,\.]+)/', $text, $matches);
        return isset($matches[1]) ? str_replace(',', '', $matches[1]) : null;
    }

    private function extraerCoberturasEspeciales($text)
    {
        $coberturas = [];
        
        // Cobertura Dental
        preg_match('/Cobertura Dental.*?Límite anual \$([\d,]+)/s', $text, $dental);
        if (!empty($dental[1])) {
            $coberturas['dental'] = [
                'limite_anual' => str_replace(',', '', $dental[1]),
                'copago' => '20%'
            ];
        }

        // Cobertura Visión
        preg_match('/Cobertura Visión.*?Límite anual \$([\d,]+)/s', $text, $vision);
        if (!empty($vision[1])) {
            $coberturas['vision'] = [
                'limite_anual' => str_replace(',', '', $vision[1]),
                'deducible' => '$280 + IVA'
            ];
        }

        return $coberturas;
    }

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
}
