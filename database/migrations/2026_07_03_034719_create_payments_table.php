<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('BDT');
            $table->enum('method', ['bkash', 'nagad', 'cod', 'bank_transfer']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded']);
            $table->string('transaction_id')->nullable();
            $table->string('sender_number')->nullable();
            $table->text('admin_note')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
