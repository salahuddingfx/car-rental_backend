<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('balance');
            $table->string('referral_code', 20)->unique()->nullable()->after('loyalty_points');
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze')->after('referral_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'referral_code', 'tier']);
        });
    }
};
