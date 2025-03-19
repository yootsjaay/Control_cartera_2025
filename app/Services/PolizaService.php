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

    /**
     * Crea una nueva póliza a partir de un archivo PDF y datos del request.
     *
     * @param Request $request
     * @param UploadedFile $archivo
     * @return Poliza
     * @throws Exception
     */
    public function crearPoliza(Request $request, UploadedFile $archivo): Poliza
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'seguro_id' => 'required|exists:seguros,id',
            'ramo_id' => 'required|exists:ramos,id',
        ]);

        $this->validarArchivo($archivo);
        $user = $this->obtenerUsuarioAutenticado();

        $compania = Compania::findOrFail($request->compania_id);
        $seguro = Seguro::findOrFail($request->seguro_id);
        $ramo = Ramo::findOrFail($request->ramo_id);

        $seguroService = $this->seguroServiceFactory->crearSeguroService($compania->slug);
        $datosExtraidos = $this->extraerDatos($seguroService, $archivo, $seguro, $ramo);

        $cliente = $this->crearCliente($datosExtraidos);
        $agente = $this->crearAgente($datosExtraidos);

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
     // dd($datosExtraidos);
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
                'user_id' => $user->id,
            ]);

            $pdfPath = $archivo->store('polizas', 'public');
            $poliza->archivo_pdf = $pdfPath;
            $poliza->save();

            Log::info('Póliza creada:', [
                'numero_poliza' => $poliza->numero_poliza,
                'archivo' => $pdfPath,
                'user_id' => $user->id,
            ]);

            return $poliza;
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