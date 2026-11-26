<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 191)->charset('ascii')->collation('ascii_bin')->unique();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('category')->nullable();
            $table->string('author')->nullable();
            $table->longText('body_html');
            $table->date('date')->nullable();
            $table->enum('status', ['draft', 'published', 'unpublished'])->default('draft');
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'deleted_at', 'id']);
        });

        Schema::create('specializations', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 191)->charset('ascii')->collation('ascii_bin')->unique();
            $table->string('title');
            $table->string('category');
            $table->text('summary');
            $table->text('home_summary')->nullable();
            $table->json('body');
            $table->json('scope');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('display_order');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['active', 'deleted_at', 'display_order', 'id']);
        });

        Schema::create('team_members', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->enum('team_group', ['leadership', 'coordinators', 'lawyers', 'support']);
            $table->boolean('homepage_partner')->default(false);
            $table->string('portrait')->nullable();
            $table->unsignedInteger('display_order');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['deleted_at', 'display_order', 'id']);
        });

        Schema::create('job_offers', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->enum('status', ['open', 'closed', 'draft'])->default('draft');
            $table->unsignedInteger('display_order');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'deleted_at', 'display_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('specializations');
        Schema::dropIfExists('articles');
    }
};
