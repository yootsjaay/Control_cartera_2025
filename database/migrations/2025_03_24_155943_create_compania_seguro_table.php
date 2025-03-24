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
        Schema::create('compania_seguro', function (Blueprint $table) {
            // Solo crear la tabla si existen las tablas referenciadas
            if (Schema::hasTable('companias') && Schema::hasTable('seguros')) {
                $table->foreignId('compania_id')->constrained('companias');
                $table->foreignId('seguro_id')->constrained('seguros');
                $table->primary(['compania_id', 'seguro_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compania_seguro');
    }
};
