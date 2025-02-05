<?php
namespace App\Services;

use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;

class HdiSegurosService implements SeguroServiceInterface
{
    public function getSeguros()
    {
        // Obtiene todos los seguros de la base de datos que pertenecen a Banorte
        return Seguro::where('compania_id', 1) 
                    ->get(['id', 'nombre']);
    }

    /**
     * Obtiene los ramos disponibles para un seguro específico de Banorte.
     */
    public function getRamos($seguroId)
    {
        // Obtienes los ramos relacionados con el seguro seleccionado
        return Ramo::where('id_seguros', $seguroId)
            ->get(['id', 'nombre_ramo']);
    }
  
    // Método para extraer datos del PDF
    public function extractToData($pdfFile)
    {
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($pdfFile->getPathname());

        // Extraer el texto del PDF
        $texto = $pdf->getText();

        // Aquí puedes hacer el procesamiento necesario del texto extraído
        return $texto;  // O alguna lógica adicional según la compañía
    }


    private function procesarAutos($text, $seguro, $ramo)
    {
        $datos = [];
        
        // Método para extraer datos con validación
        $datos['numero_poliza'] = $this->extraerDato($text, '/Póliza:\s*([0-9\-]+)/', 'Cotización');
        $datos['cotizacion'] = $this->extraerDato($text, '/Cotización:\s*(\d+)/i', 'No encontrado');
        $datos['nombre_cliente'] = $this->extraerDato($text, '/\n([A-Z\s]+)\n\s*RFC:/', 'No encontrado');
        $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]+)/', 'No encontrado');
        
        // Forma de pago
        $formas_pago = ['SEMESTRAL EFECTIVO', 'TRIMESTRAL EFECTIVO', 'ANUAL EFECTIVO', 'MENSUAL EFECTIVO'];
        $datos['forma_pago'] = $this->extraerFormaPago($text, $formas_pago);
    
        // Total a pagar
        $datos['total_a_pagar'] = $this->extraerDato($text, '/([0-9,]+\.\d{2})\s*Total a Pagar/', 'No encontrado');
    
        // Agente
        list($datos['numero_agente'], $datos['nombre_agente']) = $this->extraerAgente($text);
    
        // Nuevos campos
        $datos['ramo'] = $this->extraerDato($text, '/Ramo:\s*(.+)/i', 'No encontrado');
        $datos['fecha_cotizacion'] = $this->extraerDato($text, '/Fecha de Cotización:\s*(.+)/i', 'No encontrado');
        $datos['oficina'] = $this->extraerDato($text, '/Oficina:\s*(.+)/i', 'No encontrado');
        
        // Vigencia
        $datos['vigencia_desde'] = $this->extraerDato($text, '/Desde las 12:00 hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/i', 'No encontrado');
        $datos['vigencia_hasta'] = $this->extraerDato($text, '/Hasta las 12:00 hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/i', 'No encontrado');
        
        // Paquete
        $datos['paquete'] = $this->extraerDato($text, '/Paquete:\s*(.+)/i', 'No encontrado');
    
        // Prima Neta
        $datos['prima_neta'] = $this->extraerDato($text, '/Prima Neta\s+([\d,]+\.\d{2})/i', 'No encontrado');
        
        // Tipo de Suma
        $datos['tipo_suma'] = $this->extraerDato($text, '/Tipo Suma:\s*(.+)/i', 'No encontrado');
    
        // Serie
        $datos['serie'] = $this->extraerDato($text, '/Serie:\s*(.+)/i', 'No encontrado');
    
        // Vehículo
        $datos['vehiculo'] = $this->extraerDato($text, '/REGULARIZADO,\s*(.+),/i', 'No encontrado');
    
        return $datos;
    }
    
    // Método para extraer datos con una expresión regular
    private function extraerDato($text, $pattern, $default)
    {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        return $default;
    }
    
    // Método para extraer la forma de pago
    private function extraerFormaPago($text, $formas_pago)
    {
        foreach ($formas_pago as $forma) {
            if (preg_match('/' . preg_quote($forma, '/') . '/i', $text)) {
                return $forma;
            }
        }
        return 'NO APLICA';
    }
    
    // Método para extraer datos del agente
    private function extraerAgente($text)
    {
        if (preg_match('/Agente:\s*(\d+)\s+(.+)/i', $text, $matches)) {
            return [trim($matches[1]), trim($matches[2])];
        }
        return ['No encontrado', 'No encontrado'];
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
