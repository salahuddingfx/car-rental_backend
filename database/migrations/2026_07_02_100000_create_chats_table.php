<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('guest_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_country')->nullable();
            $table->string('guest_address')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->string('sender_type'); // 'guest', 'user', 'admin', 'system'
            $table->foreignId('sender_id')->nullable();
            $table->string('sender_name');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chats');
    }
};
