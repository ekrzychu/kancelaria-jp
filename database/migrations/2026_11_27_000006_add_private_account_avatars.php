<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_images', fn (Blueprint $table) => $table->enum('purpose', ['article', 'portrait', 'avatar'])->change());
        Schema::table('users', function (Blueprint $table): void {
            $table->char('avatar_media_id', 32)->nullable();
            $table->foreign('avatar_media_id')->references('id')->on('media_images');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['avatar_media_id']);
            $table->dropColumn('avatar_media_id');
        });
        DB::table('media_images')->where('purpose', 'avatar')->delete();
        Schema::table('media_images', fn (Blueprint $table) => $table->enum('purpose', ['article', 'portrait'])->change());
    }
};
