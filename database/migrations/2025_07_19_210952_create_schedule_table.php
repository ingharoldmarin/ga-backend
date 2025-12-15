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
        Schema::create('schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grade')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subject')->onDelete('cascade');
            $table->foreignId('week_id')->constrained('week')->onDelete('cascade');
            $table->foreignId('topic_id')->constrained('topic')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('component')->onDelete('cascade'); // Added component relation
            $table->foreignId('didactic_unit_id')->constrained('didactic_unit')->onDelete('cascade');
            $table->foreignId('standard_id')->constrained('standard')->onDelete('cascade');
            $table->foreignId('competence_id')->constrained('competence')->onDelete('cascade');
            $table->foreignId('affirmation_id')->constrained('affirmation_dna_dba')->onDelete('cascade');
            $table->foreignId('evidence_id')->constrained('evidence_dna_dba')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->boolean('executed')->default(false); // Indicates if the work was executed
            $table->string('observation')->nullable();   // Additional observation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};
