<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->enum('category', ['SUV', 'Sedan', 'Hatchback', 'Van']);
            $table->decimal('price', 10, 2);
            $table->integer('seats')->default(5);
            $table->string('transmission')->default('Automatic');
            $table->string('fuel')->default('Petrol');
            $table->string('power')->nullable();
            $table->string('speed')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->string('location')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_available')->default(true);
            $table->string('year')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
