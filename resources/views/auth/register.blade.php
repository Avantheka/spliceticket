@php
    $logo = Utility::get_superadmin_logo();
    $logos = \App\Models\Utility::get_file('uploads/logo/');

    $LangName = \App\Models\Languages::where('code', $lang)->first();
    if (empty($LangName)) {
        $LangName = new App\Models\Utility();
        $LangName->fullName = 'English';
    }
    $setting = App\Models\Utility::colorset();

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }
    $settings =   App\Models\Utility::settings();

    config(
        [
            'captcha.secret' => $settings['NOCAPTCHA_SECRET'],
            'captcha.sitekey' => $settings['NOCAPTCHA_SITEKEY'],
            'options' => [
                'timeout' => 30,
            ],
        ]
    );
@endphp

@extends('layouts.auth')

@section('page-title')
    {{ __('Register') }}
@endsection

@push('custom-scripts')
    @if (Utility::getSettingValByName('RECAPTCHA_MODULE') == 'yes')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush

@section('content')

<div class="custom-login">
    <div class="login-bg-img">
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
                            class="logo" height="41px" width="150px" />
                    </a>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarlogin">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarlogin">
                    <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                        @include('landingpage::layouts.buttons')
                        <div class="lang-dropdown-only-desk">
                            <li class="dropdown dash-h-item drp-language">
                                <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="drp-text"> {{ ucfirst($LangName->fullName) }}
                                    </span>
                                </a>
                                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                                    @foreach (App\Models\Utility::languages() as $code => $language)
                                        <a href="{{ route('register',[$ref,$code]) }}" tabindex="0"
                                            class="dropdown-item dropdown-item {{ $LangName->code == $code ? 'active' : '' }}">
                                            <span>{{ ucFirst($language) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </li>
                        </div>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="custom-wrapper">
            <div class="custom-row">

                    <div class="card">

                        <div class="card-body">
                                <div>
                                    <h2 class="mb-3 f-w-600">{{ __('Register') }}</h2>
                                </div>
                                {{ Form::open(['route' => 'register', 'method' => 'post', 'id' => 'loginForm']) }}
                                <input type="hidden" name="ref_code" value="{{$ref}}">
                                @if (session('status'))
                                    <div class="mb-4 font-medium text-lg text-green-600 text-danger">
                                        {{ __('Email SMTP settings does not configured so please contact to your site admin.') }}
                                    </div>
                                @endif

                                <div class="custom-login-form">
                                    <div class="form-group mb-3">
                                        <label class="form-label d-flex">{{ __('Full Name') }}</label>
                                        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Username'),'required'=>'required']) }}
                                    </div>
                                    @error('name')
                                        <span class="error invalid-name text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="form-group mb-3">
                                        <label class="form-label d-flex">{{ __('Email') }}</label>
                                        {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Email address'),'required'=>'required']) }}
                                    </div>
                                    @error('email')
                                        <span class="error invalid-email text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="form-group mb-3">
                                        <label class="form-label d-flex">{{ __('Password') }}</label>
                                        {{ Form::password('password', ['class' => 'form-control', 'id' => 'input-password', 'placeholder' => __('Enter Password'),'required'=>'required']) }}
                                    </div>
                                    @error('password')
                                        <span class="error invalid-password text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="form-group">
                                        <label class="form-control-label d-flex">{{ __('Confirm password') }}</label>
                                        {{ Form::password('password_confirmation', ['class' => 'form-control', 'id' => 'confirm-input-password', 'placeholder' => __('Enter Confirm Password'),'required'=>'required']) }}

                                        @error('password_confirmation')
                                            <span class="error invalid-password_confirmation text-danger" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    @if (Utility::getSettingValByName('RECAPTCHA_MODULE') == 'yes')
                                    <div class="form-group mb-4">
                                        {!! NoCaptcha::display() !!}
                                        @error('g-recaptcha-response')
                                        <span class="small text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    @endif
                                    <div class="d-grid">

                                        <button class="btn btn-primary btn-block mt-2" id="login_button">{{ __('Register') }}</button>
                                    </div>

                                </div>

                                    <p class="my-4 text-center d-flex">{{ __('Already have an account? ') }}<a
                                            href="{{ route('login',$lang) }}"
                                            tabindex="0">{{ __('Login') }}</a>
                                    </p>

                            {{{ Form::close() }}}

                        </div>
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
@endsection

@push('scripts')
    @if (Utility::getSettingValByName('RECAPTCHA_MODULE') == 'yes')
    {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
