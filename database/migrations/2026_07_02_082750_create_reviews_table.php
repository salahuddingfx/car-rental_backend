<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->integer('rating')->default(5);
            $table->text('text')->nullable();
            $table->enum('source', ['google', 'facebook', 'tripadvisor', 'apexride'])->default('apexride');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
