<div>
    {{-- Header & Breadcrumbs --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-3">
        <div class="d-block mb-3 mb-md-0">
            <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
                <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fa-solid fa-house"></i></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('settings') }}">{{ __('Settings') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Printers') }}</li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 fw-bold d-flex align-items-center">
                <i class="fa-solid fa-print text-primary me-2"></i>
                {{ __('Printer Management') }}
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 fs-6">
                    <i class="fa-brands fa-{{ strtolower($host_os) === 'windows' ? 'windows' : (strtolower($host_os) === 'macos' ? 'apple' : 'linux') }} me-1"></i>
                    {{ $host_os }} OS
                </span>
            </h2>
            <p class="small text-muted mb-0">
                {{ __('Configure dedicated printers for POS thermal receipts, A4 invoices, and barcode labels.') }}
            </p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0 d-flex gap-2">
            <button type="button" wire:click="scanPrinters" wire:loading.attr="disabled" class="btn btn-sm btn-outline-secondary shadow-sm">
                <span wire:loading.remove wire:target="scanPrinters"><i class="fa-solid fa-arrows-rotate me-1"></i></span>
                <span wire:loading wire:target="scanPrinters"><i class="fa-solid fa-spinner fa-spin me-1"></i></span>
                {{ __('Scan Printers') }}
            </button>
            <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn btn-sm btn-primary shadow-sm px-3">
                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save Settings') }}
            </button>
        </div>
    </div>

    {{-- Session Flash Alerts --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('test_success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <strong>{{ __('Print Test:') }}</strong> {{ session('test_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('test_error'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>{{ __('Print Test Warning:') }}</strong> {{ session('test_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Detected System Printers Panel --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-list-check text-primary me-2"></i>
                <h6 class="mb-0 fw-bold text-dark">{{ __('Detected System Printers') }}</h6>
                <span class="badge bg-secondary-subtle text-secondary border ms-2">{{ count($detected_printers) }}</span>
            </div>
            <small class="text-muted">
                {{ __('Printers installed on your :os computer', ['os' => $host_os]) }}
            </small>
        </div>
        <div class="card-body p-3">
            @if (count($detected_printers) > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Printer Name') }}</th>
                                <th>{{ __('Port / Connection') }}</th>
                                <th>{{ __('Type Suggestion') }}</th>
                                <th class="text-end">{{ __('Quick Assign') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detected_printers as $printer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fa-solid fa-print text-muted me-2"></i>
                                            <span class="fw-bold font-monospace text-dark">{{ $printer['name'] }}</span>
                                            @if (!empty($printer['is_default']))
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">{{ __('OS Default') }}</span>
                                            @endif
                                            @if ($pos_printer_name === $printer['name'])
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">{{ __('Active POS') }}</span>
                                            @endif
                                            @if ($a4_printer_name === $printer['name'])
                                                <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">{{ __('Active A4') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $printer['port'] ?: '-' }}</span>
                                    </td>
                                    <td>
                                        @if (!empty($printer['is_pos_suggested']))
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                                <i class="fa-solid fa-receipt me-1"></i> {{ __('POS Thermal 80mm') }}
                                            </span>
                                        @elseif (!empty($printer['is_a4_suggested']))
                                            <span class="badge bg-info-subtle text-dark border border-info-subtle">
                                                <i class="fa-solid fa-file-lines me-1"></i> {{ __('A4 Standard') }}
                                            </span>
                                        @else
                                            <span class="small text-muted">{{ __('General') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-xs">
                                            <button type="button" wire:click="selectPosPrinter('{{ addslashes($printer['name']) }}')" class="btn btn-outline-primary btn-xs py-1 px-2" title="{{ __('Assign to POS') }}">
                                                <i class="fa-solid fa-receipt me-1"></i> {{ __('Set as POS') }}
                                            </button>
                                            <button type="button" wire:click="selectA4Printer('{{ addslashes($printer['name']) }}')" class="btn btn-outline-info btn-xs py-1 px-2" title="{{ __('Assign to A4') }}">
                                                <i class="fa-solid fa-file-lines me-1"></i> {{ __('Set as A4') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-3 text-muted">
                    <i class="fa-solid fa-triangle-exclamation text-warning fs-3 mb-2 d-block"></i>
                    <p class="mb-1">{{ __('No system printers detected automatically.') }}</p>
                    <small>{{ __('You can still manually enter printer names or configure network IP printers below.') }}</small>
                </div>
            @endif
        </div>
    </div>

    {{-- Configuration Cards --}}
    <form wire:submit.prevent="save">
        <div class="row g-4">
            {{-- Card 1: POS Receipt Printer (80mm) --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary-subtle text-primary rounded me-2">
                                <i class="fa-solid fa-receipt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ __('POS Receipt Printer (80mm / 58mm)') }}</h6>
                                <small class="text-muted">{{ __('Thermal receipts printed during sales') }}</small>
                            </div>
                        </div>
                        <span class="badge bg-primary text-white">{{ $pos_paper_width }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Select Printer / Queue Name') }}</label>
                            <div class="input-group input-group-sm">
                                <select wire:model.live="pos_printer_name" class="form-select">
                                    <option value="">{{ __('-- Select Detected Printer --') }}</option>
                                    @foreach ($detected_printers as $p)
                                        <option value="{{ $p['name'] }}">
                                            {{ $p['name'] }} {{ !empty($p['is_default']) ? '('.__('Default').')' : '' }} {{ !empty($p['is_pos_suggested']) ? '★ '.__('Thermal') : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted d-block mt-1">{{ __('Or type custom name if shared or not listed above:') }}</small>
                            <input type="text" wire:model="pos_printer_name" class="form-control form-control-sm mt-1 font-monospace" placeholder="{{ __('e.g. XP-80C or POS-80 or EPSON TM-T20') }}">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">{{ __('Connection Type') }}</label>
                                <select wire:model.live="pos_connection_type" class="form-select form-select-sm">
                                    <option value="windows">{{ __('Windows Spooler (USB/Driver)') }}</option>
                                    <option value="cups">{{ __('macOS / CUPS Spooler') }}</option>
                                    <option value="network">{{ __('Network (Raw IP:Port 9100)') }}</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">{{ __('Paper Width') }}</label>
                                <select wire:model="pos_paper_width" class="form-select form-select-sm">
                                    <option value="80mm">{{ __('80mm (Standard 48 columns)') }}</option>
                                    <option value="58mm">{{ __('58mm (Narrow 32 columns)') }}</option>
                                </select>
                            </div>
                        </div>

                        @if ($pos_connection_type === 'network')
                            <div class="p-3 bg-light rounded border mb-3">
                                <h6 class="small fw-bold text-primary mb-2">
                                    <i class="fa-solid fa-network-wired me-1"></i> {{ __('Network Thermal Printer Settings') }}
                                </h6>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <label class="form-label small">{{ __('IP Address') }}</label>
                                        <input type="text" wire:model="pos_ip_address" class="form-control form-control-sm font-monospace" placeholder="192.168.1.200">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small">{{ __('Port') }}</label>
                                        <input type="number" wire:model="pos_port" class="form-control form-control-sm font-monospace" placeholder="9100">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="border rounded p-3 mb-3 bg-white">
                            <h6 class="small fw-bold text-dark mb-2">{{ __('POS Automation Features') }}</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="autoPrintSale" wire:model="pos_auto_print_on_sale">
                                <label class="form-check-label small fw-bold" for="autoPrintSale">
                                    {{ __('Auto-print receipt immediately on sale completion') }}
                                </label>
                                <small class="text-muted d-block">{{ __('When a cashier finishes a sale in POS, the receipt is automatically printed without manual clicks.') }}</small>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="cutPaperSwitch" wire:model="pos_cut_paper">
                                <label class="form-check-label small" for="cutPaperSwitch">
                                    {{ __('Auto cut paper after receipt') }}
                                </label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="drawerKickSwitch" wire:model="pos_open_drawer">
                                <label class="form-check-label small" for="drawerKickSwitch">
                                    {{ __('Kick / Open cash drawer via RJ11 port') }}
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" wire:click="testPosPrint" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary">
                                <span wire:loading.remove wire:target="testPosPrint"><i class="fa-solid fa-play me-1"></i></span>
                                <span wire:loading wire:target="testPosPrint"><i class="fa-solid fa-spinner fa-spin me-1"></i></span>
                                {{ __('Test POS Print (Send Slip)') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: A4 Invoice & Report Printer --}}
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info-subtle text-info rounded me-2">
                                <i class="fa-solid fa-file-invoice fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ __('A4 Invoice & Report Printer') }}</h6>
                                <small class="text-muted">{{ __('Standard office laser/inkjet printer for A4 documents') }}</small>
                            </div>
                        </div>
                        <span class="badge bg-info text-white">{{ __('A4 Size') }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">{{ __('Select A4 Printer') }}</label>
                            <select wire:model.live="a4_printer_name" class="form-select form-select-sm mb-1">
                                <option value="">{{ __('-- OS Default Printer --') }}</option>
                                @foreach ($detected_printers as $p)
                                    <option value="{{ $p['name'] }}">
                                        {{ $p['name'] }} {{ !empty($p['is_default']) ? '('.__('Default').')' : '' }} {{ !empty($p['is_a4_suggested']) ? '★ '.__('Office A4') : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">{{ __('Or specify exact printer name:') }}</small>
                            <input type="text" wire:model="a4_printer_name" class="form-control form-control-sm mt-1 font-monospace" placeholder="{{ __('e.g. HP LaserJet Pro M404 or Canon LBP6030') }}">
                        </div>

                        <div class="border rounded p-3 mb-3 bg-white">
                            <h6 class="small fw-bold text-dark mb-2">{{ __('A4 Printing Automation') }}</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="a4AutoPrintSwitch" wire:model="a4_auto_print">
                                <label class="form-check-label small fw-bold" for="a4AutoPrintSwitch">
                                    {{ __('Direct silent print for A4 invoices') }}
                                </label>
                                <small class="text-muted d-block">{{ __('When clicked, sends the generated A4 invoice straight to the selected printer on Windows/Mac.') }}</small>
                            </div>
                        </div>

                        {{-- Barcode / Label Printer Card (Nested/Secondary) --}}
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-barcode text-primary me-2"></i>
                                <h6 class="small fw-bold text-dark mb-0">{{ __('Barcode / Label Printer (Optional)') }}</h6>
                            </div>
                            <select wire:model="barcode_printer_name" class="form-select form-select-sm mb-1">
                                <option value="">{{ __('-- Default / Not Configured --') }}</option>
                                @foreach ($detected_printers as $p)
                                    <option value="{{ $p['name'] }}">{{ $p['name'] }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="barcode_printer_name" class="form-control form-control-sm font-monospace" placeholder="{{ __('e.g. TSC TE244 or Zebra ZD220') }}">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" wire:click="testA4Print" wire:loading.attr="disabled" class="btn btn-sm btn-outline-info">
                                <span wire:loading.remove wire:target="testA4Print"><i class="fa-solid fa-file-pdf me-1"></i></span>
                                <span wire:loading wire:target="testA4Print"><i class="fa-solid fa-spinner fa-spin me-1"></i></span>
                                {{ __('Test A4 Print (Send Page)') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Action Bar --}}
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i>
                    {{ __('Settings are saved per shop and applied automatically across all POS and Sales receipt actions.') }}
                </div>
                <div class="d-flex gap-2">
                    <button type="button" wire:click="scanPrinters" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> {{ __('Refresh') }}
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary px-4 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save Printer Configuration') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
