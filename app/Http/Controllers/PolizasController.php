<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Compania;
use App\Models\TipoSeguro;
use App\Models\Poliza;
use App\Models\Agente;
use App\Models\PagosSubsecuente;
use App\Models\Seguro;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;
use \Imagick; 
use DateTime;
use Exception;
use Smalot\PdfParser\Parser;


    class PolizasController extends Controller
    {
        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            $polizas = Poliza::all();
            $companias= Compania::all();
            $seguros = Seguro::all();
            return view('polizas.index', compact('polizas', 'companias', 'seguros'));
        }


        // PolizaController.php

        public function obtenerSubtipos($id)
        {
            $ramo = Ramo::where('id_ramo', $id)->get();
            return response()->json($subtipos); // Devuelve los subtipos como JSON
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

    public function store(Request $request)
    {
        // Validar PDF
        $request->validate([
            'pdf.*' => 'mimes:pdf|max:10000',
            'compania_id' => 'required|exists:companias,id',
            'id_ramo' => 'required|exists:tipo_seguros,id',
        ]);
    
        $compania_id = $request->input('compania_id');
        $compania = Compania::find($compania_id);
    
        $tipo_seguro = $request->input('tipo_seguro_id');
        $tipoSeguro = TipoSeguro::find($tipo_seguro);
    
        foreach ($request->file('pdf') as $file) {
            // Almacenar el archivo PDF
            $pdfPath = $file->store('Polizas', 'public');
    
            // Inicializar el parser de PDF
            $parser = new Parser();
    
            try {
                // Parsear el PDF
                $pdfParsed = $parser->parseFile(storage_path('app/public/' . $pdfPath));
                $pages = $pdfParsed->getPages();
    
                if (!$pages || count($pages) === 0) {
                    throw new \Exception('El archivo PDF no contiene páginas legibles.');
                }
    
                $allText = '';
                foreach ($pages as $page) {
                    $allText .= $page->getText();
                }
            //  dd($allText);
    
                // Procesar según compañía y tipo
                $metodos = [
                    'HDI Seguros' => [
                        'Seguro de Autos' => 'extraerDatosHdiAutos',
                        'Seguro de Daños' => 'extraerDatosHdiDanios',
                        'Seguro de Gastos Medicos' => 'extraerDatosHdiGastos',
                    ],
                    'Banorte Seguros' => 'extraerDatosBanorte',
                    
                ];
    
                $metodo = isset($metodos[$compania->nombre])
                    ? (is_array($metodos[$compania->nombre])
                        ? $metodos[$compania->nombre][$tipoSeguro->nombre] ?? null
                        : $metodos[$compania->nombre])
                    : null;
    
                if ($metodo && method_exists($this, $metodo)) {
                    $datos = $this->$metodo($allText);
                } else {
                    return redirect()->back()->with('error', 'Tipo de seguro o compañía no identificados.');
                }
    
                // Procesar cliente
                $cliente = Cliente::firstOrCreate(
                    ['rfc' => $datos['rfc']],
                    ['nombre_completo' => $datos['nombre_cliente']]
                );
    
                // Extraer vigencia
                if (preg_match('/Vigencia:\s*Desde.*?(\d{2}\/\d{2}\/\d{4}).*?Hasta.*?(\d{2}\/\d{2}\/\d{4})/', $allText, $matches)) {
                    $datos['vigencia_inicio'] = $this->convertirFecha($matches[1]);
                    $datos['vigencia_fin'] = $this->convertirFecha($matches[2]);
                } else {
                    \Log::warning('No se encontró la vigencia en el PDF. Archivo: ' . $pdfPath);
                }
    
                // Procesar agente
                $agente = Agente::firstOrCreate(
                    ['numero_agentes' => $datos['numero_agente']],
                    ['nombre_agentes' => $datos['nombre_agente']]
                );
    
                // Guardar póliza
                Poliza::create([
                    'cliente_id' => $cliente->id,
                    'compania_id' => $compania_id,
                    'agente_id' => $agente->id,
                    'tipo_seguro_id' => $tipoSeguro->id,
                    'numero_poliza' => $datos['numero_poliza'] ?? 'No disponible',
                    'vigencia_inicio' => $datos['vigencia_inicio'] ?? null,
                    'vigencia_fin' => $datos['vigencia_fin'] ?? null,
                    'forma_pago' => $datos['forma_pago'] ?? 'No especificada',
                    'total_a_pagar' => isset($datos['total_pagar']) ? floatval(str_replace(',', '', $datos['total_pagar'])) : 0,
                    'archivo_pdf' => $pdfPath,
                    'pagos_capturados' => false,
                ]);
    
            } catch (\Exception $e) {
                \Log::error('Error al procesar el archivo PDF: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                return redirect()->back()->with('error', 'Error al procesar el archivo PDF: ' . $file->getClientOriginalName());
            }
        }
    
        return redirect()->back()->with('success', 'Las pólizas han sido subidas y procesadas exitosamente.');
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
