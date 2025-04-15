<?php

namespace App\Services;

use App\Models\Seguro;
use App\Models\Ramo;
use App\Services\Contracts\SeguroServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Exception\PdfParserException;
use InvalidArgumentException;
use DateTime;
use Exception;

class ThonaSegurosService implements SeguroServiceInterface
{
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        $this->validarSeguroYramo($seguro, $ramo);

        if ($archivo->getClientMimeType() !== 'application/pdf') {
            throw new InvalidArgumentException("El archivo no es un PDF válido.");
        }

        try {
            $texto = $this->extractText($archivo);
            return $this->procesarTexto($texto, $ramo, $seguro);
        } catch (PdfParserException $e) {
            throw new Exception("Error al procesar el PDF: " . $e->getMessage());
        } catch (Exception $e) {
            throw new InvalidArgumentException("Error al procesar el PDF: " . $e->getMessage());
        }
    }

    private function extractText(UploadedFile $archivo): string
    {
        try {
            $pdf = $this->parser->parseFile($archivo->getPathname());
            return $pdf->getText();
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Error al leer el archivo PDF: " . $e->getMessage());
        }
    }

    private function procesarTexto(string $texto, Ramo $ramo, Seguro $seguro): array
    {
        $datosComunes = $this->procesarDatosComunes($texto);
        
        $mapaProcesadores = [
            'Vida' => [
                'Grupo vida' => 'procesarGrupoVida',
                'Seguro de Vida Individual' => 'procesarVidaIndividual', 
                'Seguro de inversión' => 'procesarInversion',
                'De retiro' => 'procesarRetiro'
            ],
            'Accidentes y enfermedades' => [
                'Gastos Médicos Mayores' => 'procesarGastosMedicos', 
                'Accidentes Personales' => 'procesarAccidentesPersonales', 
                'Accidentes Personales Escolares' => 'procesarAccidentesEscolares'
            ]
        ];

        if (!isset($mapaProcesadores[$ramo->nombre][$seguro->nombre])) {
            throw new InvalidArgumentException("No existe un procesador para esta combinación de ramo y seguro.");
        }

        $metodoProcesador = $mapaProcesadores[$ramo->nombre][$seguro->nombre];
        $datosEspecificos = $this->{$metodoProcesador}($texto);

        return array_merge($datosComunes, $datosEspecificos);
    }

    private function validarSeguroYramo(Seguro $seguro, Ramo $ramo): void
    {
        if (!$seguro->exists || !$ramo->exists) {
            throw new InvalidArgumentException("El seguro o ramo no están registrados.");
        }
    }

    private function procesarDatosComunes(string $texto): array
    {
        $datos = [
            'rfc' => 'N/A',
            'numero_poliza' => 'N/A',
            'nombre_cliente' => 'N/A',
            'numero_agente' => 'N/A',
            'nombre_agente' => 'N/A',
            'total_pagar' => 0.0,
            'vigencia_inicio' => null,
            'vigencia_fin' => null,
            'forma_pago' => 'N/A'
        ];
    
        // 1. Extraer RFC
        if (preg_match('/DOMICILIO DEL CONTRATANTE\s*:\s*([A-Z0-9]{12})(?:RFC)?/i', $texto, $matches)) {
            $datos['rfc'] = trim($matches[1]);
        }
    
        // 2. Extraer número de póliza (ahora como 65016-00)
        if (preg_match('/PÓLIZA:\s*([\d-]+)\s*/', $texto, $matches)) {
            $datos['numero_poliza'] = trim($matches[1]);
        }
    
        // 3. Extraer nombre del cliente
        // Primero intentamos el nombre del contratante principal
        if (preg_match('/NOMBRE DEL CONTRATANTE\s*([^\n]+)/', $texto, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]);
        }
        // Si también necesitas el subgrupo, puedes agregar una segunda verificación
        if (preg_match('/SUBGRUPO\tNOMBRE DE SUBGRUPO\tRFC ASEGURADOS\s*\n([^\t]+)\t[A-Z0-9]+\s*\d*/', $texto, $matches)) {
            $datos['nombre_cliente'] .= ' - ' . trim($matches[1]);
        }
    
        // 4. Extraer número de agente (ahora como 472, 3 dígitos)
        if (preg_match('/AGENTE:\s*(\d{3,4})\s*/', $texto, $matches)) {
            $datos['numero_agente'] = $matches[1];
        }
    
        // 5. Extraer total a pagar
        if (preg_match('/PRIMA TOTAL\s+([\d,]+\.\d{2})/', $texto, $matches)) {
            $datos['total_pagar'] = (float) str_replace(',', '', $matches[1]);
        }
    
        // 6. Extraer fechas de vigencia
        if (preg_match('/DESDE\s+(\d{2}\/\d{2}\/\d{4})\s+HASTA\s+(\d{2}\/\d{2}\/\d{4})\s*/', $texto, $matches)) {
            $dateInicio = DateTime::createFromFormat('d/m/Y', $matches[1]);
            $dateFin = DateTime::createFromFormat('d/m/Y', $matches[2]);
            $datos['vigencia_inicio'] = $dateInicio ? $dateInicio->format('Y-m-d') : null;
            $datos['vigencia_fin'] = $dateFin ? $dateFin->format('Y-m-d') : null;
        }
    
        // 7. Extraer forma de pago
        if (preg_match('/FORMA PAGO:\s*([A-Z]+)/', $texto, $matches)) {
            $datos['forma_pago'] = trim($matches[1]);
        }
    
        return $datos;
    }
    private function procesarGrupoVida(string $texto): array
    {
       $datos=$this->procesarDatosComunes($texto);
        dd($datos);
       // return [];
    }

    // Implementar el resto de métodos de procesamiento
    private function procesarVidaIndividual(string $texto): array { return []; }
    private function procesarInversion(string $texto): array { return []; }
    private function procesarRetiro(string $texto): array { return []; }
    private function procesarGastosMedicos(string $texto): array { return []; }
    private function procesarAccidentesPersonales(string $texto): array { return []; }
    private function procesarAccidentesEscolares(string $texto): array { return []; }
}