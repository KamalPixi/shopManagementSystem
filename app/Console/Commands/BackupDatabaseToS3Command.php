<?php

namespace App\Console\Commands;

use App\Services\DBImportExportService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabaseToS3Command extends Command
{
    protected $signature = 'backup:database-s3';
    protected $description = 'Create and upload a SQLite database backup to S3';

    public function handle(DBImportExportService $service): int
    {
        if (!config('backup.enabled')) {
            $this->warn('S3 database backups are disabled.');
            return self::SUCCESS;
        }

        try {
            $key = $service->uploadAutomaticBackup();
            $this->info("Database backup uploaded: {$key}");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Database backup failed: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }
}
