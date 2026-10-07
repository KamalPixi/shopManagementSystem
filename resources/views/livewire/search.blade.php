<div class="position-relative w-100 mx-2 mx-md-3">
    <form wire:submit.prevent="searchProduct" class="w-100 m-0" id="navbar-search-main" autocomplete="off">
        <div class="input-group input-group-merge search-bar align-items-center" style="height: 38px;">
            <span class="input-group-text d-flex align-items-center bg-white border-end-0 text-muted" id="topbar-addon" style="height: 38px; cursor: pointer;" wire:click="searchProduct">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input wire:model.live.debounce.250ms="search" 
                   type="text" 
                   class="form-control border-start-0 ps-0" 
                   id="topbarInputIconLeft" 
                   style="height: 38px;" 
                   placeholder="{{ __('Search by product, SKU, serial, or model...') }}" 
                   aria-label="{{ __('Search') }}" 
                   aria-describedby="topbar-addon">
            @if(!empty($search))
                <button type="button" wire:click="clearSearch" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y me-2 p-0 text-decoration-none" style="z-index: 5;" title="{{ __('Clear') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            @endif
        </div>
    </form>

    @if (!empty($search) && collect($products)->count() > 0)
        <div class="position-absolute start-0 w-100 bg-white shadow-lg rounded-3 border overflow-hidden mt-1" 
             style="top: 100%; z-index: 1050; max-height: 360px; overflow-y: auto;">
            <div class="px-3 py-1 bg-light border-bottom d-flex justify-content-between align-items-center text-muted small">
                <span style="font-size: 0.72rem;" class="fw-semibold text-uppercase text-dark">{{ __('Matching Products') }} ({{ count($products) }})</span>
                <span style="font-size: 0.7rem;">{{ __('Press Enter to view') }}</span>
            </div>
            <div class="list-group list-group-flush">
                @foreach ($products as $product)
                    <a href="{{ route('stocks.show', $product->id) }}" 
                       wire:click.prevent="searchProduct('{{ $product->id }}')" 
                       wire:key="search_product_{{ $product->id }}" 
                       class="list-group-item list-group-item-action px-3 py-2 border-bottom text-decoration-none d-flex justify-content-between align-items-center">
                        <div class="me-2 overflow-hidden">
                            <div class="fw-bold text-dark text-truncate mb-0" style="font-size: 0.88rem;">
                                {{ $product->name }}
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-2 text-muted mt-1" style="font-size: 0.75rem;">
                                @if(!empty($product->sku))
                                    <span><i class="fa-solid fa-barcode text-gray-400 me-1"></i><span class="font-monospace text-dark">{{ $product->sku }}</span></span>
                                @endif
                                @if(!empty($product->serial_no))
                                    <span><i class="fa-solid fa-hashtag text-gray-400 me-1"></i>SN: <span class="font-monospace text-dark">{{ $product->serial_no }}</span></span>
                                @endif
                                @if(!empty($product->model_no))
                                    <span><i class="fa-solid fa-tag text-gray-400 me-1"></i>{{ $product->model_no }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end text-nowrap ms-2">
                            <div class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">
                                €{{ number_format((float) $product->price, 2) }}
                            </div>
                            <span class="badge {{ $product->quantity <= ($product->low_stock_threshold ?? 5) ? 'bg-danger text-white' : 'bg-light text-dark border' }} py-0 px-2 fw-semibold" style="font-size: 0.7rem; color: {{ $product->quantity <= ($product->low_stock_threshold ?? 5) ? '#fff' : '#1f2937' }} !important;">
                                {{ $product->quantity }} {{ __('in stock') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @elseif(!empty($search) && strlen(trim($search)) >= 2 && collect($products)->count() === 0)
        <div class="position-absolute start-0 w-100 bg-white shadow-lg rounded-3 border p-3 mt-1 text-center text-muted small" 
             style="top: 100%; z-index: 1050;">
            <i class="fa-solid fa-magnifying-glass me-1 text-gray-400"></i> {{ __('No products found for') }} "<strong>{{ $search }}</strong>"
        </div>
    @endif
</div>

