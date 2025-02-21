<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Exception;
use DateTime;

class HdiSegurosService implements SeguroServiceInterface
{
    // Definir constantes para los slugs de los ramos
    const RAMO_MEDICA_TOTAL_PLUS = 'medica-total-plus';
    const RAMO_VEHICULOS = 'vehiculos-residentes-hdi-autos-y-pick-ups';
    const RAMO_DANIOS = 'danios';
    const RAMO_MEDICA_VITAL = 'medica-vital';

    protected $parser; // Inyecta el parser

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'hdi_seguros') {
            throw new InvalidArgumentException("El seguro seleccionado no pertenece a HDI.");
        }

        if ($ramo->id_seguros != $seguro->id) {
            throw new InvalidArgumentException("El ramo seleccionado no corresponde al seguro proporcionado.");
        }

        try {
            $text = $this->extractText($archivo); // Usa el nuevo método extractText
            \Log::info("Texto extraído:", ['data' => substr($text, 0, 500)]);
            return $this->procesarTexto($text, $ramo);
           //dd($text);
        } catch (Exception $e) {
            \Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF: " . $e->getMessage());
        }
    }

    private function extractText(UploadedFile $archivo): string
    {
        try {
            $pdf = $this->parser->parseFile($archivo->getPathname()); // Usa el parser inyectado
            $text = $pdf->getText();
            return $text;
        } catch (\Exception $e) {
            \Log::error("Error al parsear el PDF: " . $e->getMessage());
            throw new Exception("Error al procesar el PDF.");
        }
    }


    private function procesarTexto(string $text, Ramo $ramo): array
    {
        switch (strtolower($ramo->slug)) {
            case self::RAMO_MEDICA_TOTAL_PLUS:
                return $this->procesarGastosMedicosTotal($text);
            case self::RAMO_MEDICA_VITAL:
                return $this->procesarGastosMedicosVital($text);
            case self::RAMO_VEHICULOS:
                return $this->procesarAutos($text);
            case self::RAMO_CAMIONES_HASTA_3_5:
                return $this->procesarCamionesHasta3_5($text);
            case self::RAMO_DANIOS:
                return $this->procesarDanios($text);
            default:
                throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }

 
    private function procesarAutos(string $text): array
    {
        $datos = [];

        // Nombre del cliente
        if (preg_match('/(?:\n|^)([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+)\nRFC:/i', $text, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]) ?: 'SIN NOMBRE';
        } else {
            $datos['nombre_cliente'] = 'SIN NOMBRE'; // Más corto que "NO ESPECIFICADO"
        }

        // RFC
        $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})/i') ?? 'N/A'; // Cambiado a "N/A"

        // Número de póliza
        $datos['numero_poliza'] = $this->extraerDato($text, '/Póliza:\s*([\d\-]+)/i') ?? 'N/A';

        
        // Vigencia
        if (preg_match('/Vigencia:\s*Desde las \d{2}:\d{2} hrs\. del\s*(\d{2}\/\d{2}\/\d{4})\s*Hasta las \d{2}:\d{2} hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
            $inicio = DateTime::createFromFormat('d/m/Y', $matches[1]);
            $fin = DateTime::createFromFormat('d/m/Y', $matches[2]);
            $datos['vigencia_inicio'] = $inicio ? $inicio->format('Y-m-d') : '0000-00-00';
            $datos['vigencia_fin'] = $fin ? $fin->format('Y-m-d') : '0000-00-00';
        } else {
            $datos['vigencia_inicio'] = '0000-00-00';
            $datos['vigencia_fin'] = '0000-00-00';
        }

        // Forma de pago
        if (preg_match('/(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(EFECTIVO|CHEQUE|TARJETA)?/i', $text, $matches)) {
            $datos['forma_pago'] = trim($matches[0]);
        } else {
            $datos['forma_pago'] = 'N/A';
        }

        // Agente
        if (preg_match('/Agente:\s*(\d+)\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)(?=\n|$)/i', $text, $matches)) {
            $datos['numero_agente'] = trim($matches[1]);
            $datos['nombre_agente'] = trim($matches[2]);
        } else {
            $datos['numero_agente'] = '000000';
            $datos['nombre_agente'] = 'N/A';
        }

        // Total a pagar
        if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
            $datos['total_pagar'] = (float) str_replace(',', '', trim($matches[1]));
        } else {
            preg_match_all('/[\d,]+\.\d{2}/', $text, $matches);
            $ultimo_monto = end($matches[0]);
            $datos['total_pagar'] = $ultimo_monto ? (float) str_replace(',', '', $ultimo_monto) : 0.00;
        }

        return $datos;
       // dd($datos);
    }

    private function extraerDato(string $text, string $pattern, $default = null)
{
    if (preg_match($pattern, $text, $matches)) {
        return trim($matches[1]);
    }
    return $default;
}

private function extraerMonto(string $text, string $pattern): ?float
{
    if (preg_match($pattern, $text, $matches)) {
        return (float) str_replace(',', '', $matches[1]);
    }
    return null;
}
private function formatearFecha(string $fecha): ?string
{
    try {
        return \Carbon\Carbon::createFromFormat('d/M/Y', $fecha)->format('Y-m-d');
    } catch (\Exception $e) {
        return null;
    }
}



    private function procesarGastosMedicosTotal(String $text): array
     {
                $datos = [];
            
                // Mapa de meses en español a números
    $meses = [
        'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
        'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
        'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
    ];
                // Extrae Numero de poliza
                if (preg_match('/Suma Asegurada:\s*(.+)/i', $text, $matches)) {
                    $datos['numero_poliza'] = trim($matches[1]);
                }
            
            // Extraer Fecha y convertir al formato Y-m-d
    if (preg_match('/Las\s+(\d{2}:\d{2})\s+hrs\.\s+del\s+día\s+(\d{1,2}\/\w{3}\/\d{4}).*?Las\s+(\d{2}:\d{2})\s+hrs\.\s+del\s+día\s+(\d{1,2}\/\w{3}\/\d{4})/is', $text, $matches)) {
        // Procesar vigencia_inicio (ej. 22/ENE/2024)
        $fecha_inicio = $matches[2]; // "22/ENE/2024"
        [$dia, $mes, $anio] = explode('/', $fecha_inicio);
        $mes_num = $meses[strtoupper($mes)] ?? '01'; // Fallback a 01 si el mes no coincide
        $fecha_inicio_formateada = "$anio-$mes_num-$dia"; // "2024-01-22"
        $datos['vigencia_inicio'] = $fecha_inicio_formateada;

        // Procesar vigencia_fin (ej. 22/ENE/2025)
        $fecha_fin = $matches[4]; // "22/ENE/2025"
        [$dia, $mes, $anio] = explode('/', $fecha_fin);
        $mes_num = $meses[strtoupper($mes)] ?? '01';
        $fecha_fin_formateada = "$anio-$mes_num-$dia"; // "2025-01-22"
        $datos['vigencia_fin'] = $fecha_fin_formateada;
    }
                
                // Extraer Dirección
                if (preg_match('/Dirección:\s*(.+)/i', $text, $matches)) {
                    $datos['nombre_cliente'] = trim($matches[1]);
                }
            
                // Extraer R.F.C.
                if (preg_match('/R\.F\.C\.\:\s*([A-Z0-9]+)/i', $text, $matches)) {
                    $datos['rfc'] = $matches[1];

                }

                        // Extraer la forma de pago
            $datos['forma_pago'] = $this->extraerDato($text, '/(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/i') ?? 'ANUAL EFECTIVO';

                
                        // Extraer el último monto con formato xx,xxx.xx
            preg_match_all('/[\d,]+\.\d{2}/', $text, $matches);
            $ultimo_monto = end($matches[0]); // Tomar el último valor encontrado
            $datos['total_pagar'] = (float) str_replace(',', '', $ultimo_monto);

            // Extraer número de agente (antes de "Agente:")
            $datos['numero_agente'] = $this->extraerDato($text, '/Oficina:.*\t(\d{6})Agente:/i') ?: '000000'; // "057235"
            
            // Extraer nombre de agente (después de "Agente:")
            $datos['nombre_agente'] = $this->extraerDato($text, '/Agente:\t([A-Za-zÁÉÍÓÚáéíóúñÑ\s]+)/i') ?: 'AGENTE NO ESPECIFICADO'; // "MARICRUZ CASTILLEJOS REYES"   
               return $datos;
//dd($datos);                 
   }

            
            private function procesarGastosMedicosVital(String $text): array
                {
                    $datos = [];
               
                // Extraer número de póliza (capturar dígitos antes de "ZONA")
                $datos['numero_poliza'] = $this->extraerDato($text, '/(\d{6})\s+ZONA/i');

                        
            
                   // Extraer Vigencia completa (Desde y Hasta)
                   if (preg_match('/Las\s+(\d{2}:\d{2})\s+hrs\.\s+del\s+día\s+(\d{1,2}\/\w{3}\/\d{4}).*?Las\s+(\d{2}:\d{2})\s+hrs\.\s+del\s+día\s+(\d{1,2}\/\w{3}\/\d{4})/is', $text, $matches)) {
                 
                    // Procesar vigencia_inicio (ej. 22/ENE/2024)
        $fecha_inicio = $matches[2]; // "22/ENE/2024"
        [$dia, $mes, $anio] = explode('/', $fecha_inicio);
        $mes_num = $meses[strtoupper($mes)] ?? '01'; // Fallback a 01 si el mes no coincide
        $fecha_inicio_formateada = "$anio-$mes_num-$dia"; // "2024-01-22"
        $datos['vigencia_inicio'] = $fecha_inicio_formateada;

        // Procesar vigencia_fin (ej. 22/ENE/2025)
        $fecha_fin = $matches[4]; // "22/ENE/2025"
        [$dia, $mes, $anio] = explode('/', $fecha_fin);
        $mes_num = $meses[strtoupper($mes)] ?? '01';
        $fecha_fin_formateada = "$anio-$mes_num-$dia"; // "2025-01-22"
        $datos['vigencia_fin'] = $fecha_fin_formateada;
                }
                  // Extraer Dirección
                if (preg_match('/Dirección:\s*(.+)/i', $text, $matches)) {
                    $datos['nombre_cliente'] = trim($matches[1]);
                }
            
                // Extraer R.F.C.
                if (preg_match('/R\.F\.C\.\:\s*([A-Z0-9]+)/i', $text, $matches)) {
                    $datos['rfc'] = $matches[1];
                }

                // Extracción del Total y N° Póliza
                preg_match('/Fecha de Efectividad:\s*(\d{1,3}(?:,\d{3})*\.\d{2})/i', $text, $matches);
                $datos['total_pagar'] = isset($matches[1]) ? (float) str_replace(',', '', $matches[1]) : null;
                
               
                  // Extraer la forma de pago
                $datos['forma_pago'] = $this->extraerDato($text, '/(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/i') ?? 'ANUAL EFECTIVO';

                            
         // Extraer número de agente (antes de "Agente:")
    $datos['numero_agente'] = $this->extraerDato($text, '/Oficina:.*\t(\d{6})Agente:/i') ?: '000000'; // "057235"
    
    // Extraer nombre de agente (después de "Agente:")
    $datos['nombre_agente'] = $this->extraerDato($text, '/Agente:\t([A-Za-zÁÉÍÓÚáéíóúñÑ\s]+)/i') ?: 'AGENTE NO ESPECIFICADO'; // "MARICRUZ CASTILLEJOS REYES"   
                  // dd($datos);
                  return $datos;
                }

    private function procesarCamionesHasta3_5(String $text):array{
        $datos =[];

        $dd($text);
    }
}
