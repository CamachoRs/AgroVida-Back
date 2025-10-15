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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishmentId')->constrained('establishments')->onDelete('cascade');
            $table->foreignId('categoryId')->constrained('categories');
            $table->string('nameItem', 50);
            $table->integer('quantity');
            $table->string('unitMeasurement', 50);
            $table->timestamp('entryDate');
            $table->timestamp('expiryDate')->nullable();
            $table->string('supplierName', 50);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
