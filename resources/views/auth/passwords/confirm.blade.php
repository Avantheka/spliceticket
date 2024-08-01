@extends('layouts.auth')

@section('page-title')
    {{ __('Set a New Password') }}
@endsection
@php
    $logos=\App\Models\Utility::get_file('uploads/logo/');
@endphp
@section('content')
    <div class="col-lg-5 col-md-7">
        <a class="navbar-brand d-flex justify-content-center mt-10 mb-4" href="#">
            <img src="{{ $logos.'logo-dark.png' }}" class="auth-logo" alt="logo">
        </a>
        <div class="card bg-secondary border-0 mb-0">
            <div class="card-body px-lg-5 py-lg-5">
                <div class="text-center text-muted mb-4">
                    <h2 class="mb-3 text-18">{{ __('Confirm Password') }}</h2>
                </div>
                {{ __('Please confirm your password before continuing.') }}

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    <div class="form-group col-md-12">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <div class="form-icon-user">
                            <span class="prefix-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" placeholder="{{ __('Enter Password') }}" required="" value="{{old('password')}}">
                            <div class="invalid-feedback d-block">
                                {{ $errors->first('password') }}
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-submit btn-block mt-3">{{ __('Confirm') }}</button>
                        <a href="{{ route('login') }}" class="d-block mt-2"><small>{{ __('Login') }}</small></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
