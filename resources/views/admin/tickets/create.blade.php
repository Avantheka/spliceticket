@extends('layouts.admin')

@section('page-title')
    {{ __('Create Ticket') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">{{ __('Tickets') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@php
$plansettings = \App\Models\Utility::plansettings();
@endphp
@section('content')
    <form action="{{route('tickets.store')}}" class="mt-3" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header row">
                        <div class="col-md-6">

                            <h6>{{ __('Ticket Information') }}</h6>
                        </div>
                        @if($plansettings["enable_chatgpt"] == 'on')
                        <div class="col-md-6">
                            <a class="btn btn-primary btn-sm float-end ms-2" href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate',['support']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Content with AI') }}"><i class="fas fa-robot"> {{ __('Generate with AI') }}</i></a>

                        </div>
                        @endif

                    </div>

                    <div class="card-body">

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Name') }}</label>
                                <input class="form-control {{(!empty($errors->first('name')) ? 'is-invalid' : '')}}" type="text" name="name" required="" placeholder="{{ __('Name') }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Email') }}</label>
                                <input class="form-control {{(!empty($errors->first('email')) ? 'is-invalid' : '')}}" type="email" name="email" required="" placeholder="{{ __('Email') }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Category') }}</label>
                                <select class="form-control {{(!empty($errors->first('category')) ? 'is-invalid' : '')}}" name="category" required="">
                                    <option value="">{{ __('Select Category') }}</option>

                                    @foreach($categories as $category)

                                        <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach

                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('category') }}
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Status') }}</label>
                                <select class="form-control {{(!empty($errors->first('status')) ? 'is-invalid' : '')}}" name="status" required="">
                                    <option value="">{{ __('Select Status') }}</option>
                                    <option value="New Ticket">{{ __('New Ticket') }}</option>
                                    <option value="In Progress">{{ __('In Progress') }}</option>
                                    <option value="On Hold">{{ __('On Hold') }}</option>
                                    <option value="Closed">{{ __('Closed') }}</option>
                                    <option value="Resolved">{{ __('Resolved') }}</option>
                                </select>




                                <div class="invalid-feedback">
                                    {{ $errors->first('status') }}
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Subject') }}</label>
                                <input class="form-control {{(!empty($errors->first('subject')) ? 'is-invalid' : '')}}" type="text" name="subject" required="" placeholder="{{ __('Subject') }}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('subject') }}
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Attachments') }} <small>({{__('You can select multiple files')}})</small> </label>
                                <div class="choose-file form-group">
                                    <label for="file" class="form-label d-block">

                                        <input type="file" name="attachments[]" id="file" class="form-control mb-2 {{ $errors->has('attachments') ? ' is-invalid' : '' }}" multiple=""  data-filename="multiple_file_selection" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                        <img src="" id="blah" width="20%"/>
                                        <div class="invalid-feedback">
                                            {{ $errors->first('attachments.*') }}
                                        </div>
                                    </label>
                                </div>
                                <p class="multiple_file_selection mx-4"></p>
                            </div>

                            <div class="form-group col-md-6">

                                    <label class="require form-label">{{ __('Priority') }}</label>
                                    <select class="form-control {{(!empty($errors->first('priority')) ? 'is-invalid' : '')}}" name="priority" required="">
                                        <option value="">{{ __('Select Priority') }}</option>

                                        @foreach($priorities as $priority)

                                            <option value="{{$priority->id}}">{{$priority->name}}</option>
                                        @endforeach

                                    </select>
                                    <div class="invalid-feedback">
                                        {{ $errors->first('priority') }}
                                    </div>

                            </div>
                            <div class="form-group col-md-12">
                                <label class="require form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" class="form-control ckdescription {{(!empty($errors->first('description')) ? 'is-invalid' : '')}}"></textarea>
                                <div class="invalid-feedback">
                                    {{ $errors->first('description') }}
                                </div>
                            </div>
                            @if(!$customFields->isEmpty())
                                @include('admin.customFields.formBuilder')
                            @endif
                        </div>
                        <div class="d-flex justify-content-end text-end">
                            <a class="btn btn-secondary btn-light btn-submit" href="{{route('tickets.index')}}">{{ __('Cancel') }}</a>
                            <button class="btn btn-primary btn-submit ms-2" type="submit">{{ __('Submit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="//cdn.ckeditor.com/4.12.1/basic/ckeditor.js"></script>
    <script src="{{ asset('js/editorplaceholder.js') }}"></script>
    <script>
    $(document).ready(function () {
        $.each($('.ckdescription'), function (i, editor) {

            CKEDITOR.replace(editor, {
                extraPlugins: 'editorplaceholder',
                editorplaceholder: editor.placeholder,
                removeButtons : 'Source,Save,NewPage,ExportPdf,Preview,Print,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Find,Replace,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,CopyFormatting,RemoveFormat,Blockquote,BidiLtr,BidiRtl,Language,Anchor,Image,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,About,Maximize,ShowBlocks,TextColor,BGColor,Format,Font,FontSize'
            });
        });
    });

    </script>
@endpush

