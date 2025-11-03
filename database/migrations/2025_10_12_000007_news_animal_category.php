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
        Schema::create('newsAnimalCategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoryAnimalId')->constrained('animalCategories')->onDelete('cascade');
            $table->foreignId('newId')->constrained('news')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsAnimalCategories');
    }
};
