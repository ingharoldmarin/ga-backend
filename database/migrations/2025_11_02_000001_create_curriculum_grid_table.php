<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PASO 1: Crear tabla principal curriculum_grid
     */
    public function up(): void
    {
        // Crear tabla principal primero
        Schema::create('curriculum_grid', function (Blueprint $table) {
            $table->id();
            
            // Grado y Asignatura (obligatorios)
            $table->foreignId('grade_id')
                ->constrained('grade')
                ->onDelete('cascade');
            
            $table->foreignId('subject_id')
                ->constrained('subject')
                ->onDelete('cascade');
            
            // Campos opcionales (ahora que usamos tablas pivote)
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->index(['grade_id', 'subject_id']);
            $table->index('active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_grid');
    }
};
