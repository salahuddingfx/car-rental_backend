<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref')->unique();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('pickup_date');
            $table->date('return_date');
            $table->integer('total_days');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['Upcoming', 'Active', 'Completed', 'Cancelled'])->default('Upcoming');
            $table->json('driver_info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
