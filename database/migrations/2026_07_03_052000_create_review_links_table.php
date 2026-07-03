<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token', 64)->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('car_id')->references('id')->on('cars')->cascadeOnDelete();
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_links');
    }
};
