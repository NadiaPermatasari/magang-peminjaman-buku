<!--
=========================================================
* Argon Dashboard 2 Tailwind - Laravel 12 starter
=========================================================
* Based on Argon Dashboard Tailwind by Creative Tim
* https://www.creative-tim.com/product/argon-dashboard-tailwind
=========================================================
-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @yield('html-attributes')>
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}" />
    <link rel="icon" type="image/png" href="{{ setting('favicon') ? asset('storage/'.setting('favicon')) : asset('assets/img/favicon.png') }}" />

    @php
      $seoTitle = trim($__env->yieldContent('title')) ?: app_name();
      $seoDescription = trim($__env->yieldContent('meta-description')) ?: (setting('landing_meta_description') ?: (setting('description') ?: $seoTitle));
      $seoImage = trim($__env->yieldContent('meta-image')) ?: (setting('logo') ? asset('storage/'.setting('logo')) : asset('assets/img/apple-icon.png'));
      $seoCanonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    @endphp
    <title>{{ $seoTitle }} | {{ app_name() }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($seoDescription, 160) }}" />
    <meta name="robots" content="@yield('robots', 'index, follow')" />
    <link rel="canonical" href="{{ $seoCanonical }}" />

    {{-- Open Graph / Twitter card — shown when a page (mostly the public
         landing page) is shared on social media or messaging apps. --}}
    <meta property="og:site_name" content="{{ app_name() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="id_ID" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($seoDescription, 200) }}" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:url" content="{{ $seoCanonical }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit($seoDescription, 200) }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />

    @stack('structured-data')

    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Tom Select (select2-style searchable / multi selects, no jQuery) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.min.css" rel="stylesheet" />
    <!-- Popper (tooltips) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <!-- Main Styling: Argon Tailwind theme compiled by Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Super Admin's chosen brand color (Pengaturan > Branding) — every
         bg-blue-500/text-blue-500/border-blue-500/etc. utility resolves
         through this variable (see tailwind.config.js), so changing it
         re-themes the whole app without recompiling CSS. --}}
    <style>:root { --color-primary: {{ \App\Support\Color::hexToRgbTriplet(setting('primary_color')) }}; }</style>
    <script>
      // Restore dark mode before the page paints (avoids a light-mode flash).
      try { if (localStorage.getItem("argon-theme") === "dark") document.documentElement.classList.add("dark"); } catch (e) {}
    </script>
    @stack('styles')
  </head>

  <body class="@yield('body-class', 'm-0 font-sans text-base antialiased font-normal dark:bg-slate-900 leading-default bg-gray-50 text-slate-500')" data-page="@yield('page', 'dashboard')">
    <script>
      // Restore the desktop sidenav state (collapsed / expanded) before it renders.
      try { if (localStorage.getItem("argon-sidenav") === "collapsed") document.body.classList.add("sidenav-collapsed"); } catch (e) {}
    </script>

    @yield('body')

    <!-- plugin for charts  -->
    <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}" async></script>
    <!-- plugin for scrollbar  -->
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}" async></script>
    <!-- Tom Select -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/js/tom-select.complete.min.js"></script>
    <!-- main script file  -->
    <script>
      window.ARGON_ASSETS = "{{ asset('assets') }}/";
    </script>
    <script src="{{ asset('assets/js/argon-dashboard-tailwind.js') }}" async></script>
    <!-- form helpers: Tom Select init, file previews, drag & drop uploads, password toggles -->
    <script src="{{ asset('assets/js/forms.js') }}" defer></script>
    @stack('scripts')
  </body>
</html>
