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
        Schema::create('works', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('position');
            $table->string('url')->nullable()->default(null);
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->string('summary');
            $table->json('highlights');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
