<?php

use App\Models\JobOffer;
use App\Support\PublicUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_offers', function (Blueprint $table): void {
            $table->string('slug', 191)->charset('ascii')->collation('ascii_bin')->nullable()->unique();
            foreach (['location', 'employment_type', 'work_schedule', 'work_mode', 'salary', 'application_email'] as $column) {
                $table->string($column)->nullable();
            }
            $table->date('deadline')->nullable();
            foreach (['body', 'application_instructions', 'application_documents', 'candidate_privacy'] as $column) {
                $table->text($column)->nullable();
            }
            foreach (['responsibilities', 'requirements', 'desirable', 'benefits'] as $column) {
                $table->json($column)->nullable();
            }
        });

        DB::table('job_offers')->select(['id', 'title'])->chunkById(100, function ($offers): void {
            foreach ($offers as $offer) {
                DB::table('job_offers')->where('id', $offer->id)->update([
                    'slug' => PublicUrl::availableSlug($offer->title ?? 'Oferta pracy', JobOffer::class, JobOffer::RESERVED_SLUGS),
                ]);
            }
        });
        Schema::table('job_offers', fn (Blueprint $table) => $table->string('slug', 191)
            ->charset('ascii')->collation('ascii_bin')->change());
    }

    public function down(): void
    {
        Schema::table('job_offers', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug', 'location', 'employment_type', 'work_schedule', 'work_mode', 'salary', 'deadline', 'body',
                'responsibilities', 'requirements', 'desirable', 'benefits', 'application_instructions',
                'application_documents', 'application_email', 'candidate_privacy',
            ]);
        });
    }
};
