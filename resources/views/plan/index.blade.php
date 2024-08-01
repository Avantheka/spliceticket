@extends('layouts.admin')
@php
    $dir = asset(Storage::url('uploads/plan'));
    $admin_payment_setting = Utility::payment_settings();
@endphp


@section('page-title')
    {{ __('Plan') }}
@endsection

@section('title')
    {{ __('Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Plan') }}</li>
@endsection

@section('action-button')
    @if (\Auth::user()->type == 'Super Admin')
        @if (count($payment_setting) > 0)
            <div class="action-btn ms-2">
                <a href="#" data-url="{{ route('plan.create') }}" data-size="lg" data-ajax-popup="true"
                    data-bs-toggle="tooltip" data-title="{{ __('Create New Plan') }}" title="{{ __('Create') }}"
                    class="btn btn-sm btn-primary btn-icon m-1">
                    <i class="ti ti-plus"></i>
                </a>
            </div>
        @endif
    @endif
@endsection

@section('content')
    @if (\Auth::user()->type == 'Super Admin')
        <div class="row">
            <div class="col-12">
                @if (count($payment_setting) == 0)
                    <div class="alert alert-warning"><i class="fe fe-info"></i>
                        {{ __('Please set payment api key & secret key for add new plan') }}</div>
                @endif
            </div>
        </div>
    @endif
    <div class="row">
        @foreach ($plans as $plan)
            <div class="col-lg-4 col-xl-3 col-md-6 col-sm-6 mt-3">
                <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s"
                    style="
                min-height: 265px;
               visibility: visible;
               animation-delay: 0.2s;
               animation-name: fadeInUp;
               ">

                    <div class="card-body {{ !empty(\Auth::user()->type != 'Super Admin') ? 'plan-box' : '' }}">
                        <span class="price-badge bg-primary">{{ $plan->name }}</span>
                        @if (\Auth::user()->type == 'Super Admin' && $plan->price > 0)
                            <div class="d-flex flex-row-reverse m-0 p-0 ">
                                <div class="form-check form-switch custom-switch-v1 mb-2">
                                    <input type="checkbox" name="plan_disable"
                                        class="form-check-input input-primary plan_disable" value="1"
                                        data-id='{{ $plan->id }}' data-company="{{ $plan->id }}"
                                        data-name="{{ __('user') }}" {{ $plan->plan_disable == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="plan_disable"></label>
                                </div>
                            </div>
                        @endif
                        @if (\Auth::user()->type == 'Admin' && \Auth::user()->plan == $plan->id)
                            <div class="d-flex flex-row-reverse m-0 p-0 ">
                                <span class="d-flex align-items-center ">
                                    <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                    <span class="ms-2">{{ __('Active') }}</span>
                                </span>
                            </div>
                        @endif

                        <h1 class="mb-4 f-w-600 ">
                            {{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ number_format($plan->price) }}<small
                                class="text-sm">{{ \App\Models\Plan::$arrDuration[$plan->duration] }}</small></h1>
                        <p class="mb-0 text-center">
                            {{ __('Free Trial Days : ') . __($plan->trial_days ? $plan->trial_days : 0) }}<br />
                        </p>
                        <p class="my-4 text-center">{{ $plan->description }}</p>

                        <ul class="list-unstyled">
                            <li> <span class="theme-avtar"><i
                                        class="text-primary ti ti-circle-plus"></i></span>{{ $plan->max_agent < 0 ? __('Unlimited') : $plan->max_agent }}
                                {{ __('Agent') }}</li>

                            <li> <span class="theme-avtar"><i
                                        class="text-primary ti ti-circle-plus"></i></span>{{ $plan->storage_limit }}
                                {{ __(' MB Storage') }}</li>
                            @if ($plan->enable_custdomain == 'on')
                                <li>
                                    <span class="theme-avtar">
                                        <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Custom Domain') }}
                                </li>
                            @else
                                <li class="text-danger">
                                    <span class="theme-avtar">
                                        <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Custom Domain') }}
                                </li>
                            @endif
                            @if ($plan->enable_custsubdomain == 'on')
                                <li>
                                    <span class="theme-avtar">
                                        <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Sub Domain') }}
                                </li>
                            @else
                                <li class="text-danger">
                                    <span class="theme-avtar">
                                        <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Sub Domain') }}
                                </li>
                            @endif
                            @if ($plan->enable_chatgpt == 'on')
                                <li>
                                    <span class="theme-avtar">
                                        <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Chatgpt') }}
                                </li>
                            @else
                                <li class="text-danger">
                                    <span class="theme-avtar">
                                        <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Chatgpt') }}
                                </li>
                            @endif
                        </ul>
                        <br>


                        @if ($plan->id != \Auth::user()->plan && \Auth::user()->type != 'Super Admin')
                            @if ($plan->price > 0 && \Auth::user()->trial_plan == 0 && \Auth::user()->plan != $plan->id && $plan->trial == 1)
                                <a href="{{ route('plan.trial', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                    class="btn btn-lg btn-primary btn-icon m-1">{{ __('Start Free Trial') }}</a>
                            @endif
                            @if ($plan->price > 0)
                                <a href="{{ route('plan.payment', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                    id="interested_plan_2" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Subscribe') }}" class="btn btn-lg btn-primary btn-icon m-12">
                                    <i class="ti ti-shopping-cart m-1 text-white"></i>{{ __('Subscribe') }}
                                </a>
                            @endif
                        @endif

                        @if (\Auth::user()->type != 'Super Admin' && \Auth::user()->plan != $plan->id)
                            @if ($plan->id != 1)
                                @if (\Auth::user()->requested_plan != $plan->id)
                                    <a href="{{ route('send.request', [\Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                        class="btn btn-lg btn-primary btn-icon m-1" data-title="{{ __('Send Request') }}"
                                        title="{{ __('Send Request') }}" data-bs-toggle="tooltip">
                                        <span class="btn-inner--icon"><i class="ti ti-corner-up-right"></i></span>
                                    </a>
                                @else
                                    <a href="{{ route('request.cancel', \Auth::user()->id) }}"
                                        class="btn btn-lg btn-primary btn-icon m-1" data-title="{{ __('Cancle Request') }}"
                                        title="{{ __('Cancle Request') }}" data-bs-toggle="tooltip">
                                        <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                    </a>
                                @endif
                            @endif
                        @endif

                        @if (\Auth::user()->type == 'Super Admin')
                            <div class="row align-items-center">
                                <div class="col-3"></div>
                                <div class="col-2 me-3 mt-1">
                                    <a title="Edit Plan" href="#" class="btn btn-primary btn-icon m-1"
                                        data-url="{{ route('plan.edit', $plan->id) }}" data-ajax-popup="true"
                                        data-title="{{ __('Edit Plan') }}" data-size="lg" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Edit') }}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </div>
                                @if ($plan->price > 0)
                                    <div class="col-3">
                                        <form method="POST" action="{{ route('plan.destroy', $plan->id) }}"
                                            id="delete-form-{{ $plan->id }}">
                                            @csrf
                                            <input name="_method" type="hidden" value="DELETE">
                                            <button type="submit" class="btn btn-danger btn-icon m-1 show_confirm"
                                                data-toggle="tooltip" title="{{ __('Delete') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (\Auth::user()->type == 'Admin' && \Auth::user()->trial_expire_date)
                            @if (\Auth::user()->type == 'Admin' && \Auth::user()->trial_plan == $plan->id)
                                <p class="display-total-time mb-0">
                                    {{ __('Plan Trial Expired : ') }}
                                    {{ !empty(\Auth::user()->trial_expire_date) ? \Auth::user()->dateFormat(\Auth::user()->trial_expire_date) : 'lifetime' }}
                                </p>
                            @endif
                        @else
                            @if (\Auth::user()->type == 'Admin' && \Auth::user()->plan == $plan->id)
                                <p class="display-total-time mb-0">
                                    {{ __('Plan Expired : ') }}
                                    {{ !empty(\Auth::user()->plan_expire_date) ? \Auth::user()->dateFormat(\Auth::user()->plan_expire_date) : 'lifetime' }}
                                </p>
                            @endif
                        @endif
                    </div>

                </div>
            </div>
        @endforeach
    </div>


@endsection
@push('scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        $(document).on("click", ".plan_disable", function() {
            var id = $(this).attr('data-id');
            var plan_disable = ($(this).is(':checked')) ? $(this).val() : 0;


            $.ajax({
                url: '{{ route('plan.unable') }}',
                type: 'POST',
                data: {
                    "plan_disable": plan_disable,
                    "id": id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if (data.success) {
                        show_toastr('success', data.success);
                    } else {
                        show_toastr('error', data.error);

                    }
                }
            });
        });
    </script>
@endpush
