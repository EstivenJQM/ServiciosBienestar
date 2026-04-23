<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carga_inconsistencia', function (Blueprint $table) {
            $table->string('codigo_cargo', 30)->default('')->after('dependencia');
            $table->string('nombre_cargo', 150)->default('')->after('codigo_cargo');
        });
    }

    public function down(): void
    {
        Schema::table('carga_inconsistencia', function (Blueprint $table) {
            $table->dropColumn(['codigo_cargo', 'nombre_cargo']);
        });
    }
};
