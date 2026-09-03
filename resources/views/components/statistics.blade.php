<div class="row mt-4">
    <style>
      .s-icon {
        font-size: 2rem;
      }
    </style>
    <div class="col-12 col-sm-6 col-xl-4 mb-4">
      <div class="card border-0 shadow">
          <div class="card-body">
            <div class="row d-block d-xl-flex align-items-center">
                <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                  <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                    <i class="s-icon fa-solid fa-cart-shopping"></i>
                  </div>
                  <div class="d-sm-none">
                      <h2 class="h5">{{ __('Sales') }}</h2>
                      <h3 class="fw-extrabold mb-1">{{ $sales }}</h3>
                  </div>
                </div>
                <div class="col-12 col-xl-7 px-xl-0">
                  <div class="d-none d-sm-block">
                      <h2 class="h6 text-gray-400 mb-0">{{ __('Sales') }}</h2>
                      <h3 class="fw-extrabold mb-2">{{ $sales }}</h3>
                  </div>
                  <small class="d-flex align-items-center text-gray-500">
                      {{ __('All time sales') }}
                  </small>
                  <div class="small d-flex mt-1">
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mb-4">
      <div class="card border-0 shadow">
          <div class="card-body">
            <div class="row d-block d-xl-flex align-items-center">
                <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                  <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                    <i class="s-icon fa-solid fa-box"></i>
                  </div>
                  <div class="d-sm-none">
                      <h2 class="fw-extrabold h5">{{ __('Stocks') }}</h2>
                      <h3 class="mb-1">{{$stocks}}</h3>
                  </div>
                </div>
                <div class="col-12 col-xl-7 px-xl-0">
                  <div class="d-none d-sm-block">
                      <h2 class="h6 text-gray-400 mb-0">{{ __('Stocks') }}</h2>
                      <h3 class="fw-extrabold mb-2">{{$stocks}}</h3>
                  </div>
                  <small class="d-flex align-items-center text-gray-500">
                      {{ __('All Products') }}
                  </small>
                  <div class="small d-flex mt-1">
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mb-4">
      <div class="card border-0 shadow">
          <div class="card-body">
            <div class="row d-block d-xl-flex align-items-center">
                <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                  <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                    <i class="s-icon fa-solid fa-list"></i>
                  </div>
                  <div class="d-sm-none">
                      <h2 class="fw-extrabold h5">{{ __('Categories') }}</h2>
                      <h3 class="mb-1">{{ $categories }}</h3>
                  </div>
                </div>
                <div class="col-12 col-xl-7 px-xl-0">
                  <div class="d-none d-sm-block">
                      <h2 class="h6 text-gray-400 mb-0">{{ __('Categories') }}</h2>
                      <h3 class="fw-extrabold mb-2">{{ $categories }}</h3>
                  </div>
                  <small class="text-gray-500">{{ __('All Categories') }}</small>
                  <div class="small d-flex mt-1">
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4 mb-4">
      <div class="card border-0 shadow">
          <div class="card-body">
            <div class="row d-block d-xl-flex align-items-center">
                <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                  <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                    <i class="s-icon fa-solid fa-users"></i>
                  </div>
                  <div class="d-sm-none">
                      <h2 class="h5">{{ __('Users') }}</h2>
                      <h3 class="fw-extrabold mb-1">{{ $users }}</h3>
                  </div>
                </div>
                <div class="col-12 col-xl-7 px-xl-0">
                  <div class="d-none d-sm-block">
                      <h2 class="h6 text-gray-400 mb-0">{{ __('Users') }}</h2>
                      <h3 class="fw-extrabold mb-2">{{ $users }}</h3>
                  </div>
                  <small class="d-flex align-items-center text-gray-500">
                      {{ __('All users') }}
                  </small>
                  <div class="small d-flex mt-1">
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mb-4">
      <div class="card border-0 shadow">
          <div class="card-body">
            <div class="row d-block d-xl-flex align-items-center">
                <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                  <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                    <i class="s-icon fa-solid fa-building"></i>
                  </div>
                  <div class="d-sm-none">
                      <h2 class="fw-extrabold h5">{{ __('Brands') }}</h2>
                      <h3 class="mb-1">{{$brands}}</h3>
                  </div>
                </div>
                <div class="col-12 col-xl-7 px-xl-0">
                  <div class="d-none d-sm-block">
                      <h2 class="h6 text-gray-400 mb-0">{{ __('Brands') }}</h2>
                      <h3 class="fw-extrabold mb-2">{{$brands}}</h3>
                  </div>
                  <small class="d-flex align-items-center text-gray-500">
                      {{ __('All Brands') }}
                  </small>
                  <div class="small d-flex mt-1">
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mb-4">
      <div class="card border-0 shadow">
          <div class="card-body">
            <div class="row d-block d-xl-flex align-items-center">
                <div class="col-12 col-xl-5 text-xl-center mb-3 mb-xl-0 d-flex align-items-center justify-content-xl-center">
                  <div class="icon-shape icon-shape-primary rounded me-4 me-sm-0">
                    <i class="s-icon fa-solid fa-wrench"></i>
                  </div>
                  <div class="d-sm-none">
                      <h2 class="fw-extrabold h5">{{ __('Warranties Send') }}</h2>
                      <h3 class="mb-1">{{ $warranty_send }}</h3>
                  </div>
                </div>
                <div class="col-12 col-xl-7 px-xl-0">
                  <div class="d-none d-sm-block">
                      <h2 class="h6 text-gray-400 mb-0">{{ __('Warranties Send') }}</h2>
                      <h3 class="fw-extrabold mb-2">{{ $warranty_send }}</h3>
                  </div>
                  <small class="text-gray-500">{{ __('Items send to warranty') }}</small>
                  <div class="small d-flex mt-1">
                  </div>
                </div>
            </div>
          </div>
      </div>
    </div>
</div>
