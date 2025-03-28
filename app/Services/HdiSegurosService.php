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
    protected $parser; // Inyecta el parser

    public function __construct(Parser $parser)
    { 
        $this->parser = $parser;
    }

    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        // Validaciones de seguro y ramo
        $this->validarSeguroYramo($seguro, $ramo);

        try {
            $text = $this->extractText($archivo); // Usa el nuevo método extractText
            Log::info("Texto extraído:", ['data' => substr($text, 0, 500)]);
            return $this->procesarTexto($text, $ramo);
        } catch (Exception $e) {
            Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF: " . $e->getMessage());
        }
    }


    private function extractText(UploadedFile $archivo): string
    {
        try {
            $pdf = $this->parser->parseFile($archivo->getPathname()); // Usa el parser inyectado
            return $pdf->getText();
        } catch (\Exception $e) {
            Log::error("Error al parsear el PDF: " . $e->getMessage());
            throw new Exception("Error al procesar el PDF.");
        }
    }
    private function procesarTexto(string $text, Ramo $ramo): array
    {
        // Obtener datos comunes a todos los ramos
        $datosComunes = $this->procesarDatosComunes($text);
    
        switch (strtolower($ramo->nombre)) {
            case 'automóviles':
                $datosEspecificos = $this->procesarAutos($text);
                break;
            case 'accidentes y enfermedades':
                $datosEspecificos = $this->procesarGastosMedicosTotal($text);
                break;
            case 'daños':
                $datosEspecificos = $this->procesarCivilViajero($text);
                break;
            default:
                throw new InvalidArgumentException("El ramo {$ramo->nombre} no tiene un procesador definido.");
        }
    
        // Combina los datos comunes con los específicos
        return array_merge($datosComunes, $datosEspecificos);
    }
    
    private function procesarDatosComunes(string $text): array
    {
        $datos = [];
    
        // Nombre del cliente
        if (preg_match('/Cliente:\s*\d+\s*Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=\n\s*Domicilio Fiscal:)/i', $text, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]) ?: 'SIN NOMBRE';
        } elseif (preg_match('/Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)\s*(?=\n\s*Domicilio Fiscal:)/i', $text, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]) ?: 'SIN NOMBRE';
        } else {
            $datos['nombre_cliente'] = 'SIN NOMBRE';
        }
    
        // RFC
        $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]+)/i') ?? 'N/A';
    
        // Número de póliza
        if (preg_match('/No\.\s*(?:de\s*)?Póliza:\s*([\d\s\-]+)/i', $text, $matches)) {
            $datos['numero_poliza'] = preg_replace('/\s+/', '', $matches[1]);
        } else {
            $datos['numero_poliza'] = 'N/A';
        }
    
        // Vigencia
        if (preg_match('/Vigencia:.*?(\d{2}[\/\-]\w{3}[\/\-]\d{4}).*?(\d{2}[\/\-]\w{3}[\/\-]\d{4})/si', $text, $matches)) {
            $meses = [
                'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
                'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
                'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
            ];
            $fechaInicio = strtr($matches[1], $meses);
            $fechaFin = strtr($matches[2], $meses);
            $datos['vigencia_inicio'] = date_create_from_format('d/m/Y', $fechaInicio)->format('Y-m-d') ?? null;
            $datos['vigencia_fin'] = date_create_from_format('d/m/Y', $fechaFin)->format('Y-m-d') ?? null;
        } else {
            $datos['vigencia_inicio'] = null;
            $datos['vigencia_fin'] = null;
        }
    
        // Forma de pago
        if (preg_match('/(?:Forma\s+de\s+Pago:\s*|Forma de pago:\s*)?(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(EFECTIVO|CHEQUE|TARJETA)?/i', $text, $matches)) {
            $datos['forma_pago'] = trim($matches[1] . ' ' . ($matches[2] ?? '')) ?: 'N/A';
        } else {
            $datos['forma_pago'] = 'N/A';
        }
    
        // Total a pagar
        $datos['total_pagar'] = $this->extraerTotalPagar($text);
    
        return $datos;
    }
    
    
    private function procesarCivilViajero(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Datos específicos de Civil Viajero
        $datos['numero_agente'] = $this->extraerDatos($text, '/Clave:\s*(\d+)/i') ?: '000000';
        // Nombre del agente (antes de "Datos de la póliza:")
        $datos['nombre_agente'] = $this->extraerDatos($text, '/Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=[\s\S]*Datos de la póliza:)/i', fn($match) => trim($match)) ?: 'AGENTE NO ESPECIFICADO';
    
        dd($datos); // Depuración activa
        //return $datos;
    }
    
    private function procesarEmbarcaciones(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Datos específicos de Embarcaciones
        $datos['numero_agente'] = $this->extraerDatos($text, '/Clave:\s*(\d+)/i') ?: 'N/A';
        $datos['nombre_agente'] = $this->extraerDatos($text, '/Clave:\s*\d+\s*Nombre:\s*([^\n]+)/i', $this->limpiarTexto(...)) ?: 'AGENTE NO ESPECIFICADO';
    
        dd($datos); // Depuración activa
        //return $datos;
    }
    
    private function extraerTotalPagar(string $text): float
    {
        if (preg_match('/Prima Total\s*([\d,]+\.\d{2})/i', $text, $matches)) {
            return (float) str_replace(',', '', trim($matches[1]));
        }
    
        if (preg_match('/Desglose de Pagos:\s*Pago Único de \$([\d,]+\.\d{2})/i', $text, $matches)) {
            return (float) str_replace(',', '', trim($matches[1]));
        }
    
        preg_match_all('/[\d,]+\.\d{2}/', $text, $matches);
        $ultimo_monto = end($matches[0]);
        return $ultimo_monto ? (float) str_replace(',', '', $ultimo_monto) : 0.00;
    }
    
    private function extraerDatos(string $text, string $pattern, ?callable $processor = null): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            $value = $matches[1] ?? null;
            return $processor ? $processor($value) : $value;
        }
        return null;
    }


    private function procesarEquipoMaquinaria(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Datos específicos de Equipo y Maquinaria
        if (preg_match('/Clave:\s*(\d+)\s*Nombre:\s*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+)(?=\n|$)/i', $text, $matches)) {
            $datos['numero_agente'] = trim($matches[1]);
            $datos['nombre_agente'] = trim($matches[2]);
        } else {
            $datos['numero_agente'] = '000000';
            $datos['nombre_agente'] = 'N/A';
        }
    
        // Ajuste específico para RFC (si se requiere longitud 12-13)
        $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})/i') ?? $datos['rfc'];
    
        // Ajuste específico para Total a Pagar (prioriza "Total a Pagar")
        if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
            $datos['total_pagar'] = (float) str_replace(',', '', trim($matches[1]));
        }
    
        return $datos;
       //dd($datos);
    }
    private function procesarHdiCasa(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Datos específicos de HDI Casa
        if (preg_match('/Clave:\s*(\d+)\s*Nombre:\s*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+)(?=\n|$)/i', $text, $matches)) {
            $datos['numero_agente'] = trim($matches[1]);
            $datos['nombre_agente'] = trim($matches[2]);
        } else {
            $datos['numero_agente'] = '000000';
            $datos['nombre_agente'] = 'N/A';
        }
    
        // Ajuste para vigencia (usa '0000-00-00' en lugar de null)
        if ($datos['vigencia_inicio'] === null) {
            $datos['vigencia_inicio'] = '0000-00-00';
            $datos['vigencia_fin'] = '0000-00-00';
        }
    
        // Ajuste específico para total_pagar (prioriza "Prima Total $...")
        if (preg_match('/Prima Total\s*\$([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_pagar'] = (float) str_replace(',', '', trim($matches[1]));
        }
    
        return $datos;
    }
    private function procesarHdiEmpresa(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Datos específicos de HDI Empresa
        if (preg_match('/Clave:\s*(\d+)\s*Nombre:\s*([A-ZÁÉÍÓÚÑa-záéíóúñ\s]+)(?=\n|$)/i', $text, $matches)) {
            $datos['numero_agente'] = is_numeric($matches[1]) ? trim($matches[1]) : '000000';
            $datos['nombre_agente'] = trim($matches[2]);
        } else {
            $datos['numero_agente'] = '000000';
            $datos['nombre_agente'] = 'N/A';
        }
    
        // Ajuste específico para RFC (valida 12-13 caracteres)
        $rfc = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})/i');
        $datos['rfc'] = $rfc && preg_match('/^[A-Z0-9]{12,13}$/', $rfc) ? $rfc : $datos['rfc'];
    
        // Ajuste específico para número de póliza (valida formato)
        $numeroPoliza = $this->extraerDato($text, '/No\. de Póliza:\s*([\d\s\-]+)/i');
        $datos['numero_poliza'] = $numeroPoliza && preg_match('/^[\d\s\-]+$/', $numeroPoliza) ? preg_replace('/\s+/', '', $numeroPoliza) : $datos['numero_poliza'];
    
        // Ajuste específico para total_pagar (prioriza "Prima Total $" y fallback a "$...")
        if (preg_match('/Prima Total\s*\$([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_pagar'] = (float) str_replace(',', '', trim($matches[1]));
        } elseif (preg_match_all('/\$[\d,]+\.\d{2}/', $text, $matches)) {
            $ultimo_monto = end($matches[0]);
            $datos['total_pagar'] = (float) str_replace(['$', ','], '', $ultimo_monto);
        }
    
        return $datos;
    }



    private function procesarHdiPaqueteFamiliarTodoRiesgo(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Valores por defecto específicos
        $datos['poliza_anterior'] = 'N/A';
        $datos['numero_agente'] = '000000';
        $datos['nombre_agente'] = 'N/A';
        if ($datos['vigencia_inicio'] === null) {
            $datos['vigencia_inicio'] = '0000-00-00';
            $datos['vigencia_fin'] = '0000-00-00';
        }
    
        // Póliza anterior
        if (preg_match('/Póliza\s+anterior:\s*(\w+)/i', $text, $matches)) {
            $datos['poliza_anterior'] = $matches[1];
        }
    
        // Agente
        if (preg_match('/Clave:\s*(\d+)\s*Nombre:\s*([^\n]+)/i', $text, $matches)) {
            $datos['numero_agente'] = $matches[1];
            $datos['nombre_agente'] = trim($matches[2]); // Usamos trim en lugar de limpiarTexto por consistencia
        }
    
        // Total a pagar específico (Prima Neta, cuarto monto)
        if (preg_match('/Prima Total.*?Prima Neta.*?\n(.*?)\n/si', $text, $lineMatches)) {
            $amountsLine = $lineMatches[1];
            preg_match_all('/\$([\d,]+\.\d{2})/', $amountsLine, $amountMatches);
            if (!empty($amountMatches[1]) && count($amountMatches[1]) >= 4) {
                $datos['total_pagar'] = (float) str_replace(',', '', $amountMatches[1][3]);
            }
        }
    
        return $datos;
    }
    
    private function limpiarTexto(string $texto): string
    {
        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    private function procesarHdiResponsabilidadCivilProfesionalAgentes(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);
    
        // Valores por defecto específicos
        $datos['numero_agente'] = '000000';
        $datos['nombre_agente'] = 'N/A';
        if ($datos['vigencia_inicio'] === null) {
            $datos['vigencia_inicio'] = '0000-00-00';
            $datos['vigencia_fin'] = '0000-00-00';
        }
    
        // Nombre del cliente (sobrescribe si hay múltiples "Nombre:")
        if (!preg_match('/Cliente:\s*\d+\s*Nombre:/i', $text)) {
            preg_match_all('/Nombre:\s*([^\n]+)/i', $text, $nombres);
            if (count($nombres[1]) >= 2) {
                $datos['nombre_cliente'] = trim($nombres[1][1]); // Segundo nombre como cliente
            }
        }
    
        // Agente
        if (preg_match('/Clave:\s*(\d+)\s*Nombre:\s*([^\n]+)/i', $text, $matches)) {
            $datos['numero_agente'] = $matches[1];
            $datos['nombre_agente'] = trim($matches[2]); // Usamos trim en lugar de limpiarTexto
        }
    
        // Total a pagar específico
        if (preg_match('/Prima (?:Total|Neta).*?\n(.*?)\n/si', $text, $lineMatches)) {
            $amountsLine = preg_replace('/Tasa:.*/', '', $lineMatches[1]);
            preg_match_all('/\$([\d,]+\.\d{2})/', $amountsLine, $matches);
            if (count($matches[1]) >= 3) {
                $datos['total_pagar'] = (float) str_replace(',', '', $matches[1][count($matches[1]) - 1]);
            }
        }
    
        return $datos;
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



private function procesarComun(string $text): array
{
    $datos = [];
    $meses = [
        'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
        'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
        'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
    ];

    // Extracción común de fechas
    if (preg_match('/Las\s+(\d{2}:\d{2})\s+hrs\.\s+del\s+día\s+(\d{1,2}\/\w{3}\/\d{4}).*?Las\s+(\d{2}:\d{2})\s+hrs\.\s+del\s+día\s+(\d{1,2}\/\w{3}\/\d{4})/is', $text, $matches)) {
        $datos['vigencia_inicio'] = $this->formatearFechaMedico($matches[2], $meses);
        $datos['vigencia_fin'] = $this->formatearFechaMedico($matches[4], $meses);
    }

    // Campos comunes
    $campos = [
        'nombre_cliente' => '/Dirección:\s*(.+)/i',
        'rfc' => '/R\.F\.C\.\:\s*([A-Z0-9]+)/i',
        'forma_pago' => '/(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/i',
        'numero_agente' => '/Oficina:.*\t(\d{6})Agente:/i',
        'nombre_agente' => '/Agente:\t([A-Za-zÁÉÍÓÚáéíóúñÑ\s]+)/i'
    ];

    foreach ($campos as $key => $pattern) {
        $datos[$key] = $this->extraerDato($text, $pattern) ?? match($key) {
            'forma_pago' => 'ANUAL EFECTIVO',
            'numero_agente' => '000000',
            'nombre_agente' => 'AGENTE NO ESPECIFICADO',
            default => null
        };
    }

    return $datos;
}
private function formatearFechaMedico(string $fecha, array $meses): string
{
    [$dia, $mes, $anio] = explode('/', $fecha);
    return sprintf('%s-%s-%s', $anio, $meses[strtoupper($mes)] ?? '01', $dia);
}

private function procesarGastosMedicosTotal(string $text): array
{
    $datos = $this->procesarComun($text);
    
    // Campos específicos
    $datos['numero_poliza'] = $this->extraerDato($text, '/Suma Asegurada:\s*(.+)/i');
    preg_match_all('/[\d,]+\.\d{2}/', $text, $matches);
    $datos['total_pagar'] = (float)str_replace(',', '', end($matches[0]));
return $datos;
    
   // dd($datos);
}

private function procesarGastosMedicosVital(string $text): array
{
    $datos = $this->procesarComun($text);
    
    // Campos específicos
    $datos['numero_poliza'] = $this->extraerDato($text, '/(\d{6})\s+ZONA/i');
    $valorExtraido = $this->extraerDato($text, '/Fecha de Efectividad:\s*(\d{1,3}(?:,\d{3})*\.\d{2})/i', true);
    $datos['total_pagar'] = $valorExtraido ? (float) str_replace(',', '', $valorExtraido) : 0.0;
    
    return $datos;

   
}

private function procesarComunCamiones(string $text): array
{
    $datos = [
        'nombre_cliente' => 'SIN NOMBRE',
        'rfc' => 'N/A',
        'numero_poliza' => 'N/A',
        'vigencia_inicio' => '0000-00-00',
        'vigencia_fin' => '0000-00-00',
        'forma_pago' => 'N/A',
        'numero_agente' => '000000',
        'nombre_agente' => 'N/A',
        'total_pagar' => 0.00
    ];

    // Extracción común del nombre
    $datos['nombre_cliente'] = $this->extraerDato($text, '/(\n|^)([A-ZÁÉÍÓÚÑ\s\.\-]+)\nRFC:/i', 2) 
                              ?? $datos['nombre_cliente'];

    // Campos comunes con regex
    $campos = [
        'rfc' => '/RFC:\s*([A-Z0-9]{12,13})/i',
        'numero_poliza' => '/Póliza:\s*([\d\-]+)/i',
        'forma_pago' => '/(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(EFECTIVO|CHEQUE|TARJETA)?/i',
        'numero_agente' => '/Agente:\s*(\d+)/i',
        'nombre_agente' => '/Agente:\s*\d+\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)/i'
    ];

    foreach ($campos as $key => $pattern) {
        $datos[$key] = $this->extraerDato($text, $pattern) ?? $datos[$key];
    }

    // Procesamiento de fechas
    if (preg_match('/Vigencia:.*?(\d{2}\/\d{2}\/\d{4}).*?(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
        $datos['vigencia_inicio'] = $this->formatearFechaCamiones($matches[1]);
        $datos['vigencia_fin'] = $this->formatearFechaCamiones($matches[2]);
    }

    // Extracción del total
    if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
        $datos['total_pagar'] = (float)str_replace(',', '', $matches[1]);
    } else {
        preg_match_all('/\b\d{1,3}(?:,\d{3})*\.\d{2}\b/', $text, $matches);
        $datos['total_pagar'] = (float)str_replace(',', '', end($matches[0])) ?: 0.00;
    }

    return $datos;
}

private function formatearFechaCamiones(string $fecha): string
{
    $date = DateTime::createFromFormat('d/m/Y', $fecha);
    return $date ? $date->format('Y-m-d') : '0000-00-00';
}

private function procesarCamionesHasta3_5(string $text): array
{
    $datos = $this->procesarComunCamiones($text);
    
    // Campo exclusivo de esta póliza
    $datos['poliza_anterior'] = $this->extraerDato($text, '/Póliza Anterior\s*:\s*([\d\-]+)/i') ?? 'N/A';

    return $datos;
}

private function procesarCamionesMasDe_3_5(string $text): array
{
    return $this->procesarComunCamiones($text);
}


   

  
}
