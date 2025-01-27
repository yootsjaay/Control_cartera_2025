<?php
namespace App\Services;

use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
class HdiSegurosService implements SeguroServiceInterface
{
    public function extractToData($pdfFile)
    {
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($pdfFile->getPathname());

        // Extraer el texto del PDF
        $text = $pdf->getText();
       // Identificar el seguro o ramo
        if (stripos($text, 'HDI Autos') !== false) {
            // Procesar seguro de autos
            return $this->procesarAutos($text);
        } elseif (stripos($text, 'Gastos Médicos') !== false) {
            // Procesar seguro de gastos médicos
            return $this->procesarGastosMedicos($text);
        } elseif (stripos($text, 'Daños Materiales') !== false) {
            // Procesar seguro de daños
            return $this->procesarDaniosMateriales($text);
        } else {
            // Seguro o ramo no identificado
            return [
                'error' => 'No se pudo identificar el seguro o ramo en el documento.'
            ];
        }

      
      return $extraerDatos;  
      //return $this->procesarAutos($text);
    }

    private function procesarAutos($text){
        $datos= [];
        // Extraer número de póliza
        if (preg_match('/Póliza:\s*([0-9\-]+)/', $text, $matches)) {
            $datos['numero_poliza'] = trim($matches[1]);
        } elseif (preg_match('/Cotización:\s*(\d+)/i', $text, $matches)) {
            // Si no hay número de póliza, usar el número de cotización
            $datos['numero_poliza'] = 'Cotización: ' . trim($matches[1]);
        } else {
            $datos['numero_poliza'] = 'No encontrado';
        }

        // Extraer Cotización
        if (preg_match('/Cotización:\s*(\d+)/i', $text, $matches)) {
            $datos['cotizacion'] = $matches[1];
        } else {
            $datos['cotizacion'] = 'No encontrado';
        }

        // Extraer nombre del cliente
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
    
       
        // Forma de pago
        $formas_pago = ['SEMESTRAL EFECTIVO', 'TRIMESTRAL EFECTIVO', 'ANUAL EFECTIVO', 'MENSUAL EFECTIVO'];
        foreach ($formas_pago as $forma) {
            if (preg_match('/' . preg_quote($forma, '/') . '/i', $text)) {
                $datos['forma_pago'] = $forma;
                break;
            }
        }
        if (!isset($datos['forma_pago'])) {
            $datos['forma_pago'] = 'NO APLICA';
        }
    
        // Extraer el total a pagar
        if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
            $datos['total_a_pagar'] = trim($matches[1]);
        } else {
            $datos['total_a_pagar'] = 'No encontrado';
        }
    
        // Extraer agente (número y nombre)
        // Extraer Número de Agente y Nombre del Agente
        if (preg_match('/Agente:\s*(\d+)\s+(.+)/i', $text, $matches)) {
            $datos['numero_agente'] = trim($matches[1]); // Captura el número del agente
            $datos['nombre_agente'] = trim($matches[2]); // Captura el nombre del agente
        }

    
        //NUEVAS : 
         // Extraer el Ramo
         if (preg_match('/Ramo:\s*(.+)/i', $text, $matches)) {
            $datos['ramo'] = trim($matches[1]);
        }

      
        // Extraer Fecha de Cotización
        if (preg_match('/Fecha de Cotización:\s*(.+)/i', $text, $matches)) {
            $datos['fecha_cotizacion'] = trim($matches[1]);
        }

     /*   // Extraer Agente
        if (preg_match('/Agente:\s*(.+)/i', $text, $matches)) {
            $datos['agente'] = trim($matches[1]);
        }*/

        // Extraer Oficina
        if (preg_match('/Oficina:\s*(.+)/i', $text, $matches)) {
            $datos['oficina'] = trim($matches[1]);
        }

        // Extraer Vigencia (Desde y Hasta)
        if (preg_match('/Desde las 12:00 hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
            $datos['vigencia_desde'] = $matches[1];
        }
        if (preg_match('/Hasta las 12:00 hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
            $datos['vigencia_hasta'] = $matches[1];
        }

        // Extraer Paquete
        if (preg_match('/Paquete:\s*(.+)/i', $text, $matches)) {
            $datos['paquete'] = trim($matches[1]);
        }

        // Extraer Prima Neta
        if (preg_match('/Prima Neta\s+([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['prima_neta'] = str_replace(',', '', $matches[1]);
        }

        // Extraer Total a Pagar
        if (preg_match('/Total a Pagar\s+([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_a_pagar'] = str_replace(',', '', $matches[1]);
        }

        // Extraer Tipo de Suma
        if (preg_match('/Tipo Suma:\s*(.+)/i', $text, $matches)) {
            $datos['tipo_suma'] = trim($matches[1]);
        }

        // Extraer Serie
        if (preg_match('/Serie:\s*(.+)/i', $text, $matches)) {
            $datos['serie'] = trim($matches[1]);
        }

        // Extraer Descripción del Vehículo
        if (preg_match('/REGULARIZADO,\s*(.+),/i', $text, $matches)) {
            $datos['vehiculo'] = trim($matches[1]);
        }
    
        // Retornar todos los datos extraídos
        return $datos;
    }
            private function extraerDatosHdiGastos($text) {
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

            
                dd($datos);
                return $datos;
            }
}
