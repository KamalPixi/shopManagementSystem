<div>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 py-4">
        <div>
            <h2 class="h4 mb-1">{{ __('Printer Management') }}</h2>
            <p class="text-muted mb-0">{{ __('Choose where receipts, invoices and labels print.') }}</p>
        </div>
        <button type="button" wire:click="scanPrinters" wire:loading.attr="disabled" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrows-rotate me-1"></i> {{ __('Refresh printers') }}
        </button>
    </div>

    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $color)
        @if (session()->has($key))
            <div class="alert alert-{{ $color }}" role="status">{{ session($key) }}</div>
        @endif
    @endforeach
    @if ($test_message)
        <div class="alert alert-{{ $test_success ? 'success' : 'danger' }}" role="status" aria-live="polite">
            {{ $test_message }}
            @if ($test_pdf_url)
                <a href="{{ $test_pdf_url }}" target="_blank" rel="noopener" class="d-block mt-2">{{ __('Open test PDF to print manually') }}</a>
            @endif
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="d-flex align-items-center gap-2 text-muted small mb-3">
        <span class="badge bg-light text-dark border">{{ $host_os }}</span>
        <span>{{ trans_choice(':count installed printer|:count installed printers', count($detected_printers), ['count' => count($detected_printers)]) }}</span>
    </div>
    @if (empty($detected_printers))
        <div class="alert alert-light border small">{{ __('No printers were detected. Enter the exact name shown in your computer’s printer settings, or refresh after installing the printer.') }}</div>
    @endif
    <datalist id="installed-printers">
        @foreach ($detected_printers as $printer)<option value="{{ $printer['name'] }}">{{ !empty($printer['is_default']) ? __('System default') : ($printer['port'] ?? '') }}</option>@endforeach
    </datalist>

    <form wire:submit="save">
        <div class="row g-3">
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-1"><i class="fa-solid fa-receipt text-primary me-2"></i>{{ __('Receipt printer') }}</h3>
                        <p class="small text-muted mb-4">{{ __('Thermal receipts for POS sales. Requires an ESC/POS compatible printer.') }}</p>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="pos-connection" class="form-label">{{ __('Connection') }}</label>
                                <select id="pos-connection" wire:model.live="pos_connection_type" class="form-select">
                                    <option value="{{ $host_os === 'Windows' ? 'windows' : 'cups' }}">{{ __('Installed printer (USB / system queue)') }}</option>
                                    <option value="network">{{ __('Network printer (IP address)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="pos-width" class="form-label">{{ __('Paper width') }}</label>
                                <select id="pos-width" wire:model="pos_paper_width" class="form-select">
                                    <option value="80mm">80 mm</option><option value="58mm">58 mm</option>
                                </select>
                            </div>
                            @if ($pos_connection_type === 'network')
                                <div class="col-md-8">
                                    <label for="pos-ip" class="form-label">{{ __('Printer IP address') }}</label>
                                    <input id="pos-ip" wire:model="pos_ip_address" class="form-control" placeholder="192.168.1.200">
                                </div>
                                <div class="col-md-4">
                                    <label for="pos-port" class="form-label">{{ __('Port') }}</label>
                                    <input id="pos-port" type="number" min="1" max="65535" wire:model="pos_port" class="form-control">
                                </div>
                            @else
                                <div class="col-12">
                                    <label for="pos-printer" class="form-label">{{ __('Printer name') }}</label>
                                    <input id="pos-printer" list="installed-printers" wire:model="pos_printer_name" class="form-control" placeholder="{{ __('Choose or type an installed printer name') }}">
                                    <div class="form-text">{{ __('Use the exact installed name. Printer sharing is not required on Windows.') }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="form-check form-switch mt-4">
                            <input id="pos-auto" type="checkbox" wire:model="pos_auto_print_on_sale" class="form-check-input">
                            <label for="pos-auto" class="form-check-label">{{ __('Print receipt after each sale') }}</label>
                        </div>
                        <details class="mt-3">
                            <summary class="small text-muted">{{ __('Paper cutter and cash drawer') }}</summary>
                            <div class="form-check form-switch mt-3">
                                <input id="pos-cut" type="checkbox" wire:model="pos_cut_paper" class="form-check-input">
                                <label for="pos-cut" class="form-check-label">{{ __('Cut paper after receipt') }}</label>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input id="pos-drawer" type="checkbox" wire:model="pos_open_drawer" class="form-check-input">
                                <label for="pos-drawer" class="form-check-label">{{ __('Open connected cash drawer') }}</label>
                            </div>
                        </details>
                        <div class="border-top mt-4 pt-3">
                            <button type="button" wire:click="testPosPrint" wire:loading.attr="disabled" class="btn btn-outline-primary btn-sm">{{ __('Save & test receipt') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-1"><i class="fa-solid fa-file-invoice text-primary me-2"></i>{{ __('A4 printer') }}</h3>
                        <p class="small text-muted mb-4">{{ __('Invoices and office documents.') }}</p>
                        <label for="a4-printer" class="form-label">{{ __('Printer name') }}</label>
                        <input id="a4-printer" list="installed-printers" wire:model="a4_printer_name" class="form-control" placeholder="{{ __('System default printer') }}">
                        <div class="form-text">{{ __('Leave blank to use your system default.') }}</div>
                        <div class="form-check form-switch mt-3">
                            <input id="a4-auto" type="checkbox" wire:model="a4_auto_print" class="form-check-input">
                            <label for="a4-auto" class="form-check-label">{{ __('Send invoices directly to this printer') }}</label>
                        </div>
                        <div class="border-top mt-4 pt-3">
                            <button type="button" wire:click="testA4Print" wire:loading.attr="disabled" class="btn btn-outline-primary btn-sm">{{ __('Save & test A4 page') }}</button>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h6 mb-3"><i class="fa-solid fa-barcode text-muted me-2"></i>{{ __('Label printer') }}</h3>
                        <label for="barcode-printer" class="form-label small">{{ __('Printer name (optional)') }}</label>
                        <input id="barcode-printer" list="installed-printers" wire:model="barcode_printer_name" class="form-control" placeholder="{{ __('Choose or type a label printer name') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 py-4">
            <span class="small text-muted" wire:loading.remove>{{ __('Test buttons save your settings before sending a job.') }}</span>
            <span class="small text-primary" wire:loading role="status">{{ __('Working… Please wait.') }}</span>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">{{ __('Save Settings') }}</button>
        </div>
    </form>
</div>
