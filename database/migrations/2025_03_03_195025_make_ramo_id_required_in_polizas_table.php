<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // En la nueva migración generada

public function up(): void {
    Schema::table('polizas', function (Blueprint $table) {
        $table->foreignId('ramo_id')
            ->nullable(false) // Hacer la columna obligatoria
            ->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            //
        });
    }
};
