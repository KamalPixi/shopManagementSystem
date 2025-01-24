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
      </style>
  </head>
  <body>
    <x-sidebar />

    <main class="content">
        <x-navbar />
        @yield('content')
        @include('includes.footer')
    </main>
    <script src="{{ asset('/vendor/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('/vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/js/volt.js') }}"></script>
  </body>
</html>
