<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specializations', fn (Blueprint $table) => $table->boolean('homepage_featured')->default(false));
        $selected = DB::table('specializations')->where('active', true)->whereNull('deleted_at')
            ->orderBy('display_order')->orderBy('id')->limit(8)->pluck('id');
        DB::table('specializations')->whereIn('id', $selected)->update(['homepage_featured' => true]);
    }

    public function down(): void
    {
        Schema::table('specializations', fn (Blueprint $table) => $table->dropColumn('homepage_featured'));
    }
};
