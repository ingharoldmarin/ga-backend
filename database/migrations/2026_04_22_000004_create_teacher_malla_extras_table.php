<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_malla_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedTinyInteger('period')->nullable();
            $table->string('category', 30); // topic, component, standard, competence, affirmation, evidence
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            $table->unique(['teacher_id', 'grade_id', 'subject_id', 'period', 'category', 'item_id'], 'unique_teacher_extra');

            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grade')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subject')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_malla_extras');
    }
};
