<?php

namespace App\Console\Commands;

use App\Content\ContentImporter;
use App\Content\ContentValidator;
use App\Content\ReviewedContent;
use Illuminate\Console\Command;
use PDOException;
use RuntimeException;

final class ImportSiteContent extends Command
{
    protected $signature = 'site:import-content {--dry-run : Validate the reviewed JSON and show counts without connecting to MariaDB}';

    protected $description = 'Import the reviewed content into empty site content tables';

    public function handle(ContentImporter $importer): int
    {
        try {
            $source = (new ReviewedContent(config('site.content_path')))->all();
            $errors = (new ContentValidator(public_path()))->validate($source);
            if ($errors !== []) {
                foreach ($errors as $error) {
                    $this->error($error);
                }

                return self::FAILURE;
            }

            $records = $importer->prepare($source);
            if (! $this->option('dry-run')) {
                $importer->import($records);
            }
        } catch (PDOException $exception) {
            report($exception);
            $this->error('MariaDB import failed. Check the application log and the target schema.');

            return self::FAILURE;
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $counts = [];
        foreach ($records as $model => $rows) {
            $counts[] = [(new $model())->getTable(), count($rows)];
        }
        $this->table(['Table', 'Records'], $counts);
        $this->info($this->option('dry-run') ? 'Dry run complete. No database writes.' : 'Content imported.');

        return self::SUCCESS;
    }
}
