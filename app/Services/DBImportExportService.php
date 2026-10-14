<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class DBImportExportService
{
    public function exportDatabase()
    {
        $this->ensureSqlite();
        $backup = storage_path('app/private/backups/shop-' . now()->format('Ymd-His') . '.sqlite');
        $this->createSnapshot($this->databasePath(), $backup);
        return response()->download($backup, basename($backup), ['Content-Type' => 'application/vnd.sqlite3'])->deleteFileAfterSend(true);
    }

    public function uploadAutomaticBackup(): string
    {
        $this->ensureSqlite();
        $disk = config('backup.disk', 's3');
        $prefix = trim(config('backup.prefix', 'shop-management/backups'), '/');
        $filename = 'shop-' . now()->format('Ymd-His') . '.sqlite';
        $local = storage_path('app/private/backups/' . $filename);
        $this->createSnapshot($this->databasePath(), $local);

        $stored = \Storage::disk($disk)->put($prefix . '/' . $filename, fopen($local, 'rb'), ['visibility' => 'private']);
        @unlink($local);
        if (!$stored) throw new RuntimeException(__('The automatic backup could not be uploaded.'));

        $files = \Storage::disk($disk)->files($prefix);
        $backups = collect($files)->filter(fn ($file) => str_ends_with($file, '.sqlite'))->sort()->values();
        $keep = max(1, (int) config('backup.retention', 30));
        $backups->slice(0, max(0, $backups->count() - $keep))->each(fn ($file) => \Storage::disk($disk)->delete($file));

        return $prefix . '/' . $filename;
    }

    public function restoreDatabase(UploadedFile $file): void
    {
        $this->ensureSqlite();
        if (!in_array(strtolower($file->getClientOriginalExtension()), ['sqlite', 'db', 'sqlite3'], true)) {
            throw new RuntimeException(__('Please select a SQLite database backup file.'));
        }
        if (!class_exists(\SQLite3::class)) {
            throw new RuntimeException(__('Database restore requires the SQLite3 PHP extension.'));
        }

        $incoming = $file->storeAs('private/restore', 'incoming-' . uniqid('', true) . '.sqlite', 'local');
        if (!$incoming) {
            throw new RuntimeException(__('Could not store the database backup.'));
        }
        $incomingPath = Storage::disk('local')->path($incoming);

        try {
            $this->assertIntegrity($incomingPath);
            $databasePath = $this->databasePath();
            $this->createSnapshot($databasePath, storage_path('app/private/backups/pre-restore-' . now()->format('Ymd-His-u') . '.sqlite'));
            DB::purge();

            // NativePHP workers may still have the file open on Windows. Restore
            // through SQLite so its locking and journal handling remain intact.
            $this->restoreSnapshot($incomingPath, $databasePath);
            DB::reconnect();
            if (Artisan::call('migrate', ['--force' => true]) !== 0) {
                throw new RuntimeException(__('The database was restored, but its migrations failed.'));
            }
            $this->assertIntegrity($databasePath);
        } finally {
            @unlink($incomingPath);
        }
    }

    private function restoreSnapshot(string $source, string $destination): void
    {
        $incoming = new \SQLite3($source, SQLITE3_OPEN_READONLY);
        $active = null;

        try {
            $active = new \SQLite3($destination, SQLITE3_OPEN_READWRITE);
            $active->busyTimeout(5000);
            if (!@$incoming->backup($active)) {
                throw new RuntimeException(__('Could not restore the database. It may be busy; please wait for other operations to finish and try again.'));
            }
        } finally {
            $active?->close();
            $incoming->close();
        }
    }

    private function createSnapshot(string $source, string $destination): void
    {
        if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0750, true);
        if (file_exists($destination)) unlink($destination);
        $pdo = DB::connection()->getPdo();
        $pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');
        $pdo->exec('VACUUM INTO ' . $pdo->quote($destination));
        $this->assertIntegrity($destination);
    }

    private function assertIntegrity(string $path): void
    {
        $pdo = new \PDO('sqlite:' . $path);
        if ($pdo->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
            throw new RuntimeException(__('The SQLite database failed its integrity check.'));
        }
    }

    private function databasePath(): string
    {
        $path = DB::connection()->getDatabaseName();
        if (!$path || $path === ':memory:' || !is_file($path)) throw new RuntimeException(__('The active SQLite database file could not be found.'));
        return $path;
    }

    private function ensureSqlite(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') throw new RuntimeException(__('Database backup and restore currently require SQLite.'));
    }
}
