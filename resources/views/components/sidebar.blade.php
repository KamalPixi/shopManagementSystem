<nav class="navbar navbar-dark navbar-theme-primary px-4 col-12 d-lg-none">
    <a class="navbar-brand me-lg-5" href="">
        <img class="navbar-brand-dark" src="{{ asset('storage/' . $shop?->logo) }}" alt="Volt logo">
        <img class="navbar-brand-light" src="{{ asset('storage/' . $shop?->logo) }}" alt="Volt logo">
    </a>
    <div class="d-flex align-items-center"><button class="navbar-toggler d-lg-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button></div>
</nav>
<nav id="sidebarMenu" class="sidebar d-lg-block bg-gray-800 text-white collapse" data-simplebar>
    <div class="sidebar-inner px-4 pt-3">
        <div class="user-card d-flex d-md-none align-items-center justify-content-between justify-content-md-center pb-4">
            <div class="d-flex align-items-center">

                <div class="d-block">
                    <h2 class="h5 mb-3">Hi, {{ auth()->user()?->name }}</h2>
                    <a href="{{ route('logout') }}" class="btn btn-secondary btn-sm d-inline-flex align-items-center">
                        <svg class="icon icon-xxs me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </div>
            <div class="collapse-close d-md-none">
                <a href="#sidebarMenu" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="true" aria-label="Toggle navigation">
                    <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>
        <ul class="nav flex-column pt-3 pt-md-0">
            <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center"><span class="sidebar-icon">
                        <img class="border border-white border-2" style="width:2rem; border-radius:2px;" src="{{ asset('storage/' . $shop?->logo) }}" alt="logo">
                    </span><span class="mt-1 ms-1 sidebar-text"> {{ $shop?->name }} </span></a>
            </li>
            <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <span class="sidebar-icon">
                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#stock-app">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-box"></i>
                        </span>
                        <span class="sidebar-text">Stock</span>
                    </span>
                    <span class="link-arrow">
                        <span class="icon icon-sm">
                            <i class="fa-solid fa-angle-right"></i>
                        </span>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="stock-app" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item"><a class="nav-link" href="{{ route('stocks.add') }}"><span class="sidebar-text">New Product</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('stocks.addToStock') }}"><span class="sidebar-text">Add To Stock</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('stocks') }}"><span class="sidebar-text">Stock List</span></a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#sales-app">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </span>
                        <span class="sidebar-text">Sales</span>
                    </span>
                    <span class="icon icon-sm">
                        <i class="fa-solid fa-angle-right"></i>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="sales-app" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item"><a class="nav-link" href="{{ route('sales') }}"><span class="sidebar-text">Sales</span></a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#warranty-app">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-wrench"></i>
                        </span>
                        <span class="sidebar-text">Warranty</span>
                    </span>
                    <span class="link-arrow">
                        <span class="icon icon-sm">
                            <i class="fa-solid fa-angle-right"></i>
                        </span>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="warranty-app" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item"><a class="nav-link" href="{{ route('warrantySend') }}"><span class="sidebar-text">Warranty Send</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('warrantyReceive') }}"><span class="sidebar-text">Warranty Receive</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('warrantyCustomerReceive') }}"><span class="sidebar-text">Customer Receive</span></a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#add-app">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-circle-plus"></i>
                        </span>
                        <span class="sidebar-text">Add</span>
                    </span>
                    <span class="link-arrow">
                        <span class="icon icon-sm">
                            <i class="fa-solid fa-angle-right"></i>
                        </span>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="add-app" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item"><a class="nav-link" href="{{ route('brands') }}"><span class="sidebar-text">Brands</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('categories') }}"><span class="sidebar-text">Categories</span></a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('warranties') }}"><span class="sidebar-text">Warranties</span></a></li>
                    </ul>
                </div>
            </li>
            <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
            <li class="nav-item">
                <a href="{{ route('users') }}" class="nav-link d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <span class="sidebar-text">Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings') }}" class="nav-link d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-gear"></i>
                    </span>
                    <span class="sidebar-text">Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </span>
                    <span class="sidebar-text">Sign Out</span>
                </a>
            </li>
        </ul>
    </div>
</nav>