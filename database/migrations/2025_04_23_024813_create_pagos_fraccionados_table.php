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
            
            $table->foreignId('numero_poliza_id')->constrained('numeros_polizas');
            
            $table->date('vigencia_inicio');
            $table->date('vigencia_fin');
            $table->float('importe');
            $table->date('fecha_limite');
            
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
