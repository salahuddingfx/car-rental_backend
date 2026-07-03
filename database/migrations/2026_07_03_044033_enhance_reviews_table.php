<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete()->after('car_id');
            $table->decimal('car_condition', 3, 1)->nullable()->after('rating');
            $table->decimal('driver_rating', 3, 1)->nullable()->after('car_condition');
            $table->decimal('value_rating', 3, 1)->nullable()->after('driver_rating');
            $table->decimal('cleanliness', 3, 1)->nullable()->after('value_rating');
            $table->json('photos')->nullable()->after('cleanliness');
            $table->text('host_response')->nullable()->after('photos');
            $table->boolean('is_verified')->default(false)->after('host_response');
            $table->integer('helpful_count')->default(0)->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['car_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn([
                'user_id', 'car_id', 'booking_id', 'car_condition', 'driver_rating',
                'value_rating', 'cleanliness', 'photos', 'host_response', 'is_verified', 'helpful_count',
            ]);
        });
    }
};
