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
        // Tabla pivote: Malla - Ejes Temáticos
        Schema::create('curriculum_grid_topic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            $table->foreignId('topic_id')
                ->constrained('topic')
                ->onDelete('cascade');
            $table->timestamps();
            
            // Índice único con nombre corto
            $table->unique(['curriculum_grid_id', 'topic_id'], 'cg_topic_unique');
        });

        // Tabla pivote: Malla - Componentes
        Schema::create('curriculum_grid_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            $table->foreignId('component_id')
                ->constrained('component')
                ->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['curriculum_grid_id', 'component_id'], 'cg_component_unique');
        });

        // Tabla pivote: Malla - Estándares
        Schema::create('curriculum_grid_standard', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            $table->foreignId('standard_id')
                ->constrained('standard')
                ->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['curriculum_grid_id', 'standard_id'], 'cg_standard_unique');
        });

        // Tabla pivote: Malla - Competencias
        Schema::create('curriculum_grid_competence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            $table->foreignId('competence_id')
                ->constrained('competence')
                ->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['curriculum_grid_id', 'competence_id'], 'cg_competence_unique');
        });

        // Tabla pivote: Malla - Afirmaciones
        Schema::create('curriculum_grid_affirmation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            $table->foreignId('affirmation_id')
                ->constrained('affirmation_dna_dba')
                ->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['curriculum_grid_id', 'affirmation_id'], 'cg_affirmation_unique');
        });

        // Tabla pivote: Malla - Evidencias
        Schema::create('curriculum_grid_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curriculum_grid_id')
                ->constrained('curriculum_grid')
                ->onDelete('cascade');
            $table->foreignId('evidence_id')
                ->constrained('evidence_dna_dba')
                ->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['curriculum_grid_id', 'evidence_id'], 'cg_evidence_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_grid_evidence');
        Schema::dropIfExists('curriculum_grid_affirmation');
        Schema::dropIfExists('curriculum_grid_competence');
        Schema::dropIfExists('curriculum_grid_standard');
        Schema::dropIfExists('curriculum_grid_component');
        Schema::dropIfExists('curriculum_grid_topic');
    }
};
