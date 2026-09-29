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

    public function restoreDatabase(UploadedFile $file): void
    {
        $this->ensureSqlite();
        if (!in_array(strtolower($file->getClientOriginalExtension()), ['sqlite', 'db', 'sqlite3'], true)) {
            throw new RuntimeException(__('Please select a SQLite database backup file.'));
        }
        $incoming = $file->storeAs('private/restore', 'incoming-' . uniqid('', true) . '.sqlite');
        $incomingPath = Storage::path($incoming);
        $this->assertIntegrity($incomingPath);
        $databasePath = $this->databasePath();
        $this->createSnapshot($databasePath, storage_path('app/private/backups/pre-restore-' . now()->format('Ymd-His') . '.sqlite'));
        DB::disconnect();
        if (!@rename($incomingPath, $databasePath)) {
            throw new RuntimeException(__('Could not replace the active database file.'));
        }
        DB::purge();
        DB::reconnect();
        Artisan::call('migrate', ['--force' => true]);
        $this->assertIntegrity($databasePath);
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
