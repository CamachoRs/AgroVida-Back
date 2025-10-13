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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nameUser', 50);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('phoneNumber', 10)->unique();
            $table->boolean('status')->default(false);
            $table->enum('role', ['dueño', 'empleado', 'encargado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
