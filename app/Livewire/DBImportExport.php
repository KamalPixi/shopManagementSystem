<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\DBImportExportService;


class DBImportExport extends Component
{
    use WithFileUploads;

    public $file;

    public function import() {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:51200', // Max 50MB
        ]);

        $storedPath = $this->file->storeAs('imports', uniqid() . '.csv');
        app(DBImportExportService::class)->importFromCSV($storedPath);

        session()->flash('success', 'Import successful.');
    }

    public function export() {
        return app(DBImportExportService::class)->exportToCSV();
    }

    public function render()
    {
        return view('livewire.db-import-export');
    }
}
