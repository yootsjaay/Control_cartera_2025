<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Exception;

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

    // Normalizar texto: eliminar múltiples espacios y saltos de línea
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    // Nombre del cliente (mejorado)
    if (preg_match('/(.*?)\nRFC:/', $text, $matches)) { // Busca el nombre antes de "RFC:"
        $datos['nombre_cliente'] = trim($matches[1]);
    }

    // RFC
    $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})/i');

    // Número de póliza
    $datos['numero_poliza'] = $this->extraerDato($text, '/Póliza:\s*([\d\-]+)/i');

    // Vigencia (mejorado)
    if (preg_match('/Vigencia:\s*Desde las \d{2}:\d{2} hrs\. del\s*(\d{2}\/\d{2}\/\d{4})\s*Hasta las \d{2}:\d{2} hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/', $text, $matches)) {
        $datos['vigencia_inicio'] = $this->formatearFecha($matches[1]);
        $datos['vigencia_fin'] = $this->formatearFecha($matches[2]);
    }

    // Forma de pago (mejorado)
    if (preg_match('/(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(EFECTIVO|CHEQUE|TARJETA)?/i', $text, $matches)) {
        $datos['forma_pago'] = trim($matches[0]); // Toma la coincidencia completa
    } else {
        $datos['forma_pago'] = 'NO ESPECIFICADA'; // Valor por defecto si no se encuentra
    }

    // Agente (mejorado)
    if (preg_match('/Agente:\s*(\d+)\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)/i', $text, $matches)) {
        $datos['numero_agente'] = trim($matches[1]);
        $datos['nombre_agente'] = trim($matches[2]);
    }

    
   
      // Extraer el total a pagar
      if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
        $datos['total_pagar'] = trim($matches[1]);
    } else {
        $datos['total_pagar'] = 'No encontrado';
    }
    return $datos;
    //dd($datos);
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

            
                
                //return $datos;
      //  dd($datos);
            }

            
            private function procesarGastosMedicosVital(string $text): array
            {
                // Normalizar el texto
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);
            
                $datos = [];
            
                // Extraer número de póliza
                if (preg_match('/Suma Asegurada:\s*(.+)/i', $text, $matches)) {
                    $datos['numero_de_poliza'] = trim($matches[1]);
                }
            
                // Extraer RFC
                $datos['rfc'] = $this->extraerDato($text, '/R\.F\.C\.:\s*([A-Z0-9]{12,13})/i');
            
                // Extraer nombre del contratante (ajustado para evitar capturar "Dirección")
                $datos['nombre_cliente'] = $this->extraerDato($text, '/Nombre del Contratante:\s*([A-Za-zÁÉÍÓÚáéíóúñÑ\s\.\-]+)\s+\d{2}\/[A-Z]{3}\/\d{4}/i');
            
                // Extraer vigencia (desde y hasta)
                $datos['vigencia_inicio'] = $this->formatearFecha($this->extraerDato($text, '/Desde:\s*Las\s+\d{2}:\d{2}\s+hrs\.\s+del\s+día\s+(\d{2}\/[A-Z]{3}\/\d{4})/i'));
                $datos['vigencia_fin'] = $this->formatearFecha($this->extraerDato($text, '/Hasta:\s*Las\s+\d{2}:\d{2}\s+hrs\.\s+del\s+día\s+(\d{2}\/[A-Z]{3}\/\d{4})/i'));
            
                // Extraer agente (número y nombre)
                $datos['numero_agente'] = $this->extraerDato($text, '/Agente:\s*(\d+)/i');
                $datos['nombre_agente'] = $this->extraerDato($text, '/Agente:\s*\d+\s+([A-Za-zÁÉÍÓÚáéíóúñÑ\s\.\-]+)/i');
            
                // Extraer forma de pago
                $datos['forma_pago'] = $this->extraerDato($text, '/(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/i') ?? 'ANUAL EFECTIVO';
            
                // Extraer total a pagar
                $datos['total_pagar'] = $this->extraerMonto($text, '/Total\s+([\d,]+\.\d{2})/i');
            
                // Extraer suma asegurada
                $datos['suma_asegurada'] = $this->extraerMonto($text, '/Suma Asegurada:\s*([\d,]+\.\d{2})/i');
            
                // Extraer deducible
                $datos['deducible'] = $this->extraerDato($text, '/Deducible contratado:\s*([\d%]+)/i');
            
                //return $datos;

                dd($datos);
            }

    private function procesarCamionesHasta3_5(String $text):array{
        $datos =[];


        $dd($text);
    }
}
