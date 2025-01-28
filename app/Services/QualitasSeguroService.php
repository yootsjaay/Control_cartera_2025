<?php
namespace App\Services;

use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
class QualitasSeguroSerivice implements SeguroServiceInterface
{
    public function extractToData($pdfFile)
    {
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($pdfFile->getPathname());

        // Extraer el texto del PDF
        $text = $pdf->getText();
       // Identificar el seguro o ramo
        if (stripos($text, 'HDI Autos') !== false) {
            // Procesar seguro de autos
            return $this->AutosQualitas($text);
        } elseif (stripos($text, 'Gastos Médicos') !== false) {
            // Procesar seguro de gastos médicos
            return $this->GastosMedicosQualitas($text);
        } elseif (stripos($text, 'Daños Materiales') !== false) {
            // Procesar seguro de daños
            return $this->DaniosMaterialesQualitas($text);
        } else {
            // Seguro o ramo no identificado
            return [
                'error' => 'No se pudo identificar el seguro o ramo en el documento.'
            ];
        }

      
      return $text;  
     
    }
}