<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use App\Services\Contracts\SeguroServiceInterface;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use DateTime;
use Exception;

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

        // Verificar que el archivo sea un PDF
        if ($archivo->getClientMimeType() !== 'application/pdf') {
            throw new InvalidArgumentException("El archivo proporcionado no es un PDF.");
        }

        try {
            $text = $this->extractText($archivo); // Usa el nuevo método extractText
            Log::info("Texto extraído exitosamente", [
                'seguro' => $seguro->nombre,
                'ramo' => $ramo->nombre,
                'data' => substr($text, 0, 500),
            ]);
            return $this->procesarTexto($text, $ramo, $seguro);
        } catch (PdfParseException $e) {
            Log::error("Error al extraer texto del PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo extraer el texto del PDF: " . $e->getMessage());
        } catch (Exception $e) {
            Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF: " . $e->getMessage());
        }
    }

    private function extractText(UploadedFile $archivo): string
    {
        try {
            $pdf = $this->parser->parseFile($archivo->getPathname()); // Usa el parser inyectado
            $text= $pdf->getText();
           return $text;
           dd($text);

        } catch (\Exception $e) {
            Log::error("Error al parsear el PDF: " . $e->getMessage());
            throw new Exception("Error al procesar el PDF.");
        }
    }
    private function procesarTexto(string $text, Ramo $ramo, Seguro $seguro): array
    {
        // Obtener datos comunes a todos los ramos
        $datosComunes = $this->procesarDatosComunes($text);

        // Mapear ramos y seguros específicos
        $mapaProcesadores = [
            'Automóviles' => [
                'Autos pickup' => 'procesarAutosPickup',
                'Camiones' => 'procesarCamiones',
                'Tractos' => 'procesarTractos',
            ],
            'Accidentes y enfermedades' => [
                'Gastos Médicos Mayores' => 'procesarGastosMedicosMayores',
                'Accidentes Personales' => 'procesarAccidentesPersonales',
                'Accidentes Personales Escolares' => 'procesarAccidentesEscolares',
            ],
            'Daños' => [
                'Seguro de Daños empresa' => 'procesarDaniosEmpresa',
                'Casa' => 'procesarCasa',
                'Transporte' => 'procesarTransporte',
            ],
            'Vida' => [
                'Seguro de Vida Individual' => 'procesarVidaIndividual',
                'Grupo vida' => 'procesarGrupoVida',
                'Seguro de inversión' => 'procesarInversion',
                'De retiro' => 'procesarRetiro',
            ],
        ];

        // Verificar si el ramo y seguro tienen un procesador definido
        if (!isset($mapaProcesadores[$ramo->nombre][$seguro->nombre])) {
            throw new InvalidArgumentException("No existe un procesador definido para el seguro '{$seguro->nombre}' del ramo '{$ramo->nombre}'.");
        }

        // Obtener el nombre del método procesador
        $metodoProcesador = $mapaProcesadores[$ramo->nombre][$seguro->nombre];

        // Ejecutar el método correspondiente
        $datosEspecificos = $this->$metodoProcesador($text);

        // Combina los datos comunes con los específicos
        return array_merge($datosComunes, $datosEspecificos);
    }
    private function validarSeguroYramo(Seguro $seguro, Ramo $ramo): void
    {
        // Ejemplo básico: verifica que el seguro pertenezca a la compañía HDI o que el ramo sea de los permitidos.
        if (!$seguro || !$ramo) {
            throw new InvalidArgumentException("Seguro o ramo inválido.");
        }
        // aqui agregar validaciones.
    }

    private function procesarDatosComunes(string $text): array
    {
        $datos = [
            'nombre_cliente' => $this->extraerNombreCliente($text),
            'rfc' => $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})\b/i') ?? 'N/A',
            'vigencia_inicio' => null,
            'vigencia_fin' => null,
            'forma_pago' => $this->extraerFormaPago($text),
            'total_pagar' => $this->extraerTotalPagar($text)
        ];

        $this->validarRFC($datos['rfc'], $text);
        $this->procesarVigencia($text, $datos);

        return $datos;
    }

    private function procesarAutosPickup(string $text): array
    {
        return $this->procesarVehiculos($text, [
            'regex_vigencia' => '/Vigencia:\s*Desde las \d{2}:\d{2} hrs\. del\s*(\d{2}\/\d{2}\/\d{4})\s*Hasta las \d{2}:\d{2} hrs\. del\s*(\d{2}\/\d{2}\/\d{4})/i',
            'campos_agente' => true
        ]);
    }

    private function procesarCamiones(string $text): array
    {
        return $this->procesarVehiculos($text, [
            'regex_nombre' => '/(\n|^)([A-ZÁÉÍÓÚÑ\s\.\-]+)\nRFC:/i',
            'regex_vigencia' => '/Vigencia:.*?(\d{2}\/\d{2}\/\d{4}).*?(\d{2}\/\d{2}\/\d{4})/i'
        ]);
    }

    private function procesarVehiculos(string $text, array $config): array
    {
        $datos = [
            'nombre_cliente' => $this->extraerNombreVehiculos($text, $config),
            'rfc' => 'N/A',
            'numero_poliza' => 'N/A',
            'vigencia_inicio' => '0000-00-00',
            'vigencia_fin' => '0000-00-00',
            'forma_pago' => $this->extraerFormaPago($text),
            'numero_agente' => '000000',
            'nombre_agente' => 'N/A',
            'total_pagar' => $this->extraerTotalPagar($text)
        ];

        $campos = [
            'rfc' => '/RFC:\s*([A-Z0-9]{12,13})/i',
            'numero_poliza' => '/Póliza:\s*([\d\-]+)/i',
            'numero_agente' => '/Agente:\s*(\d+)/i',
            'nombre_agente' => '/Agente:\s*\d+\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)/i'
        ];

        foreach ($campos as $key => $pattern) {
            $datos[$key] = $this->extraerDato($text, $pattern) ?? $datos[$key];
        }

        if (preg_match($config['regex_vigencia'], $text, $matches)) {
            $datos['vigencia_inicio'] = $this->formatearFecha($matches[1]);
            $datos['vigencia_fin'] = $this->formatearFecha($matches[2]);
        }

        if (!empty($config['campos_agente'])) {
            $datos['numero_agente'] = $datos['numero_agente'] ?: '000000';
            $datos['nombre_agente'] = $datos['nombre_agente'] ?: 'N/A';
        }

        return $datos;
    }
    // Nuevo método para extracción especializada
private function extraerNombreVehiculos(string $text, array $config): string
{
    // Intenta con el patrón específico primero
    if (!empty($config['regex_nombre'])) {
        $nombre = $this->extraerDato($text, $config['regex_nombre'], 1);
        if ($nombre) return $nombre;
    }

    // Fallback a los patrones generales
    $patronesFallback = [
        '/(CLIENTE:\s*\d+\s*)?Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)/i' => 2,
        '/(\n|^)([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)\nRFC:/i' => 2
    ];

    foreach ($patronesFallback as $pattern => $group) {
        $nombre = $this->extraerDato($text, $pattern, $group);
        if ($nombre) return $nombre;
    }

    return 'SIN NOMBRE';
}

    private function extraerNombreCliente(string $text): string
    {
        $patterns = [
            '/Cliente:\s*\d*\s*Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)/i',
            '/Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=\n\s*Domicilio Fiscal:)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]) ?: 'SIN NOMBRE';
            }
        }
        return 'SIN NOMBRE';
    }

    private function extraerFormaPago(string $text): string
    {
        return $this->extraerDato($text, '/(?:Forma\s+de\s+Pago:\s*)?(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(EFECTIVO|CHEQUE|TARJETA)?/i')
            ?? 'N/A';
    }

    private function procesarVigencia(string $text, array &$datos): void
    {
        if (preg_match('/Vigencia:.*?(\d{2}[\/\-]\w{3}[\/\-]\d{4}).*?(\d{2}[\/\-]\w{3}[\/\-]\d{4})/si', $text, $matches)) {
            $datos['vigencia_inicio'] = $this->formatearFechaConMes($matches[1]);
            $datos['vigencia_fin'] = $this->formatearFechaConMes($matches[2]);
        }
    }

    private function formatearFechaConMes(string $fecha): ?string
    {
        $meses = [
            'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
        ];

        $dateTime = DateTime::createFromFormat('d/m/Y', strtr($fecha, $meses));

        return $dateTime ? $dateTime->format('Y-m-d') : null;
    }

    private function formatearFecha(string $fecha): string
    {
        return DateTime::createFromFormat('d/m/Y', $fecha)?->format('Y-m-d') ?: '0000-00-00';
    }

    private function validarRFC(string $rfc, string $text): void
    {
        if (strlen($rfc) < 12) {
            Log::error("RFC no válido", ['text' => substr($text, 0, 300)]);
        }
    }
  
