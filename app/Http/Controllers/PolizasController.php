<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Compania;
use App\Models\Seguro;
use App\Models\Poliza;
use App\Models\Agente;
use App\Models\Ramo;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use DateTime;
use Exception;
use App\Factories\SeguroFactory;
use App\Services\SeguroServiceInterface;
use Smalot\PdfParser\Parser;
use App\Http\Requests\StorePolizaRequest;



    class PolizasController extends Controller
    {
        
        public function index()
        {
            $polizas = Poliza::all();
            $companias= Compania::all();
            $seguros = Seguro::all();
            return view('polizas.index', compact('polizas', 'companias', 'seguros'));
        }

        /**
         * Show the form for creating a new resource.
         */
        public function create()
        {
            $clientes = Cliente::all();
            $companias = Compania::all();
            $seguros = Seguro::all();
            $polizas = Poliza::all();  
            
            return view('polizas.create', compact('clientes', 'companias', 'seguros','polizas' ));
        }



   // Método para obtener los seguros relacionados con una compañía
   public function obtenerSeguros($companiaId)
   {
       try {
           $seguros = Seguro::where('compania_id', $companiaId)->get(['id', 'nombre']);
           return response()->json($seguros);
       } catch (\Exception $e) {
           return response()->json(['error' => 'Error al cargar los seguros.'], 500);
       }
   }

   // Método para obtener los ramos relacionados con un seguro
   public function obtenerRamos($seguroId)
   {
       try {
           $ramos = Ramo::where('id_seguros', $seguroId)->get(['id', 'nombre_ramo']);
           return response()->json($ramos);
       } catch (\Exception $e) {
           return response()->json(['error' => 'Error al cargar los ramos.'], 500);
       }
   }
   public function store(StorePolizaRequest $request)
{
    try {
        // Aquí los datos ya están validados por StorePolizaRequest
        $compania = Compania::findOrFail($request->compania_id);
        $seguroService = SeguroFactory::crearSeguroService($compania->slug);

        // Procesamiento de los archivos PDF
        if ($request->has('pdf')) {
            foreach ($request->file('pdf') as $archivo) {
                // Extraer datos del PDF
                $text = $seguroService->extractToData($archivo);
                dd($text);  // Muestra el texto extraído para depuración
            }
        }

        return redirect()->route('polizas.index')->with('success', 'Póliza cargada exitosamente.');
    } catch (\Exception $e) {
        \Log::error('Error al procesar el PDF: ' . $e->getMessage());
        return redirect()->back()->withErrors([
            'general' => 'Ocurrió un error al procesar el PDF. Intenta nuevamente.'
        ]);
    }
}

   
   

    



        
    
        // Función para convertir la fecha
        public function convertirFecha($fecha)
        {
            try {
                $fechaObj = DateTime::createFromFormat('d/m/Y', $fecha);
                if ($fechaObj === false) {
                    // Manejo de error si el formato no es válido
                    return null;
                }
                return $fechaObj->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }
        

    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
     
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $poliza = Poliza::findOrFail($id);

        // Eliminar el archivo si existe
        if ($poliza->archivo_pdf && Storage::exists('public/polizas/' . $poliza->archivo_pdf)) {
            Storage::delete('public/polizas/' . $poliza->archivo_pdf);
        }

        $poliza->delete();

        return redirect()->route('polizas.index')->with('success', 'Póliza eliminada correctamente.');
    }


  
}
