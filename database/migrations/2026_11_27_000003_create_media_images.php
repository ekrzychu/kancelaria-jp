<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_images', function (Blueprint $table): void {
            $table->char('id', 32)->primary();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('session_version');
            $table->enum('purpose', ['article', 'portrait']);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->timestamps();
            $table->index(['record_id', 'created_at']);
        });
        Schema::table('articles', fn (Blueprint $table) => $table->json('image_blocks')->nullable());
        Schema::table('team_members', function (Blueprint $table): void {
            $table->char('portrait_media_id', 32)->nullable();
            $table->foreign('portrait_media_id')->references('id')->on('media_images');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->dropForeign(['portrait_media_id']);
            $table->dropColumn('portrait_media_id');
        });
        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn('image_blocks'));
        Schema::dropIfExists('media_images');
    }
};
