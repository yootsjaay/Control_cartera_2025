<?php

namespace App\Services;

use App\Models\{Cliente, Agente, Poliza, Compania, Seguro, Ramo};
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception; // Importa la clase Exception

class PolizaService
{
    protected $seguroServiceFactory;

    public function __construct(SeguroServiceFactory $seguroServiceFactory)
    { 
        $this->seguroServiceFactory = $seguroServiceFactory;
    }

    public function crearPoliza(Request $request, UploadedFile $archivo): Poliza
    {
        $compania = Compania::findOrFail($request->compania_id);
        $seguro = Seguro::findOrFail($request->seguro_id);
        $ramo = Ramo::findOrFail($request->ramo_id); // Asegúrate de que ramo_id sea válido
    
        $seguroService = $this->seguroServiceFactory->crearSeguroService($compania->slug);
    
        try {
            $datosExtraidos = $seguroService->extractToData($archivo, $seguro, $ramo);
    
            // Validar datos extraídos
            if (!isset($datosExtraidos['rfc']) || !isset($datosExtraidos['numero_poliza'])) {
                throw new Exception('Datos extraídos del PDF inválidos.');
            }
    
            $nombreCompleto = $datosExtraidos['nombre_cliente'] ?? 'Nombre no encontrado';
            Log::info('Nombre del cliente:', ['nombre' => $nombreCompleto]);
    
            $cliente = Cliente::create(['rfc' => $datosExtraidos['rfc'], 'nombre_completo' => $nombreCompleto]);
    
            // Crear o encontrar Agente
            $agente = Agente::where('numero_agentes', $datosExtraidos['numero_agente'])->first();
            if (!$agente) {
                $agente = Agente::create(['numero_agentes' => $datosExtraidos['numero_agente'], 'nombre_agentes' => $datosExtraidos['nombre_agente']]);
            }
    
            // Crear la póliza
            $poliza = Poliza::create([
                'numero_poliza' => $datosExtraidos['numero_poliza'] ?? null,
                'vigencia_inicio' => $datosExtraidos['vigencia_inicio'] ?? null,
                'vigencia_fin' => $datosExtraidos['vigencia_fin'] ?? null,
                'forma_pago' => $datosExtraidos['forma_pago'] ?? null,
                'total_a_pagar' => $datosExtraidos['total_pagar'],          
                'status' => 'activa',
                'cliente_id' => $cliente->id,
                'compania_id' => $compania->id,
                'seguro_id' => $seguro->id,
                'ramo_id' => $ramo->id, 
            ]);
    
            $pdfPath = $archivo->store('polizas', 'public');
            $poliza->archivo_pdf = $pdfPath;
            $poliza->save();
    
            Log::info('Póliza creada:', ['numero_poliza' => $poliza->numero_poliza, 'archivo' => $pdfPath]);
    
            return $poliza;
        } catch (Exception $e) {
            Log::error('Error al procesar el PDF ' . $archivo->getClientOriginalName() . ': ' . $e->getMessage());
            throw $e;
        }
    }
}