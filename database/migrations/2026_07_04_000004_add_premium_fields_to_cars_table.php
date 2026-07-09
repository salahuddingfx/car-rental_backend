<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('year');
            $table->timestamp('premium_expires_at')->nullable()->after('is_premium');
            $table->integer('premium_priority')->default(0)->after('premium_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['is_premium', 'premium_expires_at', 'premium_priority']);
        });
    }
};