private function procesarGastosMedicosMayores(string $text): array
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

    // Asegúrate de que 'numero_poliza' también tenga su patrón aquí
    $datos['numero_poliza'] = $this->extraerDato($text, '/P[ÓO]LIZA[:\s]*([A-Z0-9\-]+)/i') ?? $this->extraerDato($text, '/(\d{6})\s+ZONA/i');
 // Total a pagar (usa variaciones comunes)
 preg_match('/[\d,]+\.\d{2}/', $text, $matches);
 $datos['total_pagar'] = isset($matches[0]) ? (float)str_replace(',', '', $matches[0]) : 0.0;

     return $datos;
   
}

private function formatearFechaMedico(string $fecha, array $meses): string
{
    [$dia, $mes, $anio] = explode('/', $fecha);
    return sprintf('%s-%s-%s', $anio, $meses[strtoupper($mes)] ?? '01', $dia);
}
private function extraerDato(string $text, string $pattern, int $group = 1): ?string
{
    if (preg_match($pattern, $text, $matches) && isset($matches[$group])) {
        return trim($matches[$group]);
    }
    return null;
}


    private function procesarTransporte(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);

        // Datos específicos de Civil Viajero
        $datos['numero_agente'] = $this->extraerDatos($text, '/Clave:\s*(\d+)/i') ?: '000000';
        // Nombre del agente (antes de "Datos de la póliza:")
        $datos['nombre_agente'] = $this->extraerDatos($text, '/Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=[\s\S]*Datos de la póliza:)/i', fn($match) => trim($match)) ?: 'AGENTE NO ESPECIFICADO';

      return $datos; 
    }

    private function procesarEmbarcaciones(string $text): array
    {
        $datos = $this->procesarDatosComunes($text);

        // Datos específicos de Embarcaciones
        $datos['numero_agente'] = $this->extraerDatos($text, '/Clave:\s*(\d+)/i') ?: 'N/A';
        $datos['nombre_agente'] = $this->extraerDatos($text, '/Clave:\s*\d+\s*Nombre:\s*([^\n]+)/i', $this->limpiarTexto(...)) ?: 'AGENTE NO ESPECIFICADO';

   return $datos;
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

    }
    private function procesarDatosComunesDos(string $text): array
    {
        $datos = [];
        $meses = [
            'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
        ];
        
    
        // Vigencia
        if (preg_match('/Vigencia:\s*Desde.*?(\d{2}\/[A-Z]{3}\/\d{4}).*?Hasta.*?(\d{2}\/[A-Z]{3}\/\d{4})/i', $text, $matches)) {
            $datos['vigencia_inicio'] = $this->formatearFechaMedico($matches[1], $meses);
            $datos['vigencia_fin'] = $this->formatearFechaMedico($matches[2], $meses);
        } else {
            $datos['vigencia_inicio'] = '0000-00-00';
            $datos['vigencia_fin'] = '0000-00-00';
        }
    
        // Forma de pago
        $datos['forma_pago'] = $this->extraerDato($text, '/Forma\s+de\s+pago:\s*(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/i') ?? 'ANUAL EFECTIVO';
    
        // Nombre cliente (línea posterior a "Nombre:")
        $nombre = $this->extraerDato($text, '/Nombre:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=\n\s*Domicilio Fiscal:)/i');
        $datos['nombre_cliente'] = $nombre ? preg_replace('/\s+/', ' ', trim($nombre)) : 'NOMBRE NO ENCONTRADO';
    
        // RFC
        $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{10,13})/i') ?? 'N/A';
    
        // Agente
        $datos['numero_agente'] = $this->extraerDato($text, '/Clave:\s*(\d{6})/i') ?? '000000';
        $datos['nombre_agente'] = $this->extraerDato($text, '/Nombre:\s*([A-ZÁÉÍÓÚÑ\s]+)/i') ?? 'AGENTE NO ESPECIFICADO';
    
        // Total a pagar (prima total)
        if (preg_match('/Prima\s+Total\s+\$([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_pagar'] = (float) str_replace(',', '', $matches[1]);
        } else {
            $datos['total_pagar'] = 0.0;
        }
    
        // Número de póliza
        $numeroPoliza = $this->extraerDato($text, '/No\. de Póliza:\s*([\d\s\-]+)/i');
        $datos['numero_poliza'] = $numeroPoliza && preg_match('/^[\d\s\-]+$/', $numeroPoliza) ? preg_replace('/\s+/', '', $numeroPoliza) : $datos['numero_poliza'];

        return $datos;
       
    }
    
    private function procesarCasa(string $text): array
    {
        $datos = $this->procesarDatosComunesDos($text);
    
     
         return $datos;
    }
    
    private function procesarDaniosEmpresa(string $text): array
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

private function extraerMonto(string $text, string $pattern): ?float
{
    if (preg_match($pattern, $text, $matches)) {
        return (float) str_replace(',', '', $matches[1]);
    }
    return null;
}

}
