<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();

            $table->string('document_type');
            $table->string('document_number')->nullable();
            $table->string('document_image');
            $table->date('expires_at')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('document_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_verifications');
    }
};
