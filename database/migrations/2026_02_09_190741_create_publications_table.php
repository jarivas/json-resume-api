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
        Schema::create('publications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('publisher');
            $table->dateTime('releaseDate');
            $table->string('url');
            $table->string('summary');
            $table->timestamps();
        });

        Schema::create('basic_publications', function (Blueprint $table) {
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->foreignUlid('publication_id')
                ->references('id')->on('publications')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_publications');
        Schema::dropIfExists('publications');
    }
};
