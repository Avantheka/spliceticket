@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('breadcrumb')
@endsection

@section('action-button')
    @if (\Auth::user()->type == 'Admin')
        <a href="#" class="btn btn-sm btn-primary btn-icon cp_link" data-link="{{ url(\Auth::user()->slug . '/tickets') }}"
            data-toggle="tooltip" data-original-title="{{ __('Click To Copy Support Ticket Url') }}"
            title="{{ __('Click To Copy Support Ticket Url') }}" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="ti ti-copy"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                @if (\Auth::user()->type != 'Super Admin')
                    <div class="col-xxl-7">
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body dash_card_height">
                                        <div class="theme-avtar bg-primary">
                                            <i class="fas fa-list-alt"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Total') }}</p>
                                        <h6 class="mb-3">{{ __('Categories') }}</h6>
                                        <h3 class="mb-0">{{ $categories }}</h3>

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body dash_card_height">
                                        <div class="theme-avtar bg-info">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Open') }}</p>
                                        <h6 class="mb-3">{{ __('Tickets') }}</h6>
                                        <h3 class="mb-0">{{ $open_ticket }} </h3>

                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body dash_card_height">
                                        <div class="theme-avtar bg-secondary">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('New') }}</p>
                                        <h6 class="mb-3">{{ __('Tickets') }}</h6>
                                        <h3 class="mb-0">{{ $new_ticket }} </h3>

                                    </div>
                                </div>
                            </div> --}}
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body dash_card_height">
                                        <div class="theme-avtar bg-warning">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Closed') }}</p>
                                        <h6 class="mb-3">{{ __('Tickets') }}</h6>
                                        <h3 class="mb-0">{{ $close_ticket }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="card">
                                    <div class="card-body dash_card_height">
                                        <div class="theme-avtar bg-danger">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Total') }}</p>
                                        <h6 class="mb-3">{{ __('Agents') }}</h6>
                                        <h3 class="mb-0">{{ $agents }}</h3>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xxl-5">
                        <div class="card">

                            <div class="card-header d-flex justify-content-between">
                                <h5 class="">{{ __('Storage Status') }}</h5>
                                <span class="storage">{{ $user->storage_limit }} {{ __('MB') }} /
                                    {{ $plan->storage_limit }} {{ __('MB') }}</span>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <div id="device-chart"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-7">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('This Year Tickets') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id="chartBar"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-5">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Tickets by Category') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id="categoryPie"></div>
                            </div>
                        </div>
                    </div>


                    {{-- <div class="col-xxl-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Tickets by Category') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <div id="categoryPie"></div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div> --}}
                @endif

                @if (\Auth::user()->type == 'Super Admin')
                    <div class="col-xxl-7">
                        <div class="row">
                            <div class="col-lg-4 col-4">
                                <div class="card">
                                    <div class="card-body" style="min-height:215px;">
                                        <div class="theme-avtar bg-primary mb-3">
                                            <i class="ti ti-users"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Paid Users') }} : <span
                                                class="text-dark">{{ number_format($user['total_paid_user']) }}</span></p>
                                        <h6 class="mb-3">{{ __('Total Users') }}</h6>
                                        <h3 class="mb-0">{{ $user['total_user'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-4">
                                <div class="card">
                                    <div class="card-body" style="min-height:215px;">
                                        <div class="theme-avtar bg-info mb-3">
                                            <i class="ti ti-shopping-cart-plus"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Total Order Amount') }} : <span
                                                class="text-dark">{{ number_format($user['total_orders_price']) }}</span>
                                        </p>
                                        <h6 class="mb-2">{{ __('Total Orders') }}</h6>
                                        <h3 class="mb-0">{{ $user['total_orders'] }}</h3>

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-4">
                                <div class="card">
                                    <div class="card-body" style="min-height:215px;">
                                        <div class="theme-avtar bg-danger mb-3">
                                            <i class="ti ti-trophy"></i>
                                        </div>
                                        <p class="text-muted text-sm mt-4 mb-2">{{ __('Total Purchase Plan') }} : <span
                                                class="text-dark">{{ number_format($user['most_purchese_plan']) }}</span>
                                        </p>
                                        <h6 class="mb-3">{{ __('Total Plans') }}</h6>
                                        <h3 class="mb-0">{{ $user['total_plan'] }}</h3>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="col-xxl-5">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h5>{{ __('Recent Order') }}</h5>
                                <h6 class="last-day-text">{{ __('Last 7 Days') }}</h6>
                            </div>
                            <div class="card-body p-1">
                                <div id="chart-sales" height="200" class="p-3"> </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    <script>
        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            show_toastr('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>
    <script>
        (function() {
            var chartBarOptions = {
                series: [{
                    name: '{{ __('Tickets') }}',
                    data: {!! json_encode(array_values($monthData)) !!}
                }, ],

                chart: {
                    height: 150,
                    type: 'area',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode(array_keys($monthData)) !!},
                    title: {
                        text: '{{ __('Months') }}'
                    }
                },
                colors: ['#ffa21d', '#FF3A6E'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                markers: {
                    size: 4,
                    colors: ['#ffa21d', '#FF3A6E'],
                    opacity: 0.9,
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                },
                yaxis: {
                    title: {
                        text: '{{ __('Tickets') }}'
                    },
                    tickAmount: 3,
                    min: 10,
                    max: 70,
                }
            };
            var arChart = new ApexCharts(document.querySelector("#chartBar"), chartBarOptions);
            arChart.render();
        })();
        (function() {
            var categoryPieOptions = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {!! json_encode($chartData['value']) !!},
                colors: {!! json_encode($chartData['color']) !!},
                labels: {!! json_encode($chartData['name']) !!},
                legend: {
                    show: true
                }
            };
            var categoryPieChart = new ApexCharts(document.querySelector("#categoryPie"), categoryPieOptions);
            categoryPieChart.render();
        })();



        (function() {
            var chartBarOptions = {
                series: [{
                    name: 'Order',
                    data: {!! json_encode($chartDatas['data']) !!},
                }, ],
                chart: {
                    height: 250,
                    type: 'area',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: ["15-Jun", "16-Jun", "17-Jun", "18-Jun", "19-Jun", "20-Jun", "21-Jun"],
                    title: {
                        text: ''
                    }
                },
                colors: ['#1260CC'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    title: {
                        text: ''
                    },

                }
            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
            arChart.render();
        })();



        (function() {
            var options = {
                series: [{{ $storage_limit }}],
                chart: {
                    height: 350,
                    type: 'radialBar',
                    offsetY: -20,
                    sparkline: {
                        enabled: true
                    }
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        track: {
                            background: "#e7e7e7",
                            strokeWidth: '97%',
                            margin: 5, // margin is in pixels
                        },
                        dataLabels: {
                            name: {
                                show: true
                            },
                            value: {
                                offsetY: -50,
                                fontSize: '20px'
                            }
                        }
                    }
                },
                grid: {
                    padding: {
                        top: -10
                    }
                },
                colors: ["#6FD943"],
                labels: ['Used'],
            };
            var chart = new ApexCharts(document.querySelector("#device-chart"), options);
            chart.render();
        })();
    </script>
@endpush
