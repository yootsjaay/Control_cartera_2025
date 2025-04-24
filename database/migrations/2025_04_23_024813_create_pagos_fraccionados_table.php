<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos_fraccionados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poliza_id')->constrained('polizas');
            $table->Integer('numero_recibo');
            $table->date('vigencia_inicio');
            $table->date('vigencia_fin');
            $table->float('importe');
            $table->date('fecha_limite_pago');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_fraccionados');
    }
};
