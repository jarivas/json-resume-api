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
        Schema::dropIfExists('educations');
    }
};
