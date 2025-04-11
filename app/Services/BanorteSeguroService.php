<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Services\Contracts\SeguroServiceInterface;
use App\Models\Seguro;
use App\Models\Ramo;
use Illuminate\Support\Facades\Log;

use InvalidArgumentException;
use Exception;

class BanorteSeguroService implements SeguroServiceInterface
{
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser= $parser;
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
            $pdf = $this->parser->parseFile($archivo->getPathname());
            $text = $pdf->getText();
            return $text;
        } catch (\Exception $e) {
            \Log::error("Error al parsear el PDF: " . $e->getMessage());
            throw new Exception("Error al procesar el PDF.");
        }
    }

    private function procesarTexto(string $text, Ramo $ramo, Seguro $seguro): array
{
    $this->validarSeguroYramo($seguro, $ramo);
    
    $config = match($ramo->nombre) {
        'Automóviles' => $this->getConfigAutosResidentes(),
        'Accidentes y enfermedades' => $this->getConfigGastosMedicos(), // Ejemplo
        default => [],
    };

    $datosComunes = $this->procesarDatosComunes($text, $config);

    $mapaProcesadores = [
        'Vida' => [
            'Seguro de Vida Individual' => 'procesarVidaIndividual',
            'Grupo vida' => 'procesarGrupoVida',
            'Seguro de inversión' => 'procesarSeguroInversion',
            'De retiro' => 'procesarRetiro',
        ],
        'Daños' => [
            'Seguro de Daños empresa' => 'procesarDaniosEmpresa',
            'Casa' => 'procesarCasa',
            'Transporte' => 'procesarTransporte',
            'Embarcaciones' => 'procesarEmbarcaciones',
            'Rc Agentes' => 'procesarRcAgentes',
            'Maquinaria' => 'procesarMaquinaria',
        ],
        'Accidentes y enfermedades' => [
            'Gastos Médicos Mayores' => 'procesarGastosMedicos',
            'Accidentes Personales' => 'procesarAccidentesPersonales',
            'Accidentes Personales Escolares' => 'procesarEscolares',
        ],
        'Automóviles' => [
            'Autos pickup' => 'procesarAutosResidentes',
            'Camiones' => 'procesarCamiones',
            'Tractos' => 'procesarTractos',
        ],
    ];

    if (!isset($mapaProcesadores[$ramo->nombre][$seguro->nombre])) {
        throw new InvalidArgumentException("No existe un procesador definido para el seguro '{$seguro->nombre}' de ramo '{$ramo->nombre}'.");
    }

    $metodoProcesador = $mapaProcesadores[$ramo->nombre][$seguro->nombre];
    
    if (!method_exists($this, $metodoProcesador)) {
        throw new BadMethodCallException("Método procesador '{$metodoProcesador}' no existe.");
    }

    $datosEspecificos = $this->$metodoProcesador($text);

    return array_merge($datosComunes, $datosEspecificos);
}



private function validarSeguroYramo(Seguro $seguro, Ramo $ramo): void {
    if (!$seguro || !$ramo) {
        throw new InvalidArgumentException("Seguro o Ramo inválidos.");
    }
}

private function extraerDato(string $text, string $pattern, bool $trim = true): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return $trim ? trim($matches[1]) : $matches[1];
            
        }
        return null;


       
    }
private function procesarDatosComunes(string $text, array $config): array
{
    $datos = [];
      // Número de póliza
      $datos['numero_poliza'] = $this->extraerDato($text, $config['poliza_pattern']) ?? null;
   
      if (empty($datos['numero_poliza'])) {
          throw new Exception("Número de póliza es requerido");
      }
       // Nombre del cliente
    $datos['nombre_cliente'] = $this->extraerDato($text, $config['nombre_pattern']) ?? 'SIN NOMBRE';

    // RFC
    $datos['rfc'] = $this->extraerDato($text, $config['rfc_pattern']) ?? 'N/A';
   
    
    foreach ($config as $clave => $patron) {
        if (!str_ends_with($clave, '_pattern')) {
            continue; // saltamos claves que no son patrones (como 'meses')
        }

        $nombreCampo = str_replace('_pattern', '', $clave);

        if (preg_match($patron, $text, $match)) {
            $datos[$nombreCampo] = trim($match[1]);
        }
    }

    // Si quieres transformar las fechas con los meses, aquí va un ejemplo:
        foreach (['vigencia_inicio', 'vigencia_fin'] as $campoFecha) {
            if (!empty($datos[$campoFecha]) && isset($config['meses'])) {
                $datos[$campoFecha] = $this->formatearFecha($datos[$campoFecha], $config['meses']);
            } else {
                unset($datos[$campoFecha]); // Elimina la clave si está vacía o inválida
            }
        }
        
   // Extraer y convertir el total a pagar
   if (preg_match('/Prima Total:\s*\$([\d,\.]+)/i', $text, $matches)) {
    $datos['total_pagar'] = (float) str_replace([',', '$'], '', $matches[1]);
}

    return $datos;
}
private function formatearFecha(?string $fecha, array $meses): ?string
{
    if (!$fecha) return null;

    if (preg_match('/(\d{2})\/(\w{3})\/(\d{4})/', $fecha, $m)) {
        $dia = $m[1];
        $mes = strtoupper($m[2]);
        $anio = $m[3];
        $mesNumero = $meses[$mes] ?? null;
        if (!$mesNumero) return null;
        return "{$anio}-{$mesNumero}-{$dia}";
    }
    return null;
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
        'numero_agente_pattern' => '/Intermediario:\s*(\d+)\s+/i',
        'nombre_agente_pattern' => '/Intermediario:\s*\d+\s+([A-ZÁÉÍÓÚÑ\s]+)(?=Prima|$)/i',
        'meses' => [
            'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
        ],
    ];
}



  
private function procesarAutosResidentes(string $text): array
{
    $config = $this->getConfigAutosResidentes();
    
   $extraido= $this->procesarDatosComunes($text, $config);
  // dd($extraido);
  return $extraido;
}


}