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
        Schema::create('numeros_polizas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_polizas', 20);
            $table->foreignId('compania_id')->constrained('companias')->onDelete('cascade');
            $table->foreignId('ramo_id')->constrained('ramos')->onDelete('cascade');
            $table->foreignId('seguro_id')->constrained('seguros')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numeros_polizas');
    }
};
