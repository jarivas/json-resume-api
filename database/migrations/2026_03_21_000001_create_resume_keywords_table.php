<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->unique();
            $table->string('category')->default('resume');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_keywords');
    }
};
