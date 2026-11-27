<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_page_views', function (Blueprint $table): void {
            $table->date('day');
            $table->string('page', 210)->charset('ascii')->collation('ascii_bin');
            $table->string('source', 16)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('views');
            $table->primary(['day', 'page', 'source']);
        });
        Schema::table('contact_messages', fn (Blueprint $table) => $table->index('created_at'));
    }

    public function down(): void
    {
        Schema::table('contact_messages', fn (Blueprint $table) => $table->dropIndex(['created_at']));
        Schema::dropIfExists('daily_page_views');
    }
};
