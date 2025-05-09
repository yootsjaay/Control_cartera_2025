<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Group;
use App\Models\Ramo;
use App\Models\Seguro;
use App\Models\Compania;
use App\Models\NumerosPoliza;
use App\Models\Poliza;
use Carbon\Carbon;

class PolizaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el usuario administrador (asumiendo que ya existe)
        $adminUser = User::where('email', 'cardozob761218@gmail.com')->first();

        // Crear algunos ramos, seguros, compañías y números de póliza de ejemplo
        $ramo = Ramo::firstOrCreate(['nombre' => 'Automóvil']);
        // Asegúrate de que el seguro esté asociado al ramo
        $seguro = Seguro::firstOrCreate(['nombre' => 'Responsabilidad Civil', 'ramo_id' => $ramo->id]);
        $compania = Compania::firstOrCreate(['nombre' => 'HDI Seguros']);
        $numeroPoliza = NumerosPoliza::firstOrCreate(['numero_poliza' => 'AX123456']);

        // Calcular las fechas de vigencia (inicio hoy, fin el próximo mes)
        $vigenciaInicio = Carbon::now()->startOfDay(); // Importante: Establecer la hora a 00:00:00
        $vigenciaFin = Carbon::now()->addMonth()->endOfDay(); // Importante: Establecer la hora a 23:59:59

        // Crear algunas pólizas de ejemplo
        Poliza::create([
            'ramo_id' => $ramo->id,
            'seguro_id' => $seguro->id,
            'numero_poliza_id' => $numeroPoliza->id,
            'compania_id' => $compania->id,
            'user_id' => $adminUser->id,
            'nombre_cliente' => 'Juan Pérez',
            'vigencia_inicio' => $vigenciaInicio,
            'vigencia_fin' => $vigenciaFin,
            'forma_pago' => 'Anual',
            'prima_total' => 1200.00,
            'tipo_prima' => 'Anual',
            'ruta_pdf' => 'ruta/al/archivo.pdf', // Reemplazar con una ruta real
        ]);

        Poliza::create([
            'ramo_id' => $ramo->id,
            'seguro_id' => $seguro->id,
            'numero_poliza_id' => $numeroPoliza->id,
            'compania_id' => $compania->id,
            'user_id' => $adminUser->id,
            'nombre_cliente' => 'María García',
            'vigencia_inicio' => $vigenciaInicio,
            'vigencia_fin' => $vigenciaFin,
            'forma_pago' => 'Fraccionado',
            'prima_total' => 1500.00,
            'primer_pago_fraccionado' => $vigenciaInicio,
            'tipo_prima' => 'Fraccionado',
            'ruta_pdf' => 'ruta/al/archivo2.pdf', // Reemplazar con una ruta real
        ]);

        // Puedes agregar más pólizas aquí si lo deseas
    }
}