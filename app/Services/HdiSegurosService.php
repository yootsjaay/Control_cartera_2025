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
        $compania = Compania::where('slug', $slug)->firstOrFail();

    return Seguro::where('compania_id', $compania->id)->get(['id', 'nombre']);
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
    $datos = [];

    // Extraer número de póliza (cotización)
    if (preg_match('/(Cotización|Póliza):\s*([0-9\-]+)/', $text, $matches)) {
        $datos['numero_poliza'] = trim($matches[2]);
    } else {
        $datos['numero_poliza'] = 'No encontrado';
    }

    // Extraer vigencia inicio
    if (preg_match('/Desde las 12:00 hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/', $text, $matches)) {
        $datos['vigencia_inicio'] = $matches[1];
    } else {
        $datos['vigencia_inicio'] = 'No encontrado';
    }

    // Extraer vigencia fin
    if (preg_match('/Hasta las 12:00 hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/', $text, $matches)) {
        $datos['vigencia_fin'] = $matches[1];
    } else {
        $datos['vigencia_fin'] = 'No encontrado';
    }

    // Extraer nombre del cliente (entre "Nombre:" y "Teléfono:")
    if (preg_match('/\n([A-Z\s]+)\n\s*RFC:/', $text, $matches)) {
        $datos['nombre_cliente'] = trim($matches[1]);
    } else {
        $datos['nombre_cliente'] = 'No encontrado';
    }

    // Extraer RFC
    if (preg_match('/RFC:\s*([A-Z0-9]+)/', $text, $matches)) {
        $datos['rfc'] = $matches[1];
    } else {
        $datos['rfc'] = 'No encontrado';
    }

    // Extraer forma de pago
    if (preg_match('/Detalle de Cuotas por Pagar.*?(\d[\d,]*\.\d{2})/', $text, $matches)) {
        $datos['forma_pago'] = "Una exhibición de " . trim($matches[1]);
    } else {
        $datos['forma_pago'] = 'NO APLICA';
    }

    // Extraer total a pagar (ahora busca también después de "Total a Pagar")
    if (preg_match('/Total a Pagar\s*\n*([\d,]+\.\d{2})/', $text, $matches)) {
        $datos['total_pagar'] = str_replace(',', '', $matches[1]);
    } else {
        $datos['total_pagar'] = 'No encontrado';
    }

    // Extraer agente (número y nombre)
    if (preg_match('/Agente:\s*([0-9]+)\s*([A-Z\s]+)/', $text, $matches)) {
        $datos['numero_agente'] = trim($matches[1]);
        $datos['nombre_agente'] = trim($matches[2]);
    } else {
        $datos['numero_agente'] = 'No encontrado';
        $datos['nombre_agente'] = 'No encontrado';
    }

    // Extraer recibos (ajustando el patrón)
    $pattern = '/(\d{2}-\w{3}-\d{4})\s*al\s*(\d{2}-\w{3}-\d{4})\s*([\d,]+\.\d{2})/';
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
    
    $recibos = [];
    foreach ($matches as $match) {
        $recibos[] = [
            'fecha_pago' => $this->convertirFecha($match[1]),
            'vigencia_inicio' => $this->convertirFecha($match[2]),
            'importe' => floatval(str_replace(',', '', $match[3])),
        ];
    }
    
    $datos['recibos'] = $recibos;

    return $datos;
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
