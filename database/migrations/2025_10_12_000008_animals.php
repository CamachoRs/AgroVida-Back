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
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishmentId')->constrained('establishments')->onDelete('cascade');
            $table->foreignId('categoryId')->constrained('animalCategories')->onDelete('cascade');
            $table->string('name', 50);
            $table->enum('sex', ['Macho', 'Hembra']);
            $table->enum('healthStatus', ['Sano', 'En tratamiento', 'En observación', 'Crónico', 'Grave'])->default('Sano');
            $table->enum('ageRange', ['Cría', 'Juvenil', 'Adulto', 'Maduro', 'Geriátrico', 'Desconocido'])->default('Juvenil');
            $table->decimal('weight', 5, 2);
            $table->text('observations')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
