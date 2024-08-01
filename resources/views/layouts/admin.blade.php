@php

    $setting = App\Models\Utility::settings();
    $superadminsettings = App\Models\Utility::superAdminsettings();
    $logos = \App\Models\Utility::get_file('uploads/logo/');
    $getSetting = \App\Models\Utility::getSeoSetting();

    $currantLang = \Auth::user()->lang;

    // $color = 'theme-3';
    // if (!empty($setting['color'])) {
    //     $color = $setting['color'];
    // }
    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }



    $cust_theme_bg = $setting['cust_theme_bg'];
    $cust_darklayout = $setting['cust_darklayout'];
    $display_landing = $setting['display_landing'];

    $EmailTemplates = App\Models\EmailTemplate::getemailtemplate();
    $SITE_RTL = !empty($setting['SITE_RTL']) ? $setting['SITE_RTL'] : 'off';

@endphp



<!DOCTYPE html>
<html lang="{{ Auth::user()->lang }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
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
        @yield('page-title') - {{ config('app.name', 'TicketGo SaaS') }}
    </title>


    {{-- @if (\Auth::user()->type == 'Super Admin')
        <link rel="shortcut icon" href="{{ $logos . 'favicon.png' }}">
    @else
        <link rel="shortcut icon" href="{{ $logos . $setting['company_favicon'] }}">
    @endif --}}

    @if (\Auth::user()->type == 'Super Admin')
    <link rel="shortcut icon" href="{{ $logos . 'favicon.png' }}?timestamp={{ time() }}">
    @else
        <link rel="shortcut icon" href="{{ $logos . $setting['company_favicon'] }}?timestamp={{ time() }}">
    @endif



    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">
    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('public/custom/libs/select2/dist/css/select2.min.css') }}">
    <style>
            :root {
                --color-customColor: <?= $color ?>;
            }
    </style>
    <!-- vendor css -->
    @if ($SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    @endif
    @if ($cust_darklayout == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
        <style>
            :root {
                --color-customColor: <?= $color ?>;
            }
        </style>
        <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif



    <style>
        [dir="rtl"] .dash-sidebar {
            left: auto !important;
        }

        [dir="rtl"] .dash-header {
            left: 0;
            right: 280px;
        }

        [dir="rtl"] .dash-header:not(.transprent-bg) .header-wrapper {
            padding: 0 0 0 30px;
        }

        [dir="rtl"] .dash-header:not(.transprent-bg):not(.dash-mob-header)~.dash-container {
            margin-left: 0px !important;
        }

        [dir="rtl"] .me-auto.dash-mob-drp {
            margin-right: 10px !important;
        }

        [dir="rtl"] .me-auto {
            margin-left: 10px !important;
        }
    </style>
    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">

    <!-- switch button -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">

    @stack('css-page')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @if ($setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('css/custom-dark.css') }}">
    @endif
</head>

<body class="{{ $themeColor }}">

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    @include('admin.partials.sidebar')


    @include('admin.partials.topnav')

    <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commonModal"></h5>
                    <a type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></a>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commonModal"></h5>
                    <a type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></a>
                </div>
                <div class="modal-body1">

                </div>
            </div>
        </div>
    </div>

    <div class="dash-container">
        <div class="dash-content">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="row page-header-title">
                            <div class="col-md-6">
                                @if (trim($__env->yieldContent('page-title')))
                                    <h4 class="m-0">@yield('page-title')</h4>
                                @endif
                                <ul class="breadcrumb">
                                    @yield('breadcrumb')
                                </ul>
                            </div>
                            <div class="col-md-6 text-right">
                                @if (trim($__env->yieldContent('action-button')))
                                    <div class=""
                                        @if ($SITE_RTL == 'on') style=" float: left !important;" @endif>
                                        <div class="all-button-box float-end mb-3" style="margin-right: -20px;">
                                            @yield('action-button')
                                        </div>
                                    </div>
                                @elseif(trim($__env->yieldContent('multiple-action-button')))
                                    <div class=""
                                        @if ($SITE_RTL == 'on') style=" float: left !important;" @endif>
                                        <div style="margin-right: -20px;">
                                            @yield('multiple-action-button')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')

        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="liveToast" class="toast text-white fade" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
    @include('admin.partials.footer')

    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/dash.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/custom/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="https://js.pusher.com/5.0/pusher.min.js"></script>
    <script src="{{ asset('public/custom/libs/select2/dist/js/select2.full.min.js') }}"></script>



    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/fire.modal.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>

    <script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>

    <script>
        if ($('#pc-dt-simple').length) {
            const dataTable = new simpleDatatables.DataTable("#pc-dt-simple");
        }
    </script>

    <script src="{{ asset('js/custom.js') }}"></script>

    <!-- switch button -->
    <script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>

    <script type="text/javascript">
        $(document).on("click", ".show_confirm", function() {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();
            swal({
                    title: `Are you sure?`,
                    text: "This action can not be undone. Do you want to continue?",
                    icon: "warning",
                    buttons: ["No", "Yes"],
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
        });
    </script>

    <script>
        var date_picker_locale = {
            format: 'YYYY-MM-DD',
            daysOfWeek: [
                "{{ __('Sun') }}",
                "{{ __('Mon') }}",
                "{{ __('Tue') }}",
                "{{ __('Wed') }}",
                "{{ __('Thu') }}",
                "{{ __('Fri') }}",
                "{{ __('Sat') }}"
            ],
            monthNames: [
                "{{ __('January') }}",
                "{{ __('February') }}",
                "{{ __('March') }}",
                "{{ __('April') }}",
                "{{ __('May') }}",
                "{{ __('June') }}",
                "{{ __('July') }}",
                "{{ __('August') }}",
                "{{ __('September') }}",
                "{{ __('October') }}",
                "{{ __('November') }}",
                "{{ __('December') }}"
            ],
        };
        var calender_header = {
            today: "{{ __('today') }}",
            month: '{{ __('month') }}',
            week: '{{ __('week') }}',
            day: '{{ __('day') }}',
            list: '{{ __('list') }}'
        };
    </script>

    <script>
        var dataTableLang = {
            paginate: {
                previous: "<i class='fas fa-angle-left'>",
                next: "<i class='fas fa-angle-right'>"
            },
            lengthMenu: "{{ __('Show') }} _MENU_ {{ __('entries') }}",
            zeroRecords: "{{ __('No data available in table.') }}",
            info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_ {{ __('entries') }}",
            infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
            infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
            search: "{{ __('Search:') }}",
            thousands: ",",
            loadingRecords: "{{ __('Loading...') }}",
            processing: "{{ __('Processing...') }}"
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


        var custdarklayout = document.querySelector("#cust-darklayout");
        custdarklayout.addEventListener("click", function() {
            if (custdarklayout.checked) {

                document
                    .querySelector("#main-style-link")
                    .setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
                document
                    .querySelector(".m-header > .b-brand > .logo-lg")
                    .setAttribute("src", "{{ asset('/storage/uploads/logo/logo-light.png') }}");
                document.body.style.background = 'linear-gradient(141.55deg, #22242C 3.46%, #22242C 99.86%)';
            } else {

                document.body.style.setProperty('background', 'linear-gradient(141.55deg, rgba(240, 244, 243, 0) 3.46%, #f0f4f3 99.86%)', 'important');

                document
                    .querySelector("#main-style-link")
                    .setAttribute("href", "{{ asset('assets/css/style.css') }}");
                document
                    .querySelector(".m-header > .b-brand > .logo-lg")
                    .setAttribute("src", "{{ asset('/storage/uploads/logo/logo-dark.png') }}");
            }
        });


        // var custthemebg = document.querySelector("#cust-theme-bg");
        // custthemebg.addEventListener("click", function() {
        //     if (custthemebg.checked) {
        //         document.querySelector(".dash-sidebar").classList.add("transprent-bg");
        //         document
        //             .querySelector(".dash-header:not(.dash-mob-header)")
        //             .classList.add("transprent-bg");
        //     } else {
        //         document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
        //         document
        //             .querySelector(".dash-header:not(.dash-mob-header)")
        //             .classList.remove("transprent-bg");
        //     }
        // });
    </script>

    @stack('scripts')
    @if (Session::has('success'))
        <script>
            show_toastr('{{ __('Success') }}', '{!! session('success') !!}', 'success');
        </script>
    @endif
    @if (Session::has('error'))
        <script>
            show_toastr('{{ __('Error') }}', '{!! session('error') !!}', 'error');
        </script>
    @endif
</body>
@include('layouts.cookie_consent')

</html>
