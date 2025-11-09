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
        Schema::create('animalTask', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taskId')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('animalId')->constrained('animals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animalTask');
    }
};
