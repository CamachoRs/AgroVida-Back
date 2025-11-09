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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishmentId')->constrained('establishments')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('urgency', ['Alta', 'Media', 'Baja'])->default('Media');
            $table->date('deadline');
            $table->text('description');
            $table->foreignId('userId')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('inventoryId')->constrained('inventories')->nullOnDelete();
            $table->integer('itemQuantity')->nullable();
            $table->text('descriptionR')->nullable();
            $table->string('imageR')->nullable();
            $table->date('resolvedAt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
