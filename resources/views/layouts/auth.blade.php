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
      <link type="text/css" href="{{ asset('/vendor/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
      <link type="text/css" href="{{ asset('/vendor/notyf/notyf.min.css') }}" rel="stylesheet">
      <link type="text/css" href="{{ asset('/css/volt.css') }}" rel="stylesheet">
      <link href="{{asset('/assets/fontawesome/css/all.min.css') }}" rel="stylesheet" />
  </head>
  <body>

    <main>
        @yield('content')
    </main>

    <script src="{{ asset('/vendor/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('/vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script><!-- Vendor JS -->
    <script src="{{ asset('/vendor/onscreen/dist/on-screen.umd.min.js') }}"></script><!-- Slider -->
    <script src="{{ asset('/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js') }}"></script><!-- Charts -->
    <script src="{{ asset('/vendor/sweetalert2/dist/sweetalert2.all.min.js') }}"></script><!-- Moment JS -->
    <script src="{{ asset('/assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('/vendor/notyf/notyf.min.js') }}"></script><!-- Simplebar -->
    <script src="{{ asset('/vendor/simplebar/dist/simplebar.min.js') }}"></script><!-- Github buttons -->
    <script src="{{ asset('/assets/js/buttons.js') }}"></script><!-- Volt JS -->
    <script src="{{ asset('/assets/js/volt.js') }}"></script>
  </body>
</html>
