@php
    $logo = Utility::get_superadmin_logo();
    $logos = \App\Models\Utility::get_file('uploads/logo/');
    $lang = app()->getLocale();
    if ($lang == 'ar' || $lang == 'he') {
        $settings['SITE_RTL'] = 'on';
    }
    $LangName = \App\Models\Languages::where('code', $lang)->first();
    if (empty($LangName)) {
        $LangName = new App\Models\Utility();
        $LangName->fullName = 'English';
    }

    // $setting = App\Models\Settings::colorset();
    // $color = 'theme-3';
    // if (!empty($setting['color'])) {
    //     $color = $setting['color'];
    // }
    $setting = App\Models\Utility::colorset();

    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    if(isset($setting['color_flag']) && $setting['color_flag'] == 'true')
    {
        $themeColor = 'custom-color';
    }
    else {
        $themeColor = $color;
    }
    $settings = Utility::settings();

@endphp

@extends('layouts.auth')

@section('page-title')
    {{ __('Set a New Password') }}
@endsection


@section('content')


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
                            class="logo" height="41px" width="150px" />
                    </a>
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarlogin">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarlogin">
                    <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                        @include('landingpage::layouts.buttons')

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Agent Login') }}</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('search') }}">{{ __('Search Ticket') }}</a>
                        </li>
                        <div class="lang-dropdown-only-desk">
                            <li class="dropdown dash-h-item drp-language">
                                <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="drp-text"> {{ ucfirst($LangName->fullName) }}
                                    </span>
                                </a>
                                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                                    @foreach (App\Models\Utility::languages() as $code => $language)
                                        <a href="{{ route('password.reset', $code) }}" tabindex="0"
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
                                <h2 class="mb-3 f-w-600">{{ __('Reset Password') }}</h2>

                            </div>
                            <form method="POST" action="{{ route('password.update') }}">
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                @csrf

                                <div class="custom-login-form">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">{{ __('Email address') }}</label>

                                        <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" id="email" name="email" placeholder="{{ __('Enter Your Email') }}" required="" value="{{ old('email') }}">
                                        <div class="invalid-feedback d-block">
                                            {{ $errors->first('email') }}
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="password" class="form-label">{{ __('Password') }}</label>
                                        <input type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" placeholder="{{ __('Enter Password') }}" required="" value="{{ old('password') }}">
                                        <div class="invalid-feedback d-block">
                                            {{ $errors->first('password') }}
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                                        <input type="password" class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Enter Confirm Password') }}" required="">
                                        <div class="invalid-feedback d-block">
                                            {{ $errors->first('password_confirmation') }}
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button class="btn btn-primary btn-submit btn-block mt-2">{{ __('Reset Password') }}</button>
                                    </div>

                                    <p class="my-4 text-center d-flex"><a
                                        href="{{ route('login') }}" tabindex="0" >{{ __(' Log In') }}</a>
                                    </p>
                                </div>
                            </form>
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
