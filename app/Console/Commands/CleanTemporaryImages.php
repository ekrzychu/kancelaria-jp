<?php

namespace App\Console\Commands;

use App\Media\ImageStorage;
use App\Models\MediaImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CleanTemporaryImages extends Command
{
    protected $signature = 'media:clean-temporary {--apply : Remove expired, unassociated uploads}';

    protected $description = 'Report or remove expired temporary image uploads';

    public function handle(ImageStorage $storage): int
    {
        $cutoff = now()->subHours(config('media.temporary_hours'));
        $pending = MediaImage::whereNull('record_id')->where('created_at', '<=', $cutoff);
        if (! $this->option('apply')) {
            $this->info($pending->count().' expired upload(s). Run with --apply to remove them.');

            return self::SUCCESS;
        }

        $disk = $storage->disk();
        $removed = 0;
        $pending->chunkById(100, function ($batch) use ($cutoff, $disk, &$removed): void {
            foreach ($batch as $candidate) {
                $removed += (int) DB::transaction(function () use ($candidate, $cutoff, $disk): bool {
                    $media = MediaImage::whereNull('record_id')->where('created_at', '<=', $cutoff)
                        ->lockForUpdate()->find($candidate->id);
                    if (! $media) {
                        return false;
                    }

                    $media->delete();
                    DB::afterCommit(fn () => $disk->delete($media->filename()));

                    return true;
                });
            }
        });
        $this->info($removed.' expired upload(s) removed.');

        return self::SUCCESS;
    }
}
