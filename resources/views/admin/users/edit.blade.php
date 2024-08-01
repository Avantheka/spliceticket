@extends('layouts.admin')

@section('page-title')
{{ __('Edit Profile') }} ({{ $user->name }})
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
<li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection


@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{route('users.update',$user->id)}}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Name') }}</label>
                            <div class="col-sm-12 col-md-12">
                                <input type="text" placeholder="{{ __('Full name of the user') }}" name="name" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ $user->name }}" autofocus>
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <div class="col-sm-12 col-md-12">
                                <input type="email" placeholder="{{ __('Email address (should be unique)') }}" name="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ $user->email }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Password') }}</label>
                            <div class="col-sm-12 col-md-12">
                                <input type="password" name="password" autocomplete="new-password" placeholder="{{ __('Set an account password') }}" class="form-control {{ $errors->has('password') ? ' is-invalid': '' }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('password') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                            <div class="col-sm-12 col-md-12">
                                <input type="password" name="password_confirmation" placeholder="{{ __('Confirm account password') }}" autocomplete="new-password" class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid': '' }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('password_confirmation') }}
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    @php
                    $userCatgory
                    @endphp
                    @if (\Auth::user()->type == 'Admin')
                    @if (\Auth::user()->id != $user->id || \Auth::user()->type == 'Agent')
                    <div class="col-12 form-group">

                        {{ Form::label('categories', __('Category'),['class'=>'col-form-label']) }}
                        {{ Form::select('categories[]', $categories,$userCatgory, array('class' => 'form-control multi-select ','id'=>'choices-multiple','multiple'=>'')) }}
                    </div>
                    @endif
                    @endif
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label class="form-label">{{ __('Picture') }}</label>
                            <div class="col-sm-12 col-md-12">
                                <div class="form-group col-lg-12 col-md-12">
                                    <div class="choose-file form-group">
                                        <label for="file" class="form-label">
                                            <div>{{ __('Choose File Here') }}</div>

                                            <input type="file" name="avatar" id="file" class="form-control {{ $errors->has('avatar') ? ' is-invalid' : '' }}" onchange="document.getElementById('blah3').src = window.URL.createObjectURL(this.files[0])">
                                            <div class="invalid-feedback">
                                                {{ $errors->first('avatar') }}
                                            </div>
                                        </label>
                                        <p class="avatar_selection"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label"></label>
                            <div class="col-sm-12 col-md-12">
                                <div class="form-group col-lg-12 col-md-12">
                                    <div class="user-main-image">
                                        @php
                                        $logos = \App\Models\Utility::get_file('public/');
                                        @endphp

                                        <a href="{{(!empty($user->avatar))? ($logos.$user->avatar): $logos."/avatar.png"}}" target="_blank">
                                            <img src="{{(!empty($user->avatar))? ($logos.$user->avatar): $logos."/avatar.png"}}" class="img-fluid rounded-circle card-avatar" width="35" id="blah3">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="form-label"></label>
                            <div class="col-sm-12 col-md-12 text-end">
                                <button class="btn btn-primary btn-block btn-submit"><span>{{ __('Update') }}</span></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
