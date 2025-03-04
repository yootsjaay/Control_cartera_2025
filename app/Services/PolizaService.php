<?php

namespace App\Services;

use App\Models\{Cliente, Agente, Poliza, Compania, Seguro, Ramo};
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class PolizaService
{
    protected $seguroServiceFactory;

    public function __construct(SeguroServiceFactory $seguroServiceFactory)
    { 
        $this->seguroServiceFactory = $seguroServiceFactory;
    }

    public function crearPoliza(Request $request, UploadedFile $archivo): Poliza
    {
        // Validar que el archivo sea un PDF
        if ($archivo->getMimeType() !== 'application/pdf') {
            throw new Exception('El archivo debe ser un PDF válido.');
        }
    
        // Obtener el usuario autenticado
        $user = auth()->user();
        if (!$user) {
            throw new Exception('Usuario no autenticado.');
        }
    
        // Buscar compañía, seguro y ramo
        $compania = Compania::findOrFail($request->compania_id);
        $seguro = Seguro::findOrFail($request->seguro_id);
        $ramo = Ramo::findOrFail($request->ramo_id);
    
        // Crear el servicio de seguro
        $seguroService = $this->seguroServiceFactory->crearSeguroService($compania->slug);
    
        try {
            // Extraer datos del PDF
            $datosExtraidos = $seguroService->extractToData($archivo, $seguro, $ramo);
    
            // Validar datos extraídos
            $requiredFields = ['rfc', 'numero_poliza', 'nombre_cliente', 'numero_agente', 'nombre_agente', 'total_pagar'];
            foreach ($requiredFields as $field) {
                if (!isset($datosExtraidos[$field])) {
                    throw new Exception("El campo '$field' es requerido pero no se encontró en los datos extraídos.");
                }
            }
    
            // Crear o encontrar cliente
            $cliente = Cliente::create([
                'rfc' => $datosExtraidos['rfc'],
                'nombre_completo' => $datosExtraidos['nombre_cliente'] ?? 'Nombre no encontrado',
            ]);
    
            // Crear o encontrar agente
            $agente = Agente::firstOrCreate(
                ['numero_agentes' => $datosExtraidos['numero_agente']],
                ['nombre_agentes' => $datosExtraidos['nombre_agente']]
            );
    
            // Crear la póliza
            $poliza = Poliza::create([
                'numero_poliza' => $datosExtraidos['numero_poliza'],
                'vigencia_inicio' => $datosExtraidos['vigencia_inicio'] ?? null,
                'vigencia_fin' => $datosExtraidos['vigencia_fin'] ?? null,
                'forma_pago' => $datosExtraidos['forma_pago'] ?? null,
                'total_a_pagar' => $datosExtraidos['total_pagar'],
                'status' => 'activa',
                'cliente_id' => $cliente->id,
                'compania_id' => $compania->id,
                'seguro_id' => $seguro->id,
                'ramo_id' => $ramo->id,
                'user_id' => $user->id, // Asegúrate de incluir user_id aquí
            ]);
    
            // Guardar el archivo PDF
            $pdfPath = $archivo->store('polizas', 'public');
            $poliza->archivo_pdf = $pdfPath;
            $poliza->save();
    
            // Log de la póliza creada
            Log::info('Póliza creada:', [
                'numero_poliza' => $poliza->numero_poliza,
                'archivo' => $pdfPath,
                'user_id' => $user->id,
            ]);
    
            return $poliza;
        } catch (Exception $e) {
            // Log del error
            Log::error('Error al procesar el PDF ' . $archivo->getClientOriginalName() . ': ' . $e->getMessage(), [
                'exception' => $e,
                'datosExtraidos' => $datosExtraidos,
            ]);
            throw $e;
        }
    }
}