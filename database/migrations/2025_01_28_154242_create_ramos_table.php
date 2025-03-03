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
        Schema::create('ramos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_ramo', 255);
            $table->string('slug')->unique(); // Agrega la columna después de nombre_ramo
            $table->foreignId('id_seguros')->constrained('seguros')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ramos');
    }
};
