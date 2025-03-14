<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use App\Models\Seguro;
use App\Models\Ramo;
use InvalidArgumentException;
use Exception;
use DateTime;

class GmxSeguroService implements SeguroServiceInterface
{
    protected $parser;
    protected $ramoProcesadores;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->InicializarRamoProcesardores(); // Inicializar el array
    }

    private function InicializarRamoProcesardores()
    {
        $ramos = Ramo::all(); // Obtener todos los ramos desde la base de datos
        $this->ramoProcesadores = [];

        foreach ($ramos as $ramo) {
            $metodo = 'procesar' . str_replace(' ', '', ucwords(str_replace('-', ' ', $ramo->slug)));
            $this->ramoProcesadores[strtolower($ramo->slug)] = $metodo;
        }
    }

    public function extractToData(UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        if ($seguro->compania->slug !== 'gmx-seguros') { // Cambiar la validación a GMX
            throw new InvalidArgumentException("El seguro seleccionado no pertenece a GMX.");
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
        $slug = strtolower($ramo->slug);
        if (isset($this->ramoProcesadores[$slug]) && method_exists($this, $this->ramoProcesadores[$slug])) {
            $metodo = $this->ramoProcesadores[$slug];
            return $this->$metodo($text);
        } else {
            throw new InvalidArgumentException("El ramo {$ramo->slug} no tiene un procesador definido.");
        }
    }

    private function procesarDatosComunes(string $text): array // Agregar el parametro $text
    {
        // Implementar la lógica de procesamiento para datos comunes
        return [];
    }

    private function procesarTrasnporteCarga(string $text): array // Agregar el parametro $text
    {
        // Implementar la lógica de procesamiento para transporte de carga
        return [];
    }
}