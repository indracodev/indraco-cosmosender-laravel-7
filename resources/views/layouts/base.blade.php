<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <title>@yield('title', config('app.name')) — {{ config('app.name') }}</title>
   <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icon-indraco.ico') }}">
   <script>
      (() => {
         const savedTheme = localStorage.getItem('cs_theme');
         const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
         const theme = (savedTheme === 'dark' || savedTheme === 'light') ?
            savedTheme :
            (prefersDark ? 'dark' : 'light');
         document.documentElement.setAttribute('data-bs-theme', theme);
         if (localStorage.getItem('cs_sidebar_mini') === 'true') {
            document.documentElement.setAttribute('data-sidebar-mini', 'true');
         }
      })();
   </script>
   <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css') }}">
   <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons-1.13.1/bootstrap-icons.min.css') }}">
   <link rel="stylesheet" href="{{ asset('assets/css/cosmosender.css') }}">
   @stack('styles')
</head>

<body class="@yield('body_class')">
   @yield('body')

   <script src="{{ asset('assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js') }}"></script>
   <script src="{{ asset('assets/js/cosmosender.js') }}"></script>
   @stack('scripts')
</body>

</html>
