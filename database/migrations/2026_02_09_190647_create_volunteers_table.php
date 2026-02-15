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
        Schema::create('volunteers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('organization');
            $table->string('position');
            $table->string('url')->nullable()->default(null);
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->string('summary');
            $table->json('highlights');
            $table->timestamps();
        });

        Schema::create('basic_volunteers', function (Blueprint $table) {
            $table->foreignUlid('basic_id')
                ->references('id')->on('basics')
                ->cascadeOnDelete();
            $table->foreignUlid('volunteer_id')
                ->references('id')->on('volunteers')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_volunteers');
        Schema::dropIfExists('volunteers');
    }
};
