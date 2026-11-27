<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 120);
            $table->string('email', 180);
            $table->string('phone', 40)->nullable();
            $table->text('message');
            $table->string('status', 12)->default('unread');
            $table->timestamp('archived_at')->nullable();
            $table->string('delivery_status', 12)->default('pending');
            $table->unsignedInteger('delivery_attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['delivery_status', 'next_attempt_at']);
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
