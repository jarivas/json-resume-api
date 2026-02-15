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
        Schema::create('educations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('institution');
            $table->string('url')->nullable()->default(null);
            $table->string('area');
            $table->string('studyType');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->string('score')->nullable()->default(null);
            $table->string('summary');
            $table->json('courses')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create('basic_educations', function (Blueprint $table) {
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->foreignUlid('education_id')
                ->references('id')->on('educations')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_educations');
        Schema::dropIfExists('educations');
    }
};
