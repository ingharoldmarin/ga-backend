<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla pivot para asignar mallas curriculares a profesores
     * Esta tabla permite que coordinadores asignen mallas a profesores
     */
    public function up(): void
    {
        Schema::create('curriculum_grid_user', function (Blueprint $table) {
            $table->id();
            
            // Usuario (profesor) al que se asigna la malla
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Malla curricular asignada
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            
            // Coordinador o admin que realizó la asignación
            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            // Colegio donde se asignó (opcional, para filtros)
            $table->foreignId('school_id')
                ->nullable()
                ->constrained('schools')
                ->onDelete('cascade');
            
            // Fecha de asignación
            $table->timestamp('assigned_at')->useCurrent();
            
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index('user_id');
            $table->index('curriculum_grid_id');
            $table->index('school_id');
            $table->index('assigned_by');
            
            // Evitar duplicados: un profesor no puede tener la misma malla asignada dos veces
            // Nombre corto del índice para evitar problemas de longitud en MySQL
            $table->unique(['user_id', 'curriculum_grid_id'], 'unique_user_grid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_grid_user');
    }
};