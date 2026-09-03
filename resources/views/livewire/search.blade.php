<form wire:submit.prevent="searchProduct" class="navbar-search w-100" id="navbar-search-main">
    <div class="input-group input-group-merge search-bar">
      <span class="input-group-text" id="topbar-addon">
        <svg class="icon icon-xs" x-description="Heroicon name: solid/search" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
        </svg> 
      </span>

        <input wire:model.live="search" type="text" class="form-control" id="topbarInputIconLeft" placeholder="{{ __('Search') }}" aria-label="{{ __('Search') }}" aria-describedby="topbar-addon">
        @if (collect($products)->count())
        <div wire:key="customer_div" class="position-absolute rounded w-100 shadow mt-5 border overflow-scroll" style="background-color: rgb(243, 243, 243); max-height: 10rem; z-index: 1;">
            <ul class="list-unstyled m-0 py-2 w-100">
                @foreach ($products as $product)
                    <li wire:key="product_li_{{$product->id}}" class="px-3 w-100">
                        <a wire:click="searchProduct('{{ $product->id }}')" href="#" style="font-size: 0.8rem;" class="text-primary"> <small>SKU: {{ $product->sku }}</small> | <b>{{ $product->name }}</b></a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</form>
