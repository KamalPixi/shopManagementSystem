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
        $this->validate([
            'file' => 'required|file|max:512000',
        ]);

        app(DBImportExportService::class)->restoreDatabase($this->file);

        session()->flash('success', __('Database restored successfully.'));
        $this->reset('file');
    }

    public function export() {
        return app(DBImportExportService::class)->exportDatabase();
    }

    public function render()
    {
        return view('livewire.db-import-export');
    }
}
