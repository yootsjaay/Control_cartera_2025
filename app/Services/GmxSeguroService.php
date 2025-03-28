<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Exception;
use Illuminate\Support\Facades\Log;

class GmxSeguroService implements SeguroServiceInterface
{
    // Constantes para los slugs de los ramos
    const RAMO_RCP_ESTANCIAS_INFANTILES = 'rcp-estancias-infantiles';
    const RAMO_TRANSPORTE_CARGA = 'transporte-carga';
    const RAMO_RESPONSABILIDAD_CIVIL_PROFESIONAL = 'responsabilidad-civil-profesional';
    const RAMO_RC_ESPARCIMIENTO = 'rc-esparcimiento';
    const RAMO_RC_PROFESIONAL = 'rc-profesional-medicos-y-sus-profesiones-auxiliares-y-tecnicas';

    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'gmx-seguros') {
            throw new InvalidArgumentException("El seguro seleccionado no pertenece a GMX Seguros.");
        }

        if ($ramo->id_seguros != $seguro->id) {
            throw new InvalidArgumentException("El ramo seleccionado no corresponde al seguro proporcionado.");
        }

        try {
            $text = $this->extractText($archivo);
            Log::info("Texto extraído del PDF para ramo {$ramo->slug}:", ['data' => substr($text, 0, 500)]);
            return $this->procesarTexto($text, $ramo);
        } catch (Exception $e) {
            Log::error("Error al procesar el PDF: " . $e->getMessage(), [
                'exception' => $e,
                'seguro_id' => $seguro->id,
                'ramo_id' => $ramo->id
            ]);
            throw new InvalidArgumentException("Error procesando el archivo PDF: " . $e->getMessage());
        }
    }

    private function extractText(UploadedFile $archivo): string
    {
        try {
            $pdf = $this->parser->parseFile($archivo->getPathname());
            return $pdf->getText();
        } catch (Exception $e) {
            Log::error("Error al parsear el PDF: " . $e->getMessage());
            throw new Exception("Error al leer el contenido del PDF: " . $e->getMessage());
        }
    }

    private function convertirFecha(string $fecha): string
    {
        $meses = [
            'ENERO' => '01', 'FEBRERO' => '02', 'MARZO' => '03', 'ABRIL' => '04',
            'MAYO' => '05', 'JUNIO' => '06', 'JULIO' => '07', 'AGOSTO' => '08',
            'SEPTIEMBRE' => '09', 'OCTUBRE' => '10', 'NOVIEMBRE' => '11', 'DICIEMBRE' => '12'
        ];
        [$dia, $mes, $anio] = explode(' ', strtoupper($fecha));
        return "$anio-{$meses[$mes]}-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
    }

    private function procesarTexto(string $text, Ramo $ramo): array
    {
        switch ($ramo->slug) {
            case self::RAMO_TRANSPORTE_CARGA:
                return $this->procesarTransporteCarga($text);
            case self::RAMO_RCP_ESTANCIAS_INFANTILES:
                return $this->procesarRcpEstanciasInfantiles($text);
            case self::RAMO_RESPONSABILIDAD_CIVIL_PROFESIONAL:
                return $this->procesarResponsabilidadCivilProfesional($text);
            case self::RAMO_RC_ESPARCIMIENTO:
                return $this->procesarRcpEsparcimiento($text);
            case self::RAMO_RC_PROFESIONAL:
                return $this->procesarProfesionalMedicos($text);
            default:
                return $this->procesarPoliza($text);
        }
    }

    private function procesarPoliza(string $text): array
    {
        $datosExtraidos = [
            'rfc' => null,
            'numero_poliza' => null,
            'nombre_cliente' => null,
            'numero_agente' => null,
            'nombre_agente' => null,
            'vigencia_inicio' => null,
            'vigencia_fin' => null,
            'forma_pago' => null
        ];

        if (preg_match('/RFC\s+([A-Z0-9]+)/i', $text, $matches)) {
            $datosExtraidos['rfc'] = $matches[1];
        }

         // 2. Extraer Número de Póliza
         if (preg_match('/OFICINA\s+PRODUCTO\s+PÓLIZA\s+ENDOSO\s+RENOVACIÓN\s+.*?\n\d+\s+\w+\s+\d+\s+(\d{8})\s+\d+\s+\d+/is', $text, $matches)) {
            $datosExtraidos['numero_poliza'] = $matches[1];
        }

        if (preg_match('/Contratante\s+([^\n]+)/i', $text, $matches)) {
            $datosExtraidos['nombre_cliente'] = trim($matches[1]);
        }

        if (preg_match('/Agente\s+(\d+)\s*-\s*([^\n]+)/i', $text, $matches)) {
            $datosExtraidos['numero_agente'] = $matches[1];
            $datosExtraidos['nombre_agente'] = trim($matches[2]);
        }

        if (preg_match('/Desde\s+(\d{2}\s+[A-Z]+\s+\d{4})\s+\d{2}:\d{2}/i', $text, $matchesInicio) &&
            preg_match('/Hasta\s+(\d{2}\s+[A-Z]+\s+\d{4})\s+\d{2}:\d{2}/i', $text, $matchesFin)) {
            $datosExtraidos['vigencia_inicio'] = $this->convertirFecha($matchesInicio[1]);
            $datosExtraidos['vigencia_fin'] = $this->convertirFecha($matchesFin[1]);
        }

        if (preg_match('/Forma de Pago\s+([^\n]+)/i', $text, $matches)) {
            $datosExtraidos['forma_pago'] = trim($matches[1]);
        }

        return $datosExtraidos;
    }

    private function procesarTransporteCarga(string $text): array
    {
        $datos = $this->procesarPoliza($text);

        if (preg_match('/Prima\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_pagar'] = (float)str_replace([',', '$'], '', $matches[1]);
        }
        
        return $datos;
    }

    private function procesarRcpEstanciasInfantiles(string $text): array
    {
        $datos = $this->procesarPoliza($text);

        
        // 6. Total a pagar
     if (preg_match('/Prima\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$([\d,]+\.\d{2})/i', $text, $matches)) {
        $datos['total_pagar'] = (float)str_replace([',', '$'], '', $matches[1]);
    }
        dd($datos);
        return $datos;
    }

    private function procesarResponsabilidadCivilProfesional(string $text): array
    {
        $datos = $this->procesarPoliza($text);

        // Extraer Total a Pagar específico para RC Profesional (en pesos)
        if (preg_match('/Prima\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_pagar'] = (float)str_replace([',', '$'], '', $matches[1]);
        }
        
        return $datos;
    }

    private function procesarRcpEsparcimiento(string $text): array
    {
        $datos = $this->procesarPoliza($text);
        // Extraer el Total a Pagar en dólares
        if (preg_match('/Prima\s+US\$[\d,.]+\s+US\$[\d,.]+\s+US\$[\d,.]+\s+US\$[\d,.]+\s+US\$([\d,.]+)/', $text, $matches)) {
            $totalPagar = str_replace(['US$', ','], '', $matches[1]); // Eliminar "US$" y comas
            $datos['total_pagar'] = (float)$totalPagar; // Convertir a número flotante
        }
    
        
        
        return $datos;
    }

    private function procesarProfesionalMedicos(string $text): array
    {
        $datos = $this->procesarPoliza($text);

          // Extraer el Total a Pagar en dólares
          if (preg_match('/Prima\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$[\d,]+\.\d{2}\s+\$([\d,]+\.\d{2})/i', $text, $matches)) {
            $datos['total_pagar'] = (float)str_replace([',', '$'], '', $matches[1]);
        }

        return $datos;
    }
}