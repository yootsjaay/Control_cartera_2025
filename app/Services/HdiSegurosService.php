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

            //\Log::info("Texto extraído:", ['data' => substr($text, 0, 500)]);
           return $this->procesarTexto($text, $ramo);
           // dd($text);
        } catch (Exception $e) {
            \Log::error("Error al procesar el PDF: " . $e->getMessage());
            throw new InvalidArgumentException("No se pudo procesar el archivo PDF.");
        }
    }

    private function extractTextWithOCR(UploadedFile $archivo): string
{
    // Definir el directorio de salida
    $outputDir = storage_path('app/pdf_images');
    
    // Verificar si el directorio de salida existe, si no, crear
    if (!is_dir($outputDir)) {
        if (!mkdir($outputDir, 0777, true)) {
            throw new Exception("No se pudo crear el directorio para las imágenes.");
        }
    }

    // Ruta del PDF original
    $pdfPath = $archivo->getPathname();
    
    // Ruta de salida para las imágenes
    $imagePattern = "{$outputDir}/page-%04d.png";

    // Convertir PDF a imágenes (1 imagen por página)
    $convertCmd = "convert -density 300 {$pdfPath} -depth 8 -strip -background white -alpha off {$imagePattern}";
    exec($convertCmd, $output, $returnCode);

    // Verificar si el comando se ejecutó correctamente
    if ($returnCode !== 0) {
        throw new Exception("Hubo un error al convertir el PDF en imágenes.");
    }

    // Obtener todas las imágenes generadas
    $images = glob("{$outputDir}/*.png");
    if (empty($images)) {
        throw new Exception("No se generaron imágenes del PDF.");
    }

    $fullText = '';

    // Procesar cada imagen con OCR (Tesseract)
    foreach ($images as $image) {
        // Ejecutar OCR con Tesseract
        $outputText = shell_exec("tesseract {$image} stdout -l spa+eng"); // Español + Inglés
        
        // Verificar si Tesseract retornó un resultado
        if ($outputText === null) {
            throw new Exception("OCR no pudo procesar la imagen: {$image}");
        }

        $fullText .= trim($outputText) . "\n";

        // Eliminar la imagen temporal después de procesarla
        unlink($image);
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
        
        // Normalizar texto: eliminar múltiples espacios y saltos de línea
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
    
        // Extraer número de póliza
        $datos['numero_poliza'] = $this->extraerDato($text, '/Cotización:\s*(\d{10})/');
    
        // Extraer RFC (si existe)
        $datos['rfc'] = $this->extraerDato($text, '/RFC:\s*([A-Z0-9]{12,13})/i');
    
        // Extraer nombre del cliente (mejorado)
        $datos['nombre_cliente'] = $this->extraerDato($text, '/Nombre:\s*([A-Za-zÁÉÍÓÚáéíóúñÑ\s\.\-]+)/i');
    
        // Extraer agente (mejorado)
        if (preg_match('/Agente:\s*(\d+)\s+([A-ZÁÉÍÓÚÑa-záéíóúñ\s\.\-]+)/i', $text, $matches)) {
            $datos['numero_agente'] = trim($matches[1]);
            $datos['nombre_agente'] = trim($matches[2]);
        } else {
            $datos['numero_agente'] = null;
            $datos['nombre_agente'] = null;
        }
    
        // Extraer vigencia (mejorado)
        if (preg_match_all('/(\d{2}\/\d{2}\/\d{4})/', $text, $matches) && count($matches[1]) >= 2) {
            $datos['vigencia_inicio'] = $this->formatearFecha($matches[1][0]);
            $datos['vigencia_fin'] = $this->formatearFecha($matches[1][1]);
        } else {
            $datos['vigencia_inicio'] = null;
            $datos['vigencia_fin'] = null;
        }
    
        // Extraer forma de pago (mejorado)
        $datos['forma_pago'] = $this->extraerDato($text, '/(ANUAL\s+EFECTIVO|Pago\s+Fraccionado|Mensualidad)/i');
    
        // Extraer paquete (mejorado)
        $datos['paquete'] = $this->extraerDato($text, '/Paquete:\s*([\wÁÉÍÓÚáéíóúñÑ\s\-]+)/i');
    
        // Extraer montos (mejorado)
        $datos['prima_neta'] = $this->extraerMonto($text, '/Prima Neta.*?([\d,]+\.\d{2})/');
        $datos['total_a_pagar'] = $this->extraerMonto($text, '/Total a Pagar.*?([\d,]+\.\d{2})/');
    
        return $datos;
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
        $monto = $this->extraerDato($text, $pattern);
        return $monto ? (float) str_replace(',', '', $monto) : null;
    }
    
    private function formatearFecha(string $fecha): string
    {
        return \DateTime::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
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
