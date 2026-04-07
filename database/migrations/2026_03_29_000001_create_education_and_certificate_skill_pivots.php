<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_skill', function (Blueprint $table) {
            $table->string('education_id');
            $table->string('skill_id');
            $table->primary(['education_id', 'skill_id']);
            $table->timestamps();
        });

        Schema::create('certificate_skill', function (Blueprint $table) {
            $table->string('certificate_id');
            $table->string('skill_id');
            $table->primary(['certificate_id', 'skill_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_skill');
        Schema::dropIfExists('education_skill');
    }
};
