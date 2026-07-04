<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('role', ['driver', 'manager', 'dispatcher'])->default('driver');

            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('license_country')->nullable();
            $table->string('license_image')->nullable();
            $table->boolean('license_verified')->default(false);

            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('suspension_reason')->nullable();

            $table->integer('total_trips')->default(0);
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->boolean('is_available')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['provider_id', 'user_id']);
            $table->index('status');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_members');
    }
};
