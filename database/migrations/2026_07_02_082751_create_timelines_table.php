<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('Star');
            $table->enum('type', ['journey', 'process'])->default('journey');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timelines');
    }
};
