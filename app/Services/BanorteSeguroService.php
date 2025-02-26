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
  
    public function getSeguros()
    {
        $compania = Compania::where('slug', $slug)->firstOrFail();

    return Seguro::where('compania_id', $compania->id)->get(['id', 'nombre']);
    }

    /**
     * Obtiene los ramos disponibles para un seguro específico de Banorte.
     */
    public function getRamos($seguroId)
    {
        return Ramo::where('id_seguros', $seguroId)
            ->get(['id', 'nombre_ramo']);
    }
    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'banorte-seguros') {
            throw new InvalidArgumentException("El seguro seleccionado no pertenece a HDI.");
        }

        if ($ramo->id_seguros != $seguro->id) {
            throw new InvalidArgumentException("El ramo seleccionado no corresponde al seguro proporcionado.");
        }

        try {
            // Convertir el PDF a imágenes y extraer el texto con OCR
            $text = $this->extractTextWithOCR($archivo);

            \Log::info("Texto extraído:", ['data' => substr($text, 0, 500)]);
            return $this->procesarTexto($text, $ramo);
        } catch (Exception $e) {
            \Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF.");
        }
    }

    private function extractTextWithOCR(UploadedFile $archivo): string
    {
        $outputDir = storage_path('app/pdf_images');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Ruta del PDF original
        $pdfPath = $archivo->getPathname();
        $imagePattern = "{$outputDir}/page-%04d.png";

        // Convertir PDF a imágenes (1 imagen por página)
        exec("convert -density 300 {$pdfPath} -depth 8 -strip -background white -alpha off {$imagePattern}");

        // Obtener todas las imágenes generadas
        $images = glob("{$outputDir}/*.png");
        if (empty($images)) {
            throw new Exception("No se generaron imágenes del PDF.");
        }

        $fullText = '';

        foreach ($images as $image) {
            // Aplicar OCR con Tesseract en cada imagen
            $outputText = shell_exec("tesseract {$image} stdout -l spa+eng"); // Español + Inglés
            $fullText .= trim($outputText) . "\n";
            unlink($image); // Eliminar la imagen temporal
        }

        return trim($fullText);
    }


    private function procesarTexto(string $text, Ramo $ramo): array
    {
        switch (strtolower($ramo->slug)) {
            case 'gastos-medicos-mayores':
                return $this->procesarGastosMedicos($text);
            case 'automoviles-residentes':
                return $this->procesarAutos($text);
            case 'danios':
                return $this->procesarDanios($text);
            default:
                throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }
    


private function procesarGastosMedicos(string $text): array
{
    $datosExtraidos = [];

    //  Extraer número de póliza
    if (preg_match('/NO\.\s*DE\s*PÓLIZA[\s:]+(\d+)/i', $text, $match)) {
        $datosExtraidos['numero_poliza'] = $match[1];
    }

    //  Extraer fechas de vigencia
    if (preg_match('/VIGENCIA\s+DESDE.*?(\d{2}\/\d{2}\/\d{4}).*?(\d{2}\/\d{2}\/\d{4})/s', $text, $match)) {
        try {
            $datosExtraidos['fecha_inicio'] = Carbon::createFromFormat('d/m/Y', $match[1])->format('Y-m-d');
            $datosExtraidos['fecha_fin'] = Carbon::createFromFormat('d/m/Y', $match[2])->format('Y-m-d');
        } catch (Exception $e) {
            $datosExtraidos['fecha_inicio'] = null;
            $datosExtraidos['fecha_fin'] = null;
        }
    }

    //  Extraer información del asegurado
    if (preg_match_all('/([A-ZÁÉÍÓÚÑ\s]+)\t(\d{2}-\d{2}-\d{4})\s+(\d+)\s+([\d,]+)\s+M\.N\./', $text, $matches, PREG_SET_ORDER)) {
        $asegurados = [];
        foreach ($matches as $match) {
            $asegurados[] = [
                'nombre_cliente' => trim($match[1] ?? ''),
                'fecha_nacimiento' => $match[2] ?? null,
                'edad' => isset($match[3]) ? (int) $match[3] : null,
                'suma_asegurada' => isset($match[4]) ? str_replace(',', '', $match[4]) : null
            ];
        }
        $datosExtraidos['asegurados'] = $asegurados;
    }

    //  Verifica si se extrajo algo, si no, lanza error
    if (empty($datosExtraidos)) {
        throw new InvalidArgumentException("No se encontraron datos relevantes en el PDF.");
    }

    return $datosExtraidos;
}
private function procesarAutos(string $text): array
{

        $patterns = [
            'numero_poliza' => '/No\. de Póliza\s+(\d+)/',
            'nombre_cliente' => '/Nombre del Contratante:\s+([^\n]+?)(?:\s+R\.F\.C\.:|$)/', // Captura solo el nombre
            'nombre_agente' => '/Intermediario:\s+\d+\s+([A-ZÁÉÍÓÚÑ\s]+)/',
            'rfc' => '/R\.F\.C\.:?\s+([A-Z0-9]+)/', // Captura el RFC separado
            'fecha_emision' => '/Fecha de emisión:\s+\d{2}:\d{2}hrs\s+(\d{2}\/[A-Z]{3}\/\d{4})/',
            'fecha_fin' => '/Fin de vigencia:\s+\d{2}:\d{2}hrs\s+(\d{2}\/[A-Z]{3}\/\d{4})/',
            'forma_pago' => '/Forma de pago:\s+([A-ZÁÉÍÓÚÑa-z]+)/',
            'total_a_pagar' => '/Prima total:\s+\$ ([\d,\.]+)/',
        ];
    


        $data = [];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $valor = trim(preg_replace('/_{2,}|▶|\s+/', ' ', $matches[1])); // Limpia guiones bajos y caracteres raros
                
                if (!empty($valor)) {
                    $data[$key] = $valor;
                }
            }
        }

        return $data;
        }

        private function procesarDanios(String $text): array{
            $pattern =[];

            $data=[];
            
            
        }
        }