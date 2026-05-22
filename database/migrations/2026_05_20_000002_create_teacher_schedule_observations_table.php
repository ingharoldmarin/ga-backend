<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_schedule_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedTinyInteger('period')->nullable();
            $table->string('node_type', 30);
            $table->string('node_id', 50);
            $table->unsignedSmallInteger('week');          // semana planificada
            $table->boolean('executed')->default(false);   // ¿se ejecutó?
            $table->text('observation')->nullable();        // justificación si no se ejecutó
            $table->unsignedSmallInteger('rescheduled_week')->nullable(); // semana a la que se corrió
            $table->timestamps();

            $table->unique(
                ['teacher_id', 'grade_id', 'subject_id', 'period', 'node_type', 'node_id', 'week'],
                'unique_schedule_obs'
            );
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grade')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subject')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_schedule_observations');
    }
};
