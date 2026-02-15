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
        Schema::create('basics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('label');
            $table->string('email');
            $table->string('phone');
            $table->string('url')->nullable()->default(null);
            $table->text('summary')->nullable()->default(null);
            $table->json('location')->nullable()->default(null);
            $table->json('profiles')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basics');
    }
};
