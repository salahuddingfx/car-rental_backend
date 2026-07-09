<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['peak_hour', 'weekend', 'holiday', 'seasonal']);
            $table->decimal('multiplier', 5, 3)->default(1.000);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
