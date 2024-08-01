@extends('layouts.admin')

@section('page-title')
    {{ __('Create User') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@php
    $settings = App\Models\Utility::settings();

@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="post" class="needs-validation" action="{{ route('users.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Name') }}</label>
                                <div class="col-sm-12 col-md-12">
                                    <input type="text" placeholder="{{ __('Full name of the user') }}" name="name"
                                        class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                        value="{{ old('name') }}" autofocus>
                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Email') }}</label>
                                <div class="col-sm-12 col-md-12">
                                    <input type="email" placeholder="{{ __('Email address (should be unique)') }}"
                                        name="email"
                                        class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        value="{{ old('email') }}">
                                    <div class="invalid-feedback">
                                        {{ $errors->first('email') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (\Auth::user()->type == 'Admin')
                            <div class="col-12 form-group">
                                {{ Form::label('categories', __('Category'), ['class' => 'col-form-label']) }}
                                {{ Form::select('categories[]', $categories, null, ['class' => 'form-control multi-select ', 'id' => 'choices-multiple', 'multiple' => '']) }}
                            </div>
                        @endif

                        <div class="col-md-5 mb-3">
                            <span>{{ __('Login is enable') }}</span>
                            <div class="form-check form-switch d-inline-block custom-switch-v1">
                                {{ Form::checkbox('password_switch', '1', isset($settings['password_switch']) && $settings['password_switch'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'password_switch']) }}
                                <label class="form-check-label" for="password_switch"></label>

                                <input type="checkbox" name="password_switch"
                                    class="form-check-input input-primary pointer" value="on" id="password_switch"
                                    {{ isset($settings['password_switch']) && $settings['password_switch'] == 'on' ? 'checked="checked"' : '' }}>
                            </div>
                        </div>
                        <div class="row ps_div d-none">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Password') }}</label>
                                <div class="col-sm-12 col-md-12">
                                    <input type="password" name="password"
                                        placeholder="{{ __('Set an account password') }}"
                                        class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}">
                                    <div class="invalid-feedback">
                                        {{ $errors->first('password') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">{{ __('Confirm Password') }}</label>
                                <div class="col-sm-12 col-md-12">
                                    <input type="password" name="password_confirmation"
                                        placeholder="{{ __('Confirm account password') }}"
                                        class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}">
                                    <div class="invalid-feedback">
                                        {{ $errors->first('password_confirmation') }}
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="form-label">{{ __('Picture') }}</label>
                                <div class="col-sm-12 col-md-12">
                                    <div class="form-group col-lg-12 col-md-12">
                                        <div class="choose-file form-group">
                                            <label for="file" class="form-label">
                                                <div>{{ __('Choose File Here') }}</div>
                                                <input type="file" name="avatar" id="avatar"
                                                    class="form-control {{ $errors->has('avatar') ? ' is-invalid' : '' }}"
                                                    onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])"
                                                    data-filename="avatar_selection">
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
                                        {{-- <div class="user-main-image"> --}}
                                        <img src="" id="blah" width="25%" class="rounded-pill" />
                                        {{-- </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label class="form-label"></label>
                                <div class="col-sm-12 col-md-12 text-end">
                                    <button
                                        class="btn btn-primary btn-block mt-2 btn-submit"><span>{{ __('Add') }}</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endsection
        @push('scripts')
            <script src="//cdn.ckeditor.com/4.12.1/basic/ckeditor.js"></script>
            <script src="{{ asset('js/editorplaceholder.js') }}"></script>
            <script>
                $(document).ready(function() {
                    $.each($('.ckdescription'), function(i, editor) {

                        CKEDITOR.replace(editor, {
                            extraPlugins: 'editorplaceholder',
                            editorplaceholder: editor.placeholder,
                            removeButtons: 'Image,TextColor,BGColor,ShowBlocks,Maximize,FontSize,Format,Styles,Font,Strike,Subscript,Superscript,CopyFormatting,RemoveFormat,Blockquote,CreateDiv,JustifyLeft,JustifyCenter,JustifyBlock,JustifyRight,BidiLtr,BidiRtl,Language,Anchor,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Checkbox,Form,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Scayt,SelectAll,Find,Undo,Redo,Replace,Source,Save,NewPage,ExportPdf,Preview,Print,PasteFromWord,PasteText,Paste,Copy,Cut,Templates,About'

                        });
                    });
                });
            </script>
            <script>
                $(document).on('change', '#password_switch', function() {
                    if ($(this).is(':checked')) {
                        $('.ps_div').removeClass('d-none');
                        $('#password').attr("required", true);

                    } else {
                        $('.ps_div').addClass('d-none');
                        $('#password').val(null);
                        $('#password').removeAttr("required");
                    }
                });
            </script>
        @endpush
