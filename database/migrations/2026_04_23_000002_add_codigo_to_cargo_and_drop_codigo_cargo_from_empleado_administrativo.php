<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->string('codigo', 30)->nullable()->unique('uq_cargo_codigo')->after('id_cargo');
        });

        Schema::table('empleado_administrativo', function (Blueprint $table) {
            $table->dropColumn('codigo_cargo');
        });
    }

    public function down(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->dropUnique('uq_cargo_codigo');
            $table->dropColumn('codigo');
        });

        Schema::table('empleado_administrativo', function (Blueprint $table) {
            $table->string('codigo_cargo', 30)->nullable();
        });
    }
};
