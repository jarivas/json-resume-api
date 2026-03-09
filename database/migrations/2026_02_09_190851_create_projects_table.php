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
            $table->string('url')->nullable()->default(null);

            $table->foreignUlid('basic_id')
                ->nullable()->default(null)
                ->references('id')->on('basics');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
