<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Carbon\Carbon;
use Exception;

class HdiSegurosService implements SeguroServiceInterface
{
    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'hdi_seguros') {
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
        // Normalizamos el slug a minúsculas y lo comparamos con valores estandarizados
        $slug = strtolower($ramo->slug);

        switch ($slug) {
            case 'medica-total-plus':
                return $this->procesarGastosMedicosTotal($text);
            case 'vehiculos-residentes-hdi-autos-y-pick-ups': // Usar un slug estandarizado en BD
                return $this->procesarAutos($text);
            case 'danios':
                return $this->procesarDanios($text);
            default:
                throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }

    private function procesarAutos(string $text): array
{   
    $datos = [];

    // Extraer número de póliza
    $datos['numero_poliza'] = $this->extraerDato($text, '/Cotización:\s*(\d+)/');

    // Extraer RFC
    $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})/');

    // Extraer nombre del cliente
    $datos['nombre_cliente'] = $this->extraerDato($text, '/Nombre:\s*([A-Za-zÁÉÍÓÚáéíóúñÑ\s\.\-]+)/');

    // Extraer agente (número y nombre por separado)
    if (preg_match('/Agente:\s*(\d+)\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)/', $text, $matches)) {
        $datos['numero_agente'] = trim($matches[1]);
        $datos['nombre_agente'] = trim($matches[2]);
    } else {
        $datos['numero_agente'] = null;
        $datos['nombre_agente'] = null;
    }

    // Extraer vigencia (Inicio y Fin)
    if (preg_match_all('/(\d{2}\/\d{2}\/\d{4})/', $text, $matches) && count($matches[1]) >= 2) {
        $datos['vigencia_inicio'] = $matches[1][0];
        $datos['vigencia_fin'] = $matches[1][1];
    } else {
        $datos['vigencia_inicio'] = null;
        $datos['vigencia_fin'] = null;
    }

    // Extraer forma de pago
    $datos['forma_pago'] = $this->extraerDato($text, '/(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/');

    // Extraer paquete
    $datos['paquete'] = $this->extraerDato($text, '/Paquete:\s*([\wÁÉÍÓÚáéíóúñÑ\s]+)/');

    // Extraer prima neta y total a pagar
    $datos['prima_neta'] = $this->extraerDato($text, '/Prima Neta.*?([\d,]+\.\d{2})/');
    $datos['total_a_pagar'] = $this->extraerDato($text, '/Total a Pagar.*?([\d,]+\.\d{2})/');

    // Normalizar valores numéricos
    $datos['prima_neta'] = isset($datos['prima_neta']) ? floatval(str_replace(',', '', $datos['prima_neta'])) : null;
    $datos['total_a_pagar'] = isset($datos['total_a_pagar']) ? floatval(str_replace(',', '', $datos['total_a_pagar'])) : null;

    return $datos;
}

private function extraerDato($text, $pattern, $default = null)
{
    if (preg_match($pattern, $text, $matches)) {
        return trim($matches[1]);
    }
    return $default;
}







    private function procesarGastosMedicosTotal(String $text): array
     {
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

            
                
                return $datos;
            }
}
