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
        Schema::table('polizas', function (Blueprint $table) {
            $table->foreignId('creado_por')->nullable()->constrained('users')->onDelete('set null')->after('ramo_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropColumn('creado_por');
        });
    }
};
