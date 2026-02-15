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
        Schema::create('skills', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('level');
            $table->json('keywords');
            $table->timestamps();
        });

        Schema::create('basic_skills', function (Blueprint $table) {
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->foreignUlid('skill_id')
                ->references('id')->on('skills')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_skills');
        Schema::dropIfExists('skills');
    }
};
