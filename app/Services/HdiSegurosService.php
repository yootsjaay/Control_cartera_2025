<?php
namespace App\Services;

use App\Models\Ramo;
use App\Models\Seguro;
use App\Models\Compania;
use Smalot\PdfParser\Parser;
class HdiSegurosService{

    protected $parser;

    public function __construct(Parser $parser){
        $this->parser =$parser;
    }

    public function IdentifySelect($companiaId, $seguroId, $ramoId){
        $companiaId = Compania::findOrFail($companiaId);
        $seguroId= Seguro::findOrFail($seguroId);
        $ramoId = Ramo::findOrFail($ramoId);

        return compact('compania', 'seguro', 'ramo');

    }

    public function extractAutosPickup($pdfPath)
    {
        $pdf = $this->parser->parseFile($pdfPath);
        $text = $pdf->getText();
    
        // Datos generales extraídos directamente del texto
        $datos = [
            'numero_poliza' => $this->extractField($text, 'Número de Póliza:'),
            'vigencia_inicio' => $this->extractField($text, 'Vigencia Inicio:'),
            'vigencia_fin' => $this->extractField($text, 'Vigencia Fin:'),
            'forma_pago' => $this->extractFormaPago($text),
            'total_a_pagar' => $this->extractTotalAPagar($text),
        ];
    
        // Procesar cliente y agente
        $cliente = $this->processCliente($text);
        $agente = $this->processAgente($text);
    
        // Combinar todos los datos
        return array_merge($datos, [
            'cliente' => $cliente,
            'agente' => $agente,
        ]);
    }
    

    protected function extractField($text, $label)
    {
        if (preg_match('/' . preg_quote($label, '/') . '\s*(.+)/', $text, $matches)) {
            return trim($matches[1]);
        }
    
        return 'No encontrado';
    }
    protected function extractFormaPago($text)
{
    $formas_pago = ['SEMESTRAL EFECTIVO', 'TRIMESTRAL EFECTIVO', 'ANUAL EFECTIVO', 'MENSUAL EFECTIVO'];
    foreach ($formas_pago as $forma) {
        if (preg_match('/' . preg_quote($forma, '/') . '/i', $text)) {
            return $forma;
        }
    }
    return 'NO APLICA';

}

protected function extractTotalAPagar($text)
{
    if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
        return trim($matches[1]);
    }
    return 'No encontrado';
}
protected function processCliente($text)
{
    $rfc = $this->extractField($text, 'RFC:');
    $nombre = $this->extractField($text, 'Cliente:');

    return Cliente::firstOrCreate(
        ['rfc' => $rfc],
        ['nombre_completo' => $nombre]
    );
}
protected function processAgente($text)
{
    if (preg_match('/Agente:\s*([0-9]+)\s*([A-Z\s]+)/', $text, $matches)) {
        $numero = trim($matches[1]);
        $nombre = trim(preg_replace('/\s+/', ' ', $matches[2]));

        return Agente::firstOrCreate(
            ['numero_agentes' => $numero],
            ['nombre_agentes' => $nombre]
        );
    }

    return null; // Si no se encuentra información del agente
}


    
    


}