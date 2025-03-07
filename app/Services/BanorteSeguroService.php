<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Carbon\Carbon;
use Exception;

class BanorteSeguroService implements SeguroServiceInterface
{
    const RAMO_AUTOMOVILES_RESIDENTES = 'automoviles-residentes-banorte';
    const RAMO_GASTOS_MEDICOS_INDIVIDUAL='gastos-medicos-mayores-individual';
    const RAMO_GASTOS_MEDICOS_MAYORES_GRUPOS ='gastos-medicos-mayores-grupo';

    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'banorte-seguros') {
            throw new InvalidArgumentException("El seguro seleccionado no pertenece a Banorte.");
        }

        if ($ramo->id_seguros != $seguro->id) {
            throw new InvalidArgumentException("El ramo seleccionado no corresponde al seguro proporcionado.");
        }

        try {
            $text = $this->extractText($archivo);
            \Log::info("Texto extraído:", ['data' => substr($text, 0, 500)]);
            return $this->procesarTexto($text, $ramo);
        } catch (Exception $e) {
            \Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF: " . $e->getMessage());
        }
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
    private function procesarGastosMedicosIndividual(String $text):array{
    

    }
    private function procesarGastosMedicosMayoresGrupos(String $text):array{
        $datos = [];

        // Nombre del contratante
        if (preg_match('/Nombre y apellido completo\s*([A-ZÁÉÍÓÚÑ\s]+)(?=Domicilio:)/i', $text, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]) ?: 'SIN NOMBRE';
        } else {
            $datos['nombre_cliente'] = 'SIN NOMBRE';
        }

        // RFC
        $datos['rfc'] = $this->extraerDato($text, '/R\.F\.C:\s*([A-Z0-9]+)/i') ?? 'N/A';

        // Número de póliza
        $datos['numero_poliza'] = $this->extraerDato($text, '/NO\. DE PÓLIZA\s*(\d+)/i') ?? null;

        // Vigencia
        if (preg_match('/VIGENCIA\s*DESDE\s*\d{2}\s*HRS\.\s*HASTA\s*\d{2}\s*HRS\.\s*(\d{2}\/\d{2}\/\d{4})\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
            $meses = [
                '01' => '01', '02' => '02', '03' => '03', '04' => '04',
                '05' => '05', '06' => '06', '07' => '07', '08' => '08',
                '09' => '09', '10' => '10', '11' => '11', '12' => '12'
            ];
            [$diaInicio, $mesInicio, $anioInicio] = explode('/', $matches[1]);
            [$diaFin, $mesFin, $anioFin] = explode('/', $matches[2]);
            $mesInicioNum = $meses[$mesInicio] ?? '01';
            $mesFinNum = $meses[$mesFin] ?? '01';
            $datos['vigencia_inicio'] = "$anioInicio-$mesInicioNum-$diaInicio";
            $datos['vigencia_fin'] = "$anioFin-$mesFinNum-$diaFin";
        } else {
            $datos['vigencia_inicio'] = null;
            $datos['vigencia_fin'] = null;
        }

        // Forma de pago
        $datos['forma_pago'] = $this->extraerDato($text, '/FORMA DE PAGO\s*(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)/i') ?? 'N/A';

        // Número de agente
        $datos['numero_agente'] = $this->extraerDato($text, '/AGENTE\s*(\d+)/i') ?? '000000';

        // Nombre del agente (extraído de "Nombre y Clave del Agente")
        $datos['nombre_agente'] = $this->extraerDato($text, '/Nombre y Clave del Agente:\s*([A-ZÁÉÍÓÚÑ\s]+)\s*\d+/i') ?? 'AGENTE NO ESPECIFICADO';

        // Total a pagar (Prima Total)
        $primaText = substr($text, strpos($text, "Prima Total"), 50);
        \Log::info("Texto alrededor de Prima Total:", ['text' => $primaText]);
        if (preg_match('/Prima Total\s*[\r\n\s]*\$\s*([\d,\.]+)/i', $text, $matches)) {
            $datos['total_pagar'] = str_replace(',', '', $matches[1]);
        } else {
            $datos['total_pagar'] = null;
            \Log::warning("No se encontró Prima Total en el texto.");
        }

        // Log para depuración
        \Log::info("Datos procesados (Gastos Médicos):", $datos);

        return $datos;
        //dd($datos);
    }

    private function procesarAutosResidentes(string $text): array
    {
        $datos = [];

        // Nombre del contratante
        if (preg_match('/Nombre del Contratante:\s*([A-ZÁÉÍÓÚÑ\s\.\-]+)(?=\tR\.F\.C\.)/i', $text, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]) ?: 'SIN NOMBRE';
        } else {
            $datos['nombre_cliente'] = 'SIN NOMBRE';
        }

        // RFC
        $datos['rfc'] = $this->extraerDato($text, '/R\.F\.C\.:([A-Z0-9]+)/i') ?? 'N/A';

        // Número de póliza
        if (preg_match('/No\.\s*de\s*Póliza.*?\s*(\d+)\s+\d+\s+(\w+)/s', $text, $matches)) {
            $datos['numero_poliza'] = preg_replace('/\s+/', '', $matches[1]);
        }

        // Vigencia
        if (preg_match('/Inicio Vigencia:\s*\d{2}:\d{2}\s*hrs\s*(\d{2}\/\w{3}\/\d{4}).*?Fin Vigencia:\s*\d{2}:\d{2}\s*hrs\s*(\d{2}\/\w{3}\/\d{4})/is', $text, $matches)) {
            $meses = [
                'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
                'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
                'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
            ];
            [$diaInicio, $mesInicio, $anioInicio] = explode('/', $matches[1]);
            [$diaFin, $mesFin, $anioFin] = explode('/', $matches[2]);
            $mesInicioNum = $meses[strtoupper($mesInicio)] ?? '01';
            $mesFinNum = $meses[strtoupper($mesFin)] ?? '01';
            $datos['vigencia_inicio'] = "$anioInicio-$mesInicioNum-$diaInicio";
            $datos['vigencia_fin'] = "$anioFin-$mesFinNum-$diaFin";
        } else {
            $datos['vigencia_inicio'] = null;
            $datos['vigencia_fin'] = null;
        }

        // Forma de pago (ajustado para no incluir "Forma de pago:")
        if (preg_match('/Forma de pago:\s*(ANUAL|SEMESTRAL|TRIMESTRAL|MENSUAL)\s*(\d{1,2}\s*MESES)?/i', $text, $matches)) {
            $datos['forma_pago'] = trim($matches[1] . ' ' . ($matches[2] ?? '')); // Solo ANUAL 12 MESES
        } else {
            $datos['forma_pago'] = 'N/A';
        }

        // Número de agente
        $datos['numero_agente'] = $this->extraerDato($text, '/Intermediario:\s*(\d+)\s+/i') ?? '000000';

        // Nombre de agente (ajustado para no incluir texto adicional)
        $datos['nombre_agente'] = $this->extraerDato($text, '/Intermediario:\s*\d+\s+([A-ZÁÉÍÓÚÑ\s]+)(?=Prima|$)/i') ?? 'AGENTE NO ESPECIFICADO';

        // Total a pagar
        if (preg_match('/Prima Total:\s*\$([\d,\.]+)/i', $text, $matches)) {
            $datos['total_pagar'] = str_replace(',', '', $matches[1]);
        } else {
            $datos['total_pagar'] = null;
        }
        // Log para depuración
        \Log::info("Datos procesados:", $datos);

        return $datos;
       // dd($datos);
    }
}