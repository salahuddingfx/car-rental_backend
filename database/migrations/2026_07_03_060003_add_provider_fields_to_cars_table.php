<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->after('provider_id')->constrained('provider_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            $table->dropForeign(['assigned_driver_id']);
            $table->dropColumn('assigned_driver_id');
        });
    }
};
