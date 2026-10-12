<nav class="navbar navbar-dark navbar-theme-primary px-4 col-12 d-lg-none">
    <a class="navbar-brand me-lg-5" href="">
        <img class="navbar-brand-dark" src="{{ asset($shop?->logo) }}" alt="Volt logo">
        <img class="navbar-brand-light" src="{{ asset($shop?->logo) }}" alt="Volt logo">
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
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center"><span class="sidebar-icon">
                    <img class="border border-white border-2" style="width:2rem; border-radius:2px;" src="{{ asset($shop?->logo) }}" alt="logo"></span>
                    <span class="mt-1 ms-1 sidebar-text" style="font-size: 0.8rem;"> {{ $shop?->name }} </span>
                </a>
            </li>
            <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
            
            {{-- Dashboard --}}
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-gauge"></i>
                    </span>
                    <span>{{ __('Dashboard') }}</span>
                </a>
            </li>

            {{-- Cash Register --}}
            <li class="nav-item {{ request()->routeIs('cashRegister') ? 'active' : '' }}">
                <a href="{{ route('cashRegister') }}" class="nav-link {{ request()->routeIs('cashRegister') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-cash-register"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Cash Register') }}</span>
                </a>
            </li>

            {{-- Money Movements --}}
            <li class="nav-item {{ request()->routeIs('moneyMovements') ? 'active' : '' }}">
                <a href="{{ route('moneyMovements') }}" class="nav-link {{ request()->routeIs('moneyMovements') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Money In / Out') }}</span>
                </a>
            </li>

            {{-- Stock App --}}
            @php $isStockActive = request()->routeIs('stocks.*'); @endphp
            <li class="nav-item">
                <span class="nav-link {{ $isStockActive ? '' : 'collapsed' }} d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#stock-app" aria-expanded="{{ $isStockActive ? 'true' : 'false' }}">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                        <span class="sidebar-text">{{ __('Stock') }}</span>
                    </span>
                    <span class="link-arrow">
                        <span class="icon icon-sm">
                            <i class="fa-solid fa-angle-right"></i>
                        </span>
                    </span>
                </span>
                <div class="multi-level collapse {{ $isStockActive ? 'show' : '' }}" role="list" id="stock-app" aria-expanded="{{ $isStockActive ? 'true' : 'false' }}">
                    <ul class="flex-column nav">
                        <li class="nav-item {{ request()->routeIs('stocks.add') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('stocks.add') ? 'active text-white fw-bold' : '' }}" href="{{ route('stocks.add') }}">
                                <span class="sidebar-text">{{ __('New Product') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('stocks.addToStock') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('stocks.addToStock') ? 'active text-white fw-bold' : '' }}" href="{{ route('stocks.addToStock') }}">
                                <span class="sidebar-text">{{ __('Add To Stock') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('stocks.index') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('stocks.index') ? 'active text-white fw-bold' : '' }}" href="{{ route('stocks.index') }}">
                                <span class="sidebar-text">{{ __('Stock List') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Sales App --}}
            @php $isSalesActive = request()->routeIs('sales.*'); @endphp
            <li class="nav-item">
                <span class="nav-link {{ $isSalesActive ? '' : 'collapsed' }} d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#sales-app" aria-expanded="{{ $isSalesActive ? 'true' : 'false' }}">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </span>
                        <span class="sidebar-text">{{ __('Sales') }}</span>
                    </span>
                    <span class="icon icon-sm">
                        <i class="fa-solid fa-angle-right"></i>
                    </span>
                </span>
                <div class="multi-level collapse {{ $isSalesActive ? 'show' : '' }}" role="list" id="sales-app" aria-expanded="{{ $isSalesActive ? 'true' : 'false' }}">
                    <ul class="flex-column nav">
                        <li class="nav-item {{ request()->routeIs('sales.index') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('sales.index') ? 'active text-white fw-bold' : '' }}" href="{{ route('sales.index') }}">
                                <span class="sidebar-text">{{ __('Sales') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Warranty App --}}
            @php $isWarrantyActive = request()->routeIs('warranty*'); @endphp
            <li class="nav-item">
                <span class="nav-link {{ $isWarrantyActive ? '' : 'collapsed' }} d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#warranty-app" aria-expanded="{{ $isWarrantyActive ? 'true' : 'false' }}">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </span>
                        <span class="sidebar-text">{{ __('Warranty') }}</span>
                    </span>
                    <span class="link-arrow">
                        <span class="icon icon-sm">
                            <i class="fa-solid fa-angle-right"></i>
                        </span>
                    </span>
                </span>
                <div class="multi-level collapse {{ $isWarrantyActive ? 'show' : '' }}" role="list" id="warranty-app" aria-expanded="{{ $isWarrantyActive ? 'true' : 'false' }}">
                    <ul class="flex-column nav">
                        <li class="nav-item {{ request()->routeIs('warrantySend') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('warrantySend') ? 'active text-white fw-bold' : '' }}" href="{{ route('warrantySend') }}">
                                <span class="sidebar-text">{{ __('Warranty Send') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('warrantyReceive') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('warrantyReceive') ? 'active text-white fw-bold' : '' }}" href="{{ route('warrantyReceive') }}">
                                <span class="sidebar-text">{{ __('Warranty Receive') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('warrantyCustomerReceive') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('warrantyCustomerReceive') ? 'active text-white fw-bold' : '' }}" href="{{ route('warrantyCustomerReceive') }}">
                                <span class="sidebar-text">{{ __('Customer Receive') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Customers --}}
            <li class="nav-item {{ request()->routeIs('customers') ? 'active' : '' }}">
                <a href="{{ route('customers') }}" class="nav-link {{ request()->routeIs('customers') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-users-rays"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Customers') }}</span>
                </a>
            </li>

            {{-- Add App (Brands, Categories, Warranties, Suppliers) --}}
            @php $isAddActive = request()->routeIs('brands', 'categories', 'warranties', 'suppliers'); @endphp
            <li class="nav-item">
                <span class="nav-link {{ $isAddActive ? '' : 'collapsed' }} d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#add-app" aria-expanded="{{ $isAddActive ? 'true' : 'false' }}">
                    <span>
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-plus"></i>
                        </span>
                        <span class="sidebar-text">{{ __('Add') }}</span>
                    </span>
                    <span class="link-arrow">
                        <span class="icon icon-sm">
                            <i class="fa-solid fa-angle-right"></i>
                        </span>
                    </span>
                </span>
                <div class="multi-level collapse {{ $isAddActive ? 'show' : '' }}" role="list" id="add-app" aria-expanded="{{ $isAddActive ? 'true' : 'false' }}">
                    <ul class="flex-column nav">
                        <li class="nav-item {{ request()->routeIs('brands') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('brands') ? 'active text-white fw-bold' : '' }}" href="{{ route('brands') }}">
                                <span class="sidebar-text">{{ __('Brands') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('categories') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('categories') ? 'active text-white fw-bold' : '' }}" href="{{ route('categories') }}">
                                <span class="sidebar-text">{{ __('Categories') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('warranties') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('warranties') ? 'active text-white fw-bold' : '' }}" href="{{ route('warranties') }}">
                                <span class="sidebar-text">{{ __('Warranties') }}</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('suppliers') ? 'active' : '' }}">
                            <a class="nav-link {{ request()->routeIs('suppliers') ? 'active text-white fw-bold' : '' }}" href="{{ route('suppliers') }}">
                                <span class="sidebar-text">{{ __('Suppliers') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>

            {{-- Users --}}
            <li class="nav-item {{ request()->routeIs('users') ? 'active' : '' }}">
                <a href="{{ route('users') }}" class="nav-link {{ request()->routeIs('users') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-user-plus"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Users') }}</span>
                </a>
            </li>

            {{-- Settings --}}
            <li class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Settings') }}</span>
                </a>
            </li>

            {{-- Printers --}}
            <li class="nav-item {{ request()->routeIs('printers*') ? 'active' : '' }}">
                <a href="{{ route('printers.index') }}" class="nav-link {{ request()->routeIs('printers*') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-print"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Printers') }}</span>
                </a>
            </li>

            {{-- Audit Logs --}}
            <li class="nav-item {{ request()->routeIs('auditLogs') ? 'active' : '' }}">
                <a href="{{ route('auditLogs') }}" class="nav-link {{ request()->routeIs('auditLogs') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Audit Logs') }}</span>
                </a>
            </li>

            {{-- DB Import Export --}}
            <li class="nav-item {{ request()->routeIs('dbImportExport') ? 'active' : '' }}">
                <a href="{{ route('dbImportExport') }}" class="nav-link {{ request()->routeIs('dbImportExport') ? 'active' : '' }} d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-upload"></i>
                    </span>
                    <span class="sidebar-text">{{ __('DBImportExport') }}</span>
                </a>
            </li>

            {{-- Sign Out --}}
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link d-flex align-items-center">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </span>
                    <span class="sidebar-text">{{ __('Sign Out') }}</span>
                </a>
            </li>
        </ul>
    </div>
</nav>