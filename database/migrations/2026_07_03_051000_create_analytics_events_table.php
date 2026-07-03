<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // page_view, api_request, user_action, error
            $table->string('path');
            $table->string('method', 10);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('device_type', 20)->nullable(); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_type');
            $table->index('path');
            $table->index('created_at');
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
