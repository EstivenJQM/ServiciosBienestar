<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Primero se eliminan las tablas hijas (tienen FK hacia empleado)
        Schema::dropIfExists('empleado_docente');
        Schema::dropIfExists('empleado_administrativo');
        Schema::dropIfExists('empleado_contratista');

        // Se agregan dependencia y cargo directo en empleado
        Schema::table('empleado', function (Blueprint $table) {
            $table->unsignedSmallInteger('id_dependencia')->nullable()->after('id_tipo_empleado');
            $table->unsignedSmallInteger('id_cargo')->nullable()->after('id_dependencia');

            $table->foreign('id_dependencia', 'fk_empleado_dep')
                  ->references('id_dependencia')->on('dependencia')
                  ->onUpdate('cascade')
                  ->nullOnDelete();

            $table->foreign('id_cargo', 'fk_empleado_cargo')
                  ->references('id_cargo')->on('cargo')
                  ->onUpdate('cascade')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            $table->dropForeign('fk_empleado_dep');
            $table->dropForeign('fk_empleado_cargo');
            $table->dropColumn(['id_dependencia', 'id_cargo']);
        });

        Schema::create('empleado_contratista', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario_rol_sede');
            $table->unsignedSmallInteger('id_dependencia');
            $table->unsignedSmallInteger('id_cargo')->nullable();
            $table->primary('id_usuario_rol_sede');
            $table->foreign('id_usuario_rol_sede', 'fk_cont_empleado')
                  ->references('id_usuario_rol_sede')->on('empleado')->onUpdate('cascade');
            $table->foreign('id_dependencia', 'fk_cont_dep')
                  ->references('id_dependencia')->on('dependencia')->onUpdate('cascade');
            $table->foreign('id_cargo', 'fk_cont_cargo')
                  ->references('id_cargo')->on('cargo')->onUpdate('cascade');
        });

        Schema::create('empleado_administrativo', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario_rol_sede');
            $table->unsignedSmallInteger('id_dependencia');
            $table->unsignedSmallInteger('id_cargo')->nullable();
            $table->primary('id_usuario_rol_sede');
            $table->foreign('id_usuario_rol_sede', 'fk_adm_empleado')
                  ->references('id_usuario_rol_sede')->on('empleado')->onUpdate('cascade');
            $table->foreign('id_dependencia', 'fk_adm_dep')
                  ->references('id_dependencia')->on('dependencia')->onUpdate('cascade');
            $table->foreign('id_cargo', 'fk_adm_cargo')
                  ->references('id_cargo')->on('cargo')->onUpdate('cascade');
        });

        Schema::create('empleado_docente', function (Blueprint $table) {
            $table->unsignedInteger('id_usuario_rol_sede');
            $table->unsignedSmallInteger('id_dependencia')->nullable();
            $table->unsignedSmallInteger('id_cargo')->nullable();
            $table->primary('id_usuario_rol_sede');
            $table->foreign('id_usuario_rol_sede', 'fk_doc_empleado')
                  ->references('id_usuario_rol_sede')->on('empleado')->onUpdate('cascade');
        });
    }
};
