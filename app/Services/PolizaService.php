<?php

namespace App\Services;

use App\Models\{Cliente, Agente, Poliza, Compania, Seguro, Ramo};
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Services\Factories\SeguroServiceFactory;

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
        // Validaciones del request (ya están cubiertas en StorePolizaRequest)
        $this->validarArchivo($archivo);

        // Obtener usuario autenticado
        $user = $this->obtenerUsuarioAutenticado();

        // Obtener los modelos asociados (la validación ya garantiza su existencia)
        $compania = Compania::findOrFail($request->compania_id);
        $seguro = Seguro::findOrFail($request->seguro_id);
        $ramo = Ramo::findOrFail($request->ramo_id);

        // Usamos el nombre de la compañía para obtener el servicio correspondiente
        $seguroService = $this->seguroServiceFactory->crearSeguroService($compania->nombre);
        $datosExtraidos = $this->extraerDatos($seguroService, $archivo, $seguro, $ramo);

        // Crear o actualizar cliente y agente
        $cliente = $this->crearCliente($datosExtraidos);
        $agente = $this->crearAgente($datosExtraidos);

        // Guardar la póliza
        return $this->guardarPoliza($request, $archivo, $datosExtraidos, $cliente, $agente, $user, $compania, $seguro, $ramo);
    }

    protected function validarArchivo(UploadedFile $archivo): void
    {
        if ($archivo->getMimeType() !== 'application/pdf') {
            throw new Exception('El archivo debe ser un PDF válido.');
        }
    }

    protected function obtenerUsuarioAutenticado()
    {
        $user = auth()->user();
        if (!$user) {
            throw new Exception('Usuario no autenticado.');
        }
        return $user;
    }

    protected function extraerDatos($seguroService, UploadedFile $archivo, Seguro $seguro, Ramo $ramo): array
    {
        $datosExtraidos = $seguroService->extractToData($archivo, $seguro, $ramo);
        $requiredFields = ['rfc', 'numero_poliza', 'nombre_cliente', 'numero_agente', 'nombre_agente', 'total_pagar'];
        
        foreach ($requiredFields as $field) {
            if (!isset($datosExtraidos[$field])) {
                throw new Exception("El campo '$field' es requerido pero no se encontró en los datos extraídos.");
            }
        }

        return $datosExtraidos;
    }

    protected function crearCliente(array $datosExtraidos): Cliente
    {
        return Cliente::firstOrCreate(
            ['rfc' => $datosExtraidos['rfc']],
            ['nombre_completo' => $datosExtraidos['nombre_cliente'] ?? 'Nombre no encontrado']
        );
    }

    protected function crearAgente(array $datosExtraidos): Agente
    {
        return Agente::firstOrCreate(
            ['numero_agentes' => $datosExtraidos['numero_agente']],
            ['nombre_agentes' => $datosExtraidos['nombre_agente']]
        );
    }
    protected function guardarPoliza(Request $request, UploadedFile $archivo, array $datosExtraidos, Cliente $cliente, Agente $agente, $user, Compania $compania, Seguro $seguro, Ramo $ramo): Poliza
    {
        try {
            return \DB::transaction(function () use ($request, $archivo, $datosExtraidos, $cliente, $agente, $user, $compania, $seguro, $ramo) {
                // Crear la póliza en la base de datos
                $poliza = Poliza::create([
                    'numero_poliza' => $datosExtraidos['numero_poliza'],
                    'vigencia_inicio' => $datosExtraidos['vigencia_inicio'] ?? null,
                    'vigencia_fin' => $datosExtraidos['vigencia_fin'] ?? null,
                    'forma_pago' => $datosExtraidos['forma_pago'] ?? null,
                    'total_a_pagar' => $datosExtraidos['total_pagar'],
                    'status' => 'activa',
                    'cliente_id' => $cliente->id,
                    'compania_id' => $compania->id,
                    'user_id' => $user->id,
                ]);
    
                // Guardar el archivo PDF
                $pdfPath = $archivo->store('polizas', 'public');
                $poliza->archivo_pdf = $pdfPath;
                $poliza->save();
    
                // Registrar la creación de la póliza
                Log::info('Póliza creada:', [
                    'numero_poliza' => $poliza->numero_poliza,
                    'archivo' => $pdfPath,
                    'user_id' => $user->id,
                ]);
    
                return $poliza;
            });
        } catch (Exception $e) {
            Log::error('Error al procesar el PDF ' . $archivo->getClientOriginalName(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'datosExtraidos' => $datosExtraidos,
            ]);
            throw $e;
        }
    }
    
}