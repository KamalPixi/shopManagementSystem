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
        .input-date {
          height: 1rem;
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
      </style>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <x-sidebar />

    <main class="content flex-grow-1">
        <x-navbar />
        @yield('content')
      </main>
      @include('includes.footer')
    <script src="{{ asset('/vendor/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('/vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/js/volt.js') }}"></script>
  </body>
</html>
