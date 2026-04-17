<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carga_inconsistencia', function (Blueprint $table) {
            $table->string('nombre_rol', 30)->default('Estudiante')->after('id_periodo');
        });
    }

    public function down(): void
    {
        Schema::table('carga_inconsistencia', function (Blueprint $table) {
            $table->dropColumn('nombre_rol');
        });
    }
};
