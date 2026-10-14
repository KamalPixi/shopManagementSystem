<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\DBImportExportService;


class DBImportExport extends Component
{
    use WithFileUploads;

    public $file;

    public function restore() {
        if (!auth()->user()->hasPermissionTo('db-backup:restore')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $this->validate([
            'file' => 'required|file|max:512000',
        ]);

        try {
            app(DBImportExportService::class)->restoreDatabase($this->file);
        } catch (\RuntimeException $exception) {
            report($exception);
            session()->flash('failed', $exception->getMessage());
            return;
        }

        session()->flash('success', __('Database restored successfully.'));
        $this->reset('file');
    }

    public function export() {
        if (!auth()->user()->hasPermissionTo('db-backup:create')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        return app(DBImportExportService::class)->exportDatabase();
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('db-backup:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.db-import-export');
    }
}
