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
use thiagoalessio\TesseractOCR\TesseractOCR;
use \Imagick; 
use DateTime;
use Exception;
use Smalot\PdfParser\Parser;
use App\Services\HdiSegurosService;



    class PolizasController extends Controller
    {
        protected $hdiSegurosService;

    public function __construct(HdiSegurosService $hdiSegurosService)
    {
        $this->hdiSegurosService = $hdiSegurosService;
    }
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

    public function store(Request $request)
{
    // Validación de los datos enviados desde el formulario
    $request->validate([
        'compania_id' => 'required|exists:companias,id',
        'seguro_id' => 'required|exists:seguros,id',
        'ramo_id' => 'required|exists:ramos,id',
        'pdf' => 'required|array', // para múltiples archivos
        'pdf.*' => 'mimes:pdf|max:10000', // Validar que cada archivo sea PDF y no supere los 10MB
    ]);

    try {
        // Verificar que el seguro pertenece a la compañía seleccionada
        $seguroValido = Seguro::where('id', $request->seguro_id)
            ->where('compania_id', $request->compania_id)
            ->exists();

        if (!$seguroValido) {
            return redirect()->back()->withErrors('El seguro seleccionado no pertenece a la compañía seleccionada.');
        }

        // Verificar que el ramo pertenece al seguro seleccionado
        $ramoValido = Ramo::where('id', $request->ramo_id)
            ->where('seguro_id', $request->seguro_id)
            ->exists();

        if (!$ramoValido) {
            return redirect()->back()->withErrors('El ramo seleccionado no pertenece al seguro seleccionado.');
        }

        foreach ($request->file('pdf') as $file) {
            // Almacenar el archivo PDF
            $rutaArchivo = $file->store('Polizas', 'public');

            // Inicializar el parser de PDF
            $parser = new Parser();

            try {
                // Parsear el PDF
                $pdfParsed = $parser->parseFile(storage_path('app/public/' . $rutaArchivo));
                $pages = $pdfParsed->getPages();

                if (!$pages || count($pages) === 0) {
                    throw new \Exception('El archivo PDF no contiene páginas legibles.');
                }

                $allText = '';
                foreach ($pages as $page) {
                    $allText .= $page->getText();
                }

                // Crear la póliza en la base de datos
                Poliza::create([
                    'compania_id' => $request->compania_id,
                    'seguro_id' => $request->seguro_id,
                    'ramo_id' => $request->ramo_id,
                    'archivo_pdf' => $rutaArchivo,
                    'contenido_texto' => $allText, // Almacenar el texto extraído (si lo necesitas)
                    'creado_por' => auth()->user()->id // Registrar quién subió la póliza
                ]);
            } catch (\Exception $e) {
                // Manejar errores en el análisis del PDF
                \Log::error('Error al analizar el archivo PDF: ' . $e->getMessage());
                return redirect()->back()->withErrors('Error al procesar el archivo PDF. Asegúrate de que sea un archivo válido.');
            }
        }

        // Redireccionar con un mensaje de éxito
        return redirect()->route('polizas.index')->with('success', 'Póliza(s) cargada(s) exitosamente.');
    } catch (\Exception $e) {
        // Manejar errores generales
        \Log::error('Error al guardar la póliza: ' . $e->getMessage());
        return redirect()->back()->withErrors('Ocurrió un error al guardar la póliza. Intenta nuevamente.');
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
        //extraccion de compania HDI
        private function extraerDatosHdiAutos($text){ 
        $datos = [];
    
        // Extraer número de póliza
        if (preg_match('/Póliza:\s*([0-9\-]+)/', $text, $matches)) {
            $datos['numero_poliza'] = trim($matches[1]);
        } else {
            $datos['numero_poliza'] = 'No encontrado';
        }
    
        // Extraer nombre del cliente
        if (preg_match('/\n([A-Z\s]+)\n\s*RFC:/', $text, $matches)) {
            $datos['nombre_cliente'] = trim($matches[1]);
        } else {
            $datos['nombre_cliente'] = 'No encontrado';
        }
    
        // Extraer RFC
        if (preg_match('/RFC:\s*([A-Z0-9]+)/', $text, $matches)) {
            $datos['rfc'] = $matches[1];
        } else {
            $datos['rfc'] = 'No encontrado';
        }
    
        // Extraer marca, modelo y año del vehículo
        if (preg_match('/([A-Z\s]+)\s*,\s*([A-Z\s]+)\s*([0-9]{4})/', $text, $matches)) {
            $marca = trim($matches[1]);
            if (strpos($marca, 'NO APLICA') !== false) {
                $marca = str_replace('NO APLICA', '', $marca);
                $marca = trim($marca);
            }
            $datos['marca'] = $marca;
            $datos['modelo'] = trim($matches[2]);
            $datos['anio'] = trim($matches[3]);
        } else {
            $datos['marca'] = 'No encontrado';
            $datos['modelo'] = 'No encontrado';
            $datos['anio'] = 'No encontrado';
        }
    
        // Forma de pago
        $formas_pago = ['SEMESTRAL EFECTIVO', 'TRIMESTRAL EFECTIVO', 'ANUAL EFECTIVO', 'MENSUAL EFECTIVO'];
        foreach ($formas_pago as $forma) {
            if (preg_match('/' . preg_quote($forma, '/') . '/i', $text)) {
                $datos['forma_pago'] = $forma;
                break;
            }
        }
        if (!isset($datos['forma_pago'])) {
            $datos['forma_pago'] = 'NO APLICA';
        }
    
        // Extraer el total a pagar
        if (preg_match('/([0-9,]+\.\d{2})\s*Total a Pagar/', $text, $matches)) {
            $datos['total_pagar'] = trim($matches[1]);
        } else {
            $datos['total_pagar'] = 'No encontrado';
        }
    
        // Extraer agente (número y nombre)
        if (preg_match('/Agente:\s*([0-9]+)\s*([A-Z\s]+)\s*(?=\n\s*Descripción|$)/', $text, $matches)) {
            $datos['numero_agente'] = trim($matches[1]);
            $nombre_agente = trim(preg_replace('/\s+/', ' ', $matches[2]));
            $datos['nombre_agente'] = $nombre_agente;
        } else {
            $datos['numero_agente'] = 'No encontrado';
            $datos['nombre_agente'] = 'No encontrado';
        }
    
        // Extraer recibos (fechas de pago, importes, vigencia)
        $pattern = '/(\d{2}-\w{3}-\d{4})al\d+\s+([\d,]+\.\d{2})\s+(\d{2}-\w{3}-\d{4})\s+(\d{2}-\w{3}-\d{4})/';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
        
        $recibos = [];
        foreach ($matches as $match) {
            $recibos[] = [
                'fecha_pago' => $this->convertirFecha($match[1]),
                'importe' => floatval(str_replace(',', '', $match[2])),
                'vigencia_inicio' => $this->convertirFecha($match[3]),
                'vigencia_fin' => $this->convertirFecha($match[4]),
            ];
        }
        
        // Agregar los recibos a los datos extraídos
        $datos['recibos'] = $recibos;
    
        // Retornar todos los datos extraídos
        return $datos;
    }
    
        private function extraerDatosHdiGastos($text) {
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

        
            dd($datos);
            return $datos;
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
