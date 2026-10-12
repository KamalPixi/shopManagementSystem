<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Services\PrinterManagerService;

class PrinterSettings extends Component
{
    public string $host_os = '';
    public array $detected_printers = [];

    // POS Printer Properties
    public string $pos_printer_name = '';
    public string $pos_connection_type = 'windows';
    public string $pos_ip_address = '';
    public int $pos_port = 9100;
    public string $pos_paper_width = '80mm';
    public bool $pos_open_drawer = true;
    public bool $pos_cut_paper = true;
    public bool $pos_auto_print_on_sale = false;

    // A4 Printer Properties
    public string $a4_printer_name = '';
    public bool $a4_auto_print = false;

    // Barcode / Label Printer Properties
    public string $barcode_printer_name = '';

    // Status / Testing feedback
    public ?string $test_message = null;
    public ?bool $test_success = null;

    protected function rules(): array
    {
        return [
            'pos_printer_name' => 'nullable|string|max:255',
            'pos_connection_type' => 'required|in:windows,cups,network',
            'pos_ip_address' => 'nullable|string|max:255',
            'pos_port' => 'nullable|integer|min:1|max:65535',
            'pos_paper_width' => 'required|in:80mm,58mm',
            'pos_open_drawer' => 'boolean',
            'pos_cut_paper' => 'boolean',
            'pos_auto_print_on_sale' => 'boolean',
            'a4_printer_name' => 'nullable|string|max:255',
            'a4_auto_print' => 'boolean',
            'barcode_printer_name' => 'nullable|string|max:255',
        ];
    }

    public function mount(PrinterManagerService $service): void
    {
        $this->host_os = PHP_OS_FAMILY === 'Windows' ? 'Windows' : (PHP_OS_FAMILY === 'Darwin' ? 'macOS' : PHP_OS_FAMILY);

        $settings = $service->getSettings();

        // POS
        $this->pos_printer_name = $settings['pos']['printer_name'] ?? '';
        $this->pos_connection_type = $settings['pos']['connection_type'] ?? (PHP_OS_FAMILY === 'Windows' ? 'windows' : 'cups');
        $this->pos_ip_address = $settings['pos']['ip_address'] ?? '';
        $this->pos_port = (int) ($settings['pos']['port'] ?? 9100);
        $this->pos_paper_width = $settings['pos']['paper_width'] ?? '80mm';
        $this->pos_open_drawer = (bool) ($settings['pos']['open_drawer'] ?? true);
        $this->pos_cut_paper = (bool) ($settings['pos']['cut_paper'] ?? true);
        $this->pos_auto_print_on_sale = (bool) ($settings['pos']['auto_print_on_sale'] ?? false);

        // A4
        $this->a4_printer_name = $settings['a4']['printer_name'] ?? '';
        $this->a4_auto_print = (bool) ($settings['a4']['auto_print'] ?? false);

        // Barcode
        $this->barcode_printer_name = $settings['barcode']['printer_name'] ?? '';

        $this->scanPrinters($service);
    }

    public function scanPrinters(PrinterManagerService $service): void
    {
        $this->detected_printers = $service->detectSystemPrinters();
    }

    public function selectPosPrinter(string $name): void
    {
        $this->pos_printer_name = $name;
    }

    public function selectA4Printer(string $name): void
    {
        $this->a4_printer_name = $name;
    }

    public function selectBarcodePrinter(string $name): void
    {
        $this->barcode_printer_name = $name;
    }

    public function save(PrinterManagerService $service): void
    {
        $validated = $this->validate();

        $saved = $service->saveSettings([
            'pos' => [
                'printer_name' => trim($this->pos_printer_name),
                'connection_type' => $this->pos_connection_type,
                'ip_address' => trim($this->pos_ip_address),
                'port' => (int) $this->pos_port,
                'paper_width' => $this->pos_paper_width,
                'open_drawer' => (bool) $this->pos_open_drawer,
                'cut_paper' => (bool) $this->pos_cut_paper,
                'auto_print_on_sale' => (bool) $this->pos_auto_print_on_sale,
            ],
            'a4' => [
                'printer_name' => trim($this->a4_printer_name),
                'auto_print' => (bool) $this->a4_auto_print,
            ],
            'barcode' => [
                'printer_name' => trim($this->barcode_printer_name),
            ],
        ]);

        if ($saved) {
            session()->flash('success', __('Printer configuration updated successfully.'));
        } else {
            session()->flash('error', __('Failed to save printer settings.'));
        }
    }

    public function testPosPrint(PrinterManagerService $service): void
    {
        // First save current values to test the latest settings
        $this->save($service);

        $result = $service->testPosPrint();
        $this->test_success = $result['success'];
        $this->test_message = $result['message'];

        if ($result['success']) {
            session()->flash('test_success', $result['message']);
        } else {
            session()->flash('test_error', $result['message']);
        }
    }

    public function testA4Print(PrinterManagerService $service): void
    {
        $this->save($service);

        $result = $service->testA4Print();
        $this->test_success = $result['success'];
        $this->test_message = $result['message'];

        if (!empty($result['pdf_url'])) {
            $this->dispatch('print-pdf', ['url' => $result['pdf_url']]);
        }

        if ($result['success']) {
            session()->flash('test_success', $result['message']);
        } else {
            session()->flash('test_error', $result['message']);
        }
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('settings:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.settings.printer-settings');
    }
}
