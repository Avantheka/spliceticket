@php
    $setting = App\Models\Utility::settings();
    // $company_logo = \App\Models\Utility::GetLogo();
    $setting = App\Models\Utility::colorset();

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }
    $getSetting = \App\Models\Utility::getSeoSetting();
    $lang = app()->getLocale();
    if ($lang == 'ar' || $lang == 'he') {
        $settings['SITE_RTL'] = 'on';
    }

    // $logo = \App\Models\Utility::get_file('uploads/logo');
    $logo = Utility::get_superadmin_logo();
    $logos = \App\Models\Utility::get_file('uploads/logo/');
    $LangName = \App\Models\Languages::where('code', $lang)->first();
    if (empty($LangName)) {
        $LangName = new App\Models\Utility();
        $LangName->fullName = 'English';
    }
@endphp

<!DOCTYPE html>
<html lang="en" dir="{{ isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on' ? 'rtl' : '' }}">

<head>
    <title>
        {{ \App\Models\Utility::getValByName('header_text') ? \App\Models\Utility::getValByName('header_text') : config('app.name', 'WhatsStore') }}
        - @yield('title')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Dashboard Template Description" />
    <meta name="keywords" content="Dashboard Template" />
    <meta name="author" content="WorkDo" />

    <meta name="title" content="{{$getSetting['meta_keywords']}}">
    <meta name="description" content="{{$getSetting['meta_description']}}">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:title" content="{{$getSetting['meta_keywords']}}">
    <meta property="og:description" content="{{$getSetting['meta_description']}}">
    <meta property="og:image" content="{{asset('storage/uploads/metaevent/'.$getSetting['meta_image'])}}">
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:title" content="{{$getSetting['meta_keywords']}}">
    <meta property="twitter:description" content="{{$getSetting['meta_description']}}">
    <meta property="twitter:image" content="{{asset('storage/uploads/metaevent/'.$getSetting['meta_image'])}}">

    <!-- Favicon icon -->
    <link rel="icon" href="{{ asset(Storage::url('uploads/logo/favicon.png')) }}" type="image/x-icon" />
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/stylesheet.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom-login.css') }}" id="main-style-link">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/libs/animate.css/animate.min.css') }}" id="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('custom/css/custom.css') }}" id="stylesheet">

    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    {{-- @if ($setting['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
    @endif
    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif --}}
    @if ($setting['cust_darklayout'] == 'on')
        @if (isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
        @endif
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        @if (isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">

        @else
            <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
        @endif
    @endif

    @if (isset($setting['SITE_RTL']) && $setting['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-rtl.css') }}" id="main-style-link">
    @else
    <link rel="stylesheet" href="{{ asset('assets/css/custom-auth.css') }}" id="main-style-link">

    @endif
    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/custom-dark.css') }}" id="main-style-link">

    @endif

</head>

<body class="{{ $themeColor }}">
    <!-- [ auth-signup ] start -->

    <div class="custom-login">
        <div class="login-bg-img">
            {{-- <img src="{{ asset('assets/images/auth/' . $color . '.svg') }}" class="login-bg-1"> --}}
          <img src="{{ isset($setting['color_flag']) && $setting['color_flag'] == 'false' ? asset('assets/images/auth/' . $themeColor . '.svg') : asset('assets/images/auth/theme-3.svg') }}" class="login-bg-1">
            <img src="{{ asset('assets/images/user2.svg') }}" class="login-bg-2">
        </div>
        <div class="bg-login bg-primary"></div>
        <div class="custom-login-inner">

            <nav class="navbar navbar-expand-md default">
                <div class="container pe-2">
                    <div class="navbar-brand">
                        <a href="#">
                            <img src="{{ $logos . $logo . '?timestamp=' . time() }}"
                                alt="{{ config('app.name', 'TicketGo Saas') }}" alt="logo" loading="lazy"
                                class="logo" />
                        </a>
                    </div>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarlogin">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarlogin">
                        <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                            @include('landingpage::layouts.buttons')
                            {{-- <div class="lang-dropdown-only-desk">
                                <li class="dropdown dash-h-item drp-language">
                                    <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span class="drp-text"> {{ ucfirst($LangName->fullName) }}
                                        </span>
                                    </a>
                                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                                        @foreach (App\Models\Utility::languages() as $code => $language)
                                            <a href="{{ route('verification.notice', $code) }}" tabindex="0"
                                                class="dropdown-item dropdown-item {{ $LangName->code == $code ? 'active' : '' }}">
                                                <span>{{ ucFirst($language) }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </li>
                            </div> --}}
                            <li class="dropdown dash-h-item drp-language">
                                <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="drp-text"> {{ ucfirst($LangName->fullName) }}
                                    </span>
                                </a>
                                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                                    @foreach (App\Models\Utility::languages() as $code => $language)
                                        <a href="{{ route('verification.notice', $code) }}" tabindex="0"
                                            class="dropdown-item dropdown-item {{ $LangName->code == $code ? 'active' : '' }}">
                                            <span>{{ ucFirst($language) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </nav>

            <main class="custom-wrapper">
                <div class="custom-row">
                        <div class="card">
                            @yield('content')
                        </div>
                </div>
            </main>
            <footer>
                <div class="auth-footer">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <span>&copy; {{ date('Y') }}
                                    {{ App\Models\Utility::getValByName('footer_text') ? App\Models\Utility::getValByName('footer_text') : config('app.name', 'Storego Saas') }}
                                   </span>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- [ auth-signup ] end -->

    <!-- Required Js -->
    <script src="{{ asset('custom/js/jquery-admin.min.js') }}"></script>
    <script src="{{ asset('custom/js/custom-admin.js') }}"></script>

    <script src="{{ asset('assets/js/vendor-all.js') }}"></script>

    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('custom/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <script>
        feather.replace();
    </script>
    <div class="pct-customizer">
        <div class="pct-c-btn">
            <button class="btn btn-primary" id="pct-toggler">
                <i data-feather="settings"></i>
            </button>
        </div>
        <div class="pct-c-content">
            <div class="pct-header bg-primary">
                <h5 class="mb-0 text-white f-w-500">{{ __('Theme Customizer') }}</h5>
            </div>
            <div class="pct-body">
                <h6 class="mt-2">
                    <i data-feather="credit-card" class="me-2"></i>{{ __('Primary color settings') }}
                </h6>
                <hr class="my-2" />
                <div class="theme-color themes-color">
                    <a href="#!" class="" data-value="theme-1"></a>
                    <a href="#!" class="" data-value="theme-2"></a>
                    <a href="#!" class="" data-value="theme-3"></a>
                    <a href="#!" class="" data-value="theme-4"></a>
                </div>
                <h6 class="mt-4">
                    <i data-feather="layout" class="me-2"></i>{{ __('Sidebar settings') }}
                </h6>
                <hr class="my-2" />
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="cust-theme-bg" checked />
                    <label class="form-check-label f-w-600 pl-1"
                        for="cust-theme-bg">{{ __('Transparent layout') }}</label>
                </div>
                <h6 class="mt-4">
                    <i data-feather="sun" class="me-2"></i>{{ __('Layout settings') }}
                </h6>
                <hr class="my-2" />
                <div class="form-check form-switch mt-2">
                    <input type="checkbox" class="form-check-input" id="cust-darklayout" />
                    <label class="form-check-label f-w-600 pl-1"
                        for="cust-darklayout">{{ __('Dark Layout') }}</label>
                </div>
            </div>
        </div>
    </div>
    <input type="checkbox" class="d-none" id="cust-theme-bg"
        {{ Utility::getValByName('cust_theme_bg') == 'on' ? 'checked' : '' }} />
    <input type="checkbox" class="d-none" id="cust-darklayout"
        {{ Utility::getValByName('cust_darklayout') == 'on' ? 'checked' : '' }} />
    <script>
        $(document).ready(function() {
            cust_darklayout();
        });
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

        var custthemebg = document.querySelector("#cust-theme-bg");
        custthemebg.addEventListener("click", function() {
            if (custthemebg.checked) {
                document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.add("transprent-bg");
            } else {
                document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.remove("transprent-bg");
            }
        });

        function cust_darklayout() {
            var custdarklayout = document.querySelector("#cust-darklayout");
            // custdarklayout.addEventListener("click", function() {

            if (custdarklayout.checked) {
                document
                    .querySelector(".m-header > .b-brand > .logo-lg")
                    .setAttribute("src", "{{ $logo . '/' . 'logo-light.png' }}");
                document
                    .querySelector("#main-style-link")
                    .setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
            } else {
                document
                    .querySelector(".m-header > .b-brand > .logo-lg")
                    .setAttribute("src", "{{ $logo . '/' . 'logo-dark.png' }}");
                document
                    .querySelector("#main-style-link")
                    .setAttribute("href", "{{ asset('assets/css/style.css') }}");
            }


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
        var toster_pos = "{{ $setting['SITE_RTL'] == 'on' ? 'left' : 'right' }}";
    </script>
    @stack('script')
    @stack('custom-scripts')
</body>

</html>
