<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Exception;

class BanorteSeguroService implements SeguroServiceInterface
{
    const RAMO_AUTOMOVILES_RESIDENTES = 'automoviles-residentes-banorte';
    const RAMO_GASTOS_MEDICOS_INDIVIDUAL = 'gastos-medicos-mayores-individual';
    const RAMO_GASTOS_MEDICOS_MAYORES_GRUPOS = 'gastos-medicos-mayores-grupo';

    protected Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }


    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        $this->validarEntrada($archivo, $seguro, $ramo);

        try {
            $text = $this->extractText($archivo);
            \Log::info("Texto extraído:", ['data' => substr($text, 0, 500)]);
            return $this->procesarTexto($text, $ramo);
        } catch (Exception $e) {
            \Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF: " . $e->getMessage());
        }
    }

    private function extraerFecha(string $text, string $pattern, array $meses = []): ?array
    {
        if (!preg_match($pattern, $text, $matches)) {
            return null;
        }
    
        [$dia, $mes, $anio] = explode('/', $matches[1]);
        if (!empty($meses)) {
            $mesNum = $meses[strtoupper($mes)] ?? '01';
        } else {
            $mesNum = $mes;
        }
    
        return ["$anio-$mesNum-$dia"];
    }

private function extraerDatosComunes(string $text, array $config): array
{
    $datos = [];

    // Nombre del cliente
    $datos['nombre_cliente'] = $this->extraerDato($text, $config['nombre_pattern']) ?? 'SIN NOMBRE';

    // RFC
    $datos['rfc'] = $this->extraerDato($text, $config['rfc_pattern']) ?? 'N/A';

    // Número de póliza
    $datos['numero_poliza'] = $this->extraerDato($text, $config['poliza_pattern']) ?? null;
   
    if (empty($datos['numero_poliza'])) {
        throw new Exception("Número de póliza es requerido");
    }
    
    // Vigencia
    $fechaInicio = $this->extraerFecha($text, $config['vigencia_inicio_pattern'], $config['meses'] ?? []);
    $fechaFin = $this->extraerFecha($text, $config['vigencia_fin_pattern'], $config['meses'] ?? []);
    $datos['vigencia_inicio'] = $fechaInicio[0] ?? null;
    $datos['vigencia_fin'] = $fechaFin[0] ?? null;

    // Forma de pago
    $datos['forma_pago'] = $this->extraerDato($text, $config['forma_pago_pattern']) ?? 'N/A';

    // Número de agente
    $datos['numero_agente'] = $this->extraerDato($text, $config['agente_numero_pattern']) ?? '000000';

    // Nombre del agente
    $datos['nombre_agente'] = $this->extraerDato($text, $config['agente_nombre_pattern']) ?? 'AGENTE NO ESPECIFICADO';

    // Total a pagar
    $datos['total_pagar'] = $this->extraerDato($text, $config['total_pagar_pattern']) ?? null;

    return $datos;
}
private function getConfigGastosMedicos(): array
{
    return [
        'nombre_pattern' => '/Nombre y apellido completo\s*([A-ZÁÉÍÓÚÑ\s]+)(?=Domicilio:)/i',
        'rfc_pattern' => '/R\.F\.C:\s*([A-Z0-9]+)/i',
        'poliza_pattern' => '/NO\. DE PÓLIZA\s*(\d+)/i',
        'vigencia_inicio_pattern' => '/VIGENCIA\s*DESDE\s*\d{2}\s*HRS\.\s*HASTA\s*\d{2}\s*HRS\.\s*(\d{2}\/\d{2}\/\d{4})/i',
        'vigencia_fin_pattern' => '/VIGENCIA\s*DESDE\s*\d{2}\s*HRS\.\s*HASTA\s*\d{2}\s*HRS\.\s*\d{2}\/\d{2}\/\d{4}\s*(\d{2}\/\d{2}\/\d{4})/i',
        'forma_pago_pattern' => '/FORMA DE PAGO\s*(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)/i',
        'agente_numero_pattern' => '/AGENTE\s*(\d+)/i',
        'agente_nombre_pattern' => '/Nombre y Clave del Agente:\s*([A-ZÁÉÍÓÚÑ\s]+)\s*\d+/i',
        'total_pagar_pattern' => '/Prima Total\s*[\r\n\s]*\$\s*([\d,\.]+)/i',
    ];
}

private function getConfigAutosResidentes(): array
{
    return [
        'nombre_pattern' => '/Nombre del Contratante:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=\tR\.F\.C\.)/i',
        'rfc_pattern' => '/R\.F\.C\.:([A-Z0-9]+)/i',
        'poliza_pattern' => '/No\.\s*de\s*Póliza.*?\s*(\d+)\s+\d+\s+(\w+)/s',
        'vigencia_inicio_pattern' => '/Inicio Vigencia:\s*\d{2}:\d{2}\s*hrs\s*(\d{2}\/\w{3}\/\d{4})/i',
        'vigencia_fin_pattern' => '/Fin Vigencia:\s*\d{2}:\d{2}\s*hrs\s*(\d{2}\/\w{3}\/\d{4})/i',
        'forma_pago_pattern' => '/Forma de pago:\s*(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(\d{1,2}\s*MESES)?/i',
        'agente_numero_pattern' => '/Intermediario:\s*(\d+)\s+/i',
        'agente_nombre_pattern' => '/Intermediario:\s*\d+\s+([A-ZÁÉÍÓÚÑ\s]+)(?=Prima|$)/i',
        'total_pagar_pattern' => '/Prima Total:\s*\$([\d,\.]+)/i',
        'meses' => [
            'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
        ],
    ];
}
    private function extractText(UploadedFile $archivo): string
    {
        try {
            $pdf = $this->parser->parseFile($archivo->getPathname());
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
            case self::RAMO_AUTOMOVILES_RESIDENTES:
                return $this->procesarAutosResidentes($text);
            case self::RAMO_GASTOS_MEDICOS_INDIVIDUAL:
                return $this->procesarGastosMedicosIndividual($text);
            case self::RAMO_GASTOS_MEDICOS_MAYORES_GRUPOS:
                return $this->procesarGastosMedicosMayoresGrupos($text);
            default:
                throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }

    private function extraerDato(string $text, string $pattern, bool $trim = true): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return $trim ? trim($matches[1]) : $matches[1];
        }
        return null;
    }
    
    private function procesarGastosMedicosIndividual(string $text): array
{
    throw new \RuntimeException("Método no implementado.");
}
private function procesarGastosMedicosMayoresGrupos(string $text): array
    {
        $config = $this->getConfigGastosMedicos();
        return $this->extraerDatosComunes($text, $config);
    }
    
    private function procesarAutosResidentes(string $text): array
    {
        $config = $this->getConfigAutosResidentes();
        return $this->extraerDatosComunes($text, $config);
    }
}