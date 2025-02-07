<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Carbon\Carbon;
use Exception;

class HdiSegurosService implements SeguroServiceInterface
{
    public function getSeguros()
    {
        return Seguro::where('compania_id', 1)  
            ->get(['id', 'nombre']);
    }

    /**
     * Obtiene los ramos disponibles para un seguro específico de HDI.
     */
    public function getRamos($seguroId)
    {
        return Ramo::where('id_seguros', $seguroId)
            ->get(['id', 'nombre_ramo']);
    }
  
    // Método para extraer datos del PDF
    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'hdi_seguros') {
            throw new InvalidArgumentException("El seguro seleccionado no pertenece a HDI.");
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

            // Llamamos al método específico según el ramo
            return $this->procesarTexto($text, $ramo);

        } catch (Exception $e) {
            \Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF.");
        }
    }

    private function procesarTexto(string $text, Ramo $ramo): array
    {
        // Normalizamos el slug a minúsculas y lo comparamos con valores estandarizados
        $slug = strtolower($ramo->slug);

        switch ($slug) {
            case 'medica-total-plus':
                return $this->procesarGastosMedicosTotal($text);
            case 'vehiculos-residentes-hdi-autos-y-pick-ups': // Usar un slug estandarizado en BD
                return $this->procesarAutos($text);
            case 'danios':
                return $this->procesarDanios($text);
            default:
                throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }

    private function procesarAutos(string $text): array
    {
        \Log::info('Procesando autos HDI...');
        
        return [
            'mensaje' => 'Procesando autos HDI',
            'datos' => []
        ];
    }






    private function procesarGastosMedicosTotal(String $text): array
     {
                $datos = [];
            
                // Extrae Numero de poliza
                if (preg_match('/Suma Asegurada:\s*(.+)/i', $text, $matches)) {
                    $datos['numero_de_poliza'] = trim($matches[1]);
                }
            
            
                // Extraer Vigencia completa (Desde y Hasta)
                if (preg_match('/Desde:\s*(.+)\nHasta:\s*(.+)/i', $text, $matches)) {
                    $datos['vigencia'] = [
                        'desde' => trim($matches[1]),
                        'hasta' => trim($matches[2]),
                    ];
                }

                
                // Extraer Dirección
                if (preg_match('/Dirección:\s*(.+)/i', $text, $matches)) {
                    $datos['contratante'] = trim($matches[1]);
                }
            
                // Extraer R.F.C.
                if (preg_match('/R\.F\.C\.\:\s*([A-Z0-9]+)/i', $text, $matches)) {
                    $datos['rfc'] = $matches[1];
                }
                    // Extraer el monto de Pagos Subsecuentes
        if (preg_match('/([\d,]+\.\d{2})\s*Pagos Subsecuentes/i', $text, $matches)) {
            $datos['pagos_subsecuentes'] = str_replace(',', '', $matches[1]); // Convertir a número sin comas
        }
            
        
        // Extraer Pagos Subsecuentes (monto relacionado con fechas específicas)
        if (preg_match('/(\d{2}\/[A-Z]{3}\/\d{4})\s+(\d{2}\/[A-Z]{3}\/\d{4})\s+([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['fecha_inicio_pago_subsecuente'] = $matches[1]; // Primera fecha (inicio)
            $datos['fecha_fin_pago_subsecuente'] = $matches[2];   // Segunda fecha (fin)
            $datos['monto_pago_subsecuente'] = str_replace(',', '', $matches[3]); // Monto sin comas
        }

            
                
                return $datos;
            }
}
