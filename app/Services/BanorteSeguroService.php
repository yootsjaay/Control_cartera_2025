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
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser= $parser;
    }



    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        $this->validaSeguroYamo($seguro, $ramo);
        
        //verificacion que el archovo sea un PDF
        if($archivo->getClientMimeType() != 'application/pdf'){
            throw new InvalidArgumentException('El archivo proporcionado no es un pdf');
            try {
                $text = $this->extractText($archivo);
                Log::info("Texto Extraido Existosamente",[
                    'seguro' => $seguro->nombre,
                    'ramo'=> $ramo->nombre,
                    'data'=> substr($text, 0, 500),
                ]);
                return $this->procesarText($text, $ramo, $seguro);
            }catch (PdfParseException $e){
                Log::error("Error al extraer texto del PDF:". $e->getMessage());
                throw new InvalidArgumentException('no se pudo procesar el archivo PDF');
            }catch (Exception $e){
                Log::error("Error al procesar Pdf" . $e->getMessage());
                throw InvalidArgumentException('No se pudo procesar el PDF');
            }
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
       $datosComunes = $this->procesarDatosComunes($text);

       //mapear ramos y seguros especificos
       $mapaProcesadores = [
            'Vida' => [
                'Seguro de Vida Individual',
                ' Grupo vida', 
                'Seguro de inversión',
                'De retiro',
            ],
            'Daños'=> [
                ' Seguro de Daños empresa',
                 'Casa',
                 'Transporte',
                 'Embarcaciones',
                 'Rc Agentes',
                 'Maquinaria',
            ],
            'Accidentes y enfermedades'=>[
                'Gastos Médicos Mayores', 
                'Accidentes Personales', 
                'Accidentes Personales Escolares'
            ],
            'Automóviles'=>[
                'Autos pickup'=> 'procesarAutosResidentes',
                'Camiones', 
                'Tractos'
            ],
        ];
        if(!isset($mapaProcesadores[$ramo->nombre][$seguro->nombre])){
            throw  new InvalidArgumentException("No existe un procesador definido para el seguro'{$seguro->nombre}' de ramo '{$ramo->nombre}'.");
        }
        $metodoProcesadr = $mapaProcesadores[$ramo->nombre][$seguro->nombre];
        $datosEspecificos = $this->metodoProcesador($text);

        return array_merge($datosComunes, $datosEspecificos);
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

private function procesarDatosComunes(string $text, array $config): array
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