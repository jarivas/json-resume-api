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
        Schema::create('languages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('language');
            $table->string('fluency');
            $table->timestamps();
        });

        Schema::create('basic_languages', function (Blueprint $table) {
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->foreignUlid('language_id')
                ->references('id')->on('languages')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_languages');
        Schema::dropIfExists('languages');
    }
};
