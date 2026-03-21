<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_embeddings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('model_type');
            $table->string('model_id');
            $table->text('content')->nullable();
            $table->json('vector')->nullable();
            $table->integer('vector_length')->nullable()->index('vector_length');
            $table->string('embedding_model')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id'], 'resume_embeddings_model_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_embeddings');
    }
};
