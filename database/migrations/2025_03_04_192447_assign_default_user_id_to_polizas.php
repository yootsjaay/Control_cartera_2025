<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar la columna `user_id` (sin restricciones de clave foránea)
        Schema::table('polizas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        // 2. Asignar un valor por defecto a `user_id`
        $defaultUserId = User::first()->id; // Obtener el ID del primer usuario
        DB::table('polizas')->update(['user_id' => $defaultUserId]);

        // 3. Hacer que la columna `user_id` no sea nullable y agregar la clave foránea
        Schema::table('polizas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            // Eliminar la clave foránea y la columna `user_id`
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};