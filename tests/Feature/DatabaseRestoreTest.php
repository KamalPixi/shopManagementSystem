<?php

namespace Tests\Feature;

use App\Services\DBImportExportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\TestCase;

class DatabaseRestoreTest extends TestCase
{
    private string $directory;
    private string $originalStorage;
    private PDO $observer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/shop-restore-'.uniqid();
        mkdir($this->directory, 0750, true);
        $this->originalStorage = $this->app->storagePath();
        $this->app->useStoragePath($this->directory.'/storage');
        config(['database.default' => 'restore_test', 'database.connections.restore_test' => [
            'driver' => 'sqlite', 'database' => $this->directory.'/active.sqlite',
            'foreign_key_constraints' => true,
        ]]);
        Storage::fake('local');
        $this->observer = new PDO('sqlite:'.$this->directory.'/active.sqlite');
        $this->observer->exec("CREATE TABLE items (name TEXT); INSERT INTO items VALUES ('original')");
    }

    protected function tearDown(): void
    {
        DB::purge('restore_test');
        unset($this->observer);
        $this->app->useStoragePath($this->originalStorage);
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_restore_updates_an_open_database_and_preserves_a_pre_restore_backup(): void
    {
        $this->observer->exec('PRAGMA journal_mode=WAL');
        $incoming = new PDO('sqlite:'.$this->directory.'/incoming.sqlite');
        $incoming->exec("CREATE TABLE items (name TEXT); INSERT INTO items VALUES ('restored')");
        $incoming = null;
        Artisan::shouldReceive('call')->once()->with('migrate', ['--force' => true])->andReturn(0);

        app(DBImportExportService::class)->restoreDatabase($this->upload());

        $this->assertSame('restored', $this->observer->query('SELECT name FROM items')->fetchColumn());
        $this->assertSame('restored', DB::table('items')->value('name'));
        $backups = glob($this->directory.'/storage/app/private/backups/pre-restore-*.sqlite');
        $this->assertCount(1, $backups);
        $backup = new PDO('sqlite:'.$backups[0]);
        $this->assertSame('original', $backup->query('SELECT name FROM items')->fetchColumn());
        $this->assertSame([], Storage::disk('local')->allFiles('private/restore'));
    }

    public function test_invalid_backup_leaves_the_active_database_unchanged_and_cleans_up(): void
    {
        file_put_contents($this->directory.'/incoming.sqlite', 'invalid database');
        Artisan::shouldReceive('call')->never();
        try {
            app(DBImportExportService::class)->restoreDatabase($this->upload());
            $this->fail('An invalid database should be rejected.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('original', $this->observer->query('SELECT name FROM items')->fetchColumn());
            $this->assertSame([], Storage::disk('local')->allFiles('private/restore'));
        }
    }

    public function test_a_locked_database_is_preserved_when_restore_fails(): void
    {
        $incoming = new PDO('sqlite:'.$this->directory.'/incoming.sqlite');
        $incoming->exec("CREATE TABLE items (name TEXT); INSERT INTO items VALUES ('restored')");
        $incoming = null;
        $this->observer->beginTransaction();
        $this->observer->query('SELECT name FROM items')->fetchAll();
        Artisan::shouldReceive('call')->never();

        try {
            app(DBImportExportService::class)->restoreDatabase($this->upload());
            $this->fail('A locked database should prevent restore.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('busy', $exception->getMessage());
        } finally {
            $this->observer->rollBack();
        }

        $this->assertSame('original', $this->observer->query('SELECT name FROM items')->fetchColumn());
        $this->assertSame([], Storage::disk('local')->allFiles('private/restore'));
    }

    private function upload(): UploadedFile
    {
        return new UploadedFile($this->directory.'/incoming.sqlite', 'backup.sqlite', null, null, true);
    }
}
