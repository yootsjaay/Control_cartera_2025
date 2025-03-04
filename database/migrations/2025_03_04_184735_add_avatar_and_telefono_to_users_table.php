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
        Schema::table('users', function (Blueprint $table) {
            // Agregar el campo 'avatar' (puede ser NULL)
            $table->string('avatar')->nullable()->after('email');

            // Agregar el campo 'telefono' (puede ser NULL)
            $table->string('telefono')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar los campos en caso de revertir la migración
            $table->dropColumn('avatar');
            $table->dropColumn('telefono');
        });
    }
};
