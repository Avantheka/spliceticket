@php
    // $setting = App\Models\Settings::colorset();
    $setting = App\Models\Utility::colorset();
    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }

    $logo = Utility::get_superadmin_logo();
    $logos = \App\Models\Utility::get_file('uploads/logo/');

    $settings = Utility::settings();

    $getSetting = \App\Models\Utility::getSeoSetting();

    $cust_darklayout = $settings['cust_darklayout'];
    $SITE_RTL = !empty($settings['SITE_RTL']) ? $settings['SITE_RTL'] : 'off';

    $lang = app()->getLocale();
    if ($lang == 'ar' || $lang == 'he') {
        $settings['SITE_RTL'] = 'on';
    }

@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $settings['SITE_RTL'] == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,  initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta name="description" content="Dashboard Template Description" />
    <meta name="keywords" content="Dashboard Template" />
    <meta name="author" content="WorkDo" />

    <meta name="title" content="{{ $getSetting['meta_keywords'] }}">
    <meta name="description" content="{{ $getSetting['meta_description'] }}">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ $getSetting['meta_keywords'] }}">
    <meta property="og:description" content="{{ $getSetting['meta_description'] }}">
    <meta property="og:image" content="{{ asset('storage/uploads/metaevent/' . $getSetting['meta_image']) }}">
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ $getSetting['meta_keywords'] }}">
    <meta property="twitter:description" content="{{ $getSetting['meta_description'] }}">
    <meta property="twitter:image" content="{{ asset('storage/uploads/metaevent/' . $getSetting['meta_image']) }}">


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('page-title') - {{ config('app.name', 'Support Ticket') }}
    </title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ $logos . 'favicon.png' }}">
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}" />
    {{-- <link rel="stylesheet" href="{{asset('assets/css/style.css')}}" id="main-style-link"> --}}


    @if ($settings['cust_darklayout'] == 'on')
        @if (isset($settings['SITE_RTL']) && $settings['SITE_RTL'] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
        @endif
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        @if (isset($settings['SITE_RTL']) && $settings['SITE_RTL'] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
        @else
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
        @endif
    @endif


    @if (isset($settings['SITE_RTL']) && $settings['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-rtl.css') }}" id="main-style-link">
    @else
    <link rel="stylesheet" href="{{ asset('assets/css/custom-auth.css') }}" id="main-style-link">
    @endif
    @if ($settings['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/custom-dark.css') }}" id="main-style-link">
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
        <script>  document.addEventListener('DOMContentLoaded', (event) => {
            const recaptcha = document.querySelector('.g-recaptcha');
            recaptcha.setAttribute("data-theme", "dark");
          });
        </script>
    @endif

    @stack('css-page')

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">


    <style type="text/css">
        img.navbar-brand-img {
            width: 245px;
            height: 61px;
        }
    </style>

    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

</head>

<body class="{{ $themeColor }}">

    @yield('content')

    @if ($settings['enable_cookie'] == 'on')
        @include('layouts.cookie_consent')
    @endif

    <!-- Required Js -->
    <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="https://js.pusher.com/5.0/pusher.min.js"></script>

    <script>
        feather.replace();
        var pctoggle = document.querySelector("#pct-toggler");
        if (pctoggle) {
            pctoggle.addEventListener("click", function() {
                if (
                    !document.querySelector(".pct-customizer").classList.contains("active")
                ) {
                    document.querySelector(".pct-customizer").classList.add("active");
                } else {
                    document.querySelector(".pct-customizer").classList.remove("active");
                }
            });
        }
        var themescolors = document.querySelectorAll(".themes-color > a");
        for (var h = 0; h < themescolors.length; h++) {
            var c = themescolors[h];

            c.addEventListener("click", function(event) {
                var targetElement = event.target;
                if (targetElement.tagName == "SPAN") {
                    targetElement = targetElement.parentNode;
                }
                var temp = targetElement.getAttribute("data-value");
                removeClassByPrefix(document.querySelector("body"), "theme-");
                document.querySelector("body").classList.add(temp);
            });
        }

        function removeClassByPrefix(node, prefix) {
            for (let i = 0; i < node.classList.length; i++) {
                let value = node.classList[i];
                if (value.startsWith(prefix)) {
                    node.classList.remove(value);
                }
            }
        }
    </script>
    <script>
        function show_toastr(title, message, type) {

            var f = document.getElementById('liveToast');
            var a = new bootstrap.Toast(f).show();

            if (type == 'success') {
                $('#liveToast').addClass('bg-primary');
            } else {
                $('#liveToast').addClass('bg-danger');
            }
            $('#liveToast .toast-body').html(message);
        }
    </script>


    @stack('scripts')

    @if ($message = Session::get('success'))
        <script>
            show_toastr('Success', '{!! $message !!}', 'success');
        </script>
    @endif
    @if ($message = Session::get('error'))
        <script>
            show_toastr('Error', '{!! $message !!}', 'error');
        </script>
    @endif

</body>
{{-- @include('layouts.cookie_consent') --}}

</html>
