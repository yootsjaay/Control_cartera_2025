<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServicioClaseToCompaniasTable extends Migration
{
    public function up()
    {
        Schema::table('companias', function (Blueprint $table) {
            $table->string('servicio_clase')->nullable()->after('nombre');
        });
    }

    public function down()
    {
        Schema::table('companias', function (Blueprint $table) {
            $table->dropColumn('servicio_clase');
        });
    }
}
