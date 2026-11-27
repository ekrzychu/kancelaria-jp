<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', fn (Blueprint $table) => $table->enum('portrait_source', ['none', 'uploaded', 'existing'])->default('none'));
        DB::table('team_members')->whereNotNull('portrait')->where('portrait', '!=', '')->update(['portrait_source' => 'existing']);
        DB::table('team_members')->whereNotNull('portrait_media_id')->update(['portrait_source' => 'uploaded']);
    }

    public function down(): void
    {
        Schema::table('team_members', fn (Blueprint $table) => $table->dropColumn('portrait_source'));
    }
};
