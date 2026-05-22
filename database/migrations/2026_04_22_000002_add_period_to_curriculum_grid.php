<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_grid', function (Blueprint $table) {
            $table->tinyInteger('period')->unsigned()->nullable()->after('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_grid', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};
