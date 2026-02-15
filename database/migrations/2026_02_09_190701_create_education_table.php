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
        Schema::create('education', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('institution');
            $table->string('url');
            $table->string('area');
            $table->string('studyType');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->string('score');
            $table->string('summary');
            $table->json('courses');
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
