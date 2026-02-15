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
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->string('description');
            $table->json('highlights');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('basic_projects', function (Blueprint $table) {
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->foreignUlid('project_id')
                ->references('id')->on('projects')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_projects');
        Schema::dropIfExists('projects');
    }
};
