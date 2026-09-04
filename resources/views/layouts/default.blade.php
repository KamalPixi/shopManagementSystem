<!--
=========================================================
* Volt Free - Bootstrap 5 Dashboard
=========================================================

* Product Page: https://themesberg.com/product/admin-dashboard/volt-bootstrap-5-dashboard
* Copyright 2021 Themesberg (https://www.themesberg.com)
* License (https://themesberg.com/licensing)

* Designed and coded by https://themesberg.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. Please contact us to request a removal.

-->
<!DOCTYPE html>
<html lang="en">
  <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <title>ShopManagementSystem</title>
      <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
      <link type="text/css" href="{{ asset('/assets/css/volt.css') }}" rel="stylesheet">
      <link href="{{asset('/assets/fontawesome/css/all.min.css') }}" rel="stylesheet" />

      <style>
        /* Global Form Standards */
        .form-control,
        .form-select,
        .form-control-sm,
        .form-select-sm {
          height: 40px !important;
          min-height: 40px !important;
          padding: 0.5rem 0.85rem !important;
          font-size: 0.9rem !important;
          line-height: 1.5 !important;
          border-radius: 0.375rem !important;
          border-color: #d1d5db;
        }

        /* Allow multi-line textareas to size naturally without being forced to 40px */
        textarea.form-control,
        textarea.form-control-sm {
          height: auto !important;
          min-height: 52px !important;
          padding: 0.55rem 0.85rem !important;
        }

        /* Form labels standard typography & spacing */
        form label:not(.form-check-label) {
          display: inline-block;
          font-size: 0.85rem;
          font-weight: 600;
          color: #374151;
          margin-bottom: 0.35rem !important;
        }

        /* Compact form controls inside compact data tables (e.g. POS cart line-items) */
        .table .form-control-sm,
        .table .form-select-sm {
          height: 32px !important;
          min-height: 32px !important;
          padding: 0.25rem 0.5rem !important;
          font-size: 0.82rem !important;
          border-radius: 0.25rem !important;
        }

        /* Ensure date and number inputs align vertically with standard inputs */
        input[type="date"].form-control,
        input[type="date"].form-control-sm {
          line-height: 1.5 !important;
        }

        /* to fix old laptop large display issue */
        html {
          font-size: 14px;
        }

        /* Sidebar active child highlight */
        .sidebar .multi-level .nav-item.active > .nav-link,
        .sidebar .multi-level .nav-link.active {
          color: #ffffff !important;
          background: rgba(255, 255, 255, 0.12);
          border-radius: 0.375rem;
          font-weight: 600;
        }
        .sidebar .nav-item.active > .nav-link:not([data-bs-toggle="collapse"]) {
          background-color: #1f2937;
          color: #ffffff;
        }

        /* Sticky footer styling - full width edge to edge */
        .site-footer {
          position: sticky;
          bottom: 0;
          z-index: 999;
          background: rgba(255, 255, 255, 0.98);
          backdrop-filter: blur(5px);
          border-top: 1px solid #e5e7eb;
          margin-left: -1rem !important;
          margin-right: -1rem !important;
          margin-bottom: 0 !important;
          border-radius: 0 !important;
        }
      </style>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <x-sidebar />

    <main class="content flex-grow-1 d-flex flex-column">
        <x-navbar />
        <div class="flex-grow-1">
            @yield('content')
        </div>
        @include('includes.footer')
    </main>
    <script src="{{ asset('/vendor/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('/vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/js/volt.js') }}"></script>
  </body>
</html>
