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
        Schema::create('polizas', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('ramo_id')->constrained('ramos')->onDelete('cascade');
            $table->foreignId('seguro_id')->constrained('seguros')->onDelete('cascade');
            $table->foreignId('numero_poliza_id')->constrained('numeros_polizas')->onDelete('cascade');
        
            $table->string('nombre_cliente', 255);
            $table->date('vigencia_inicio');
            $table->date('vigencia_fin');
            $table->enum('forma_pago', ['Contado', 'Fraccionado', 'Otro'])->default('Contado');
            $table->double('prima_total', 10, 2);
            $table->string('ruta_pdf', 512);
            
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polizas');
    }
};
