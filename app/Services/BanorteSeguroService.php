<?php
namespace App\Services;

use Smalot\PdfParser\Parser;

class BanorteSeguroService implements SeguroServiceInterface
{
    public function extractToData($pdfFile)
    {
        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($pdfFile->getPathname());

        // Extraer el texto del PDF
        $texto = $pdf->getText();

        // Aquí puedes hacer el procesamiento necesario del texto extraído
        return $texto;  // O alguna lógica adicional según la compañía
    }
}
