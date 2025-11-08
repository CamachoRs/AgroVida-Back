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
        Schema::create('medicalReviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animalId')->constrained('animals')->onDelete('cascade');
            $table->enum('reviewType', ['Chequeo general', 'Tratamiento', 'Vacunación', 'Examen', 'Emergencia']);
            $table->text('observations')->nullable();
            $table->string('reviewerName', 50);
            $table->string('medicationName', 100)->nullable();
            $table->string('dose', 50)->nullable();
            $table->enum('administrationRoute', ['Oral', 'Inyectable', 'Tópico', 'Intravenoso'])->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicalReviews');
    }
};
