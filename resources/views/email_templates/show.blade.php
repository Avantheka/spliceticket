@extends('layouts.admin')

@section('page-title')
{{__('Email Templates')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Email Templates') }}</li>
@endsection



@push('css-page')
<link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush

@push('scripts')

<script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
<script src="{{asset('js/tinymce/tinymce.min.js')}}"></script>
<script>
    if ($(".pc-tinymce-2").length) {
        tinymce.init({
            selector: '.pc-tinymce-2',
            height: "400",
            content_style: 'body { font-family: "Inter", sans-serif; }'
        });
    }
</script>
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
@php
     $LangName = $currEmailTempLang->language;

@endphp

@section('multiple-action-button')
<div class="text-end mb-3">
    <div class="d-flex justify-content-end drp-languages">
        <ul class="list-unstyled mb-0 m-2">
            <li class="dropdown dash-h-item drp-language" style="list-style-type: none;">

                <a
                class="dash-head-link dropdown-toggle arrow-none me-0"
                data-bs-toggle="dropdown"
                href="#"
                role="button"
                aria-haspopup="false"
                aria-expanded="false"
                >
                <span class="drp-text hide-mob text-primary">{{ __('Locale: ') }}{{ ucFirst($LangName->fullName) }}</span>
                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                </a>

                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                    @foreach ($languages as $code => $lang)
                    <a href="{{ route('manage.email.language', [$emailTemplate->id, $code]) }}"
                        class="dropdown-item {{ $currEmailTempLang->lang == $code ? 'text-primary' : '' }}">{{ ucFirst($lang) }}</a>
                @endforeach

                </div>
            </li>
        </ul>
        <ul class="list-unstyled mb-0 m-2">
            <li class="dropdown dash-h-item drp-language" style="list-style-type: none;">
                <a class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <span class="drp-text hide-mob text-primary">{{ __('Template: ') }} {{ $emailTemplate->name }}</span>
                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                </a>
                <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                    @foreach ($EmailTemplates as $EmailTemplate)
                    <a href="{{ route('manage.email.language', [$EmailTemplate->id,(Request::segment(3)?Request::segment(3):\Auth::user()->lang)]) }}"
                    class="dropdown-item {{$emailTemplate->name == $EmailTemplate->name ? 'text-primary' : '' }}">{{ $EmailTemplate->name }}
                    </a>
                @endforeach

                </div>
            </li>
        </ul>
        <ul class="list-unstyled mb-0 m-2">
            <li class="dropdown dash-h-item drp-language" style="list-style-type: none;">
                <a class="btn btn-primary btn-sm float-end" style="height:35px; margin-top:3px;" href="#" data-size="md" data-ajax-popup-over="true" data-url="{{ route('generate',['email template']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Content with AI') }}"><i class="fas fa-robot"> {{ __('Generate with AI') }}</i></a>
            </li>
        </ul>
    </div>
</div>
@endsection
@section('content')



    <div class="row">

        <div class="col-12">
            <div class="row">

            </div>
            <div class="card">
                <div class="card-body">

                    <div class="language-wrap">
                        <div class="row">
                            <h6>{{ __('Place Holders') }}</h6>
                            <div class="col-lg-12 col-md-9 col-sm-12 language-form-wrap">

                                <div class="card">
                                    <div class="card-header card-body">
                                        <div class="row text-xs">
                                            @if($emailTemplate->slug=='new_ticket')
                                                <div class="row">
                                                    <p class="col-6">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-6">{{__('Ticket Name')}} : <span class="pull-right text-primary">{ticket_name}</span></p>
                                                    <p class="col-6">{{__('Ticket Id')}} : <span class="pull-right text-primary">{ticket_id}</span></p>
                                                    <p class="col-6">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-6">{{__('Email')}} : <span class="pull-right text-primary">{email}</span></p>
                                                    <p class="col-6">{{__('Password')}} : <span class="pull-right text-primary">{password}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='new_ticket_reply')
                                                <div class="row">
                                                    <p class="col-6">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-6">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-6">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-6">{{__('Ticket Name')}} : <span class="pull-right text-primary">{ticket_name}</span></p>
                                                    <p class="col-6">{{__('Ticket Id')}} : <span class="pull-right text-primary">{ticket_id}</span></p>
                                                    <p class="col-6">{{__('Ticket Description')}} : <span class="pull-right text-primary">{ticket_description}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='new_user')
                                                <div class="row">
                                                    <p class="col-6">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-6">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-6">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-6">{{__('Email')}} : <span class="pull-right text-primary">{email}</span></p>
                                                    <p class="col-6">{{__('Password')}} : <span class="pull-right text-primary">{password}</span></p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-9 col-sm-12 language-form-wrap">
                                {{Form::model($currEmailTempLang, array('route' => array('email_template.update', $currEmailTempLang->parent_id), 'method' => 'PUT')) }}
                                <div class="row">
                                    <div class="form-group col-12">
                                        {{Form::label('subject',__('Subject'),['class'=>'form-control-label text-dark'])}}
                                        {{Form::text('subject',null,array('class'=>'form-control font-style','required'=>'required'))}}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{Form::label('name',__('Name'),['class'=>'form-control-label text-dark'])}}
                                        {{Form::text('name',$emailTemplate->name,['class'=>'form-control font-style','disabled'=>'disabled'])}}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{Form::label('from',__('From'),['class'=>'form-control-label text-dark'])}}
                                        {{ Form::text('from', $emailTemplate->from, ['class' => 'form-control font-style', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-12">
                                        {{Form::label('content',__('Email Message'),['class'=>'form-control-label text-dark'])}}
                                        {{Form::textarea('content',$currEmailTempLang->content,array('class'=>'ckdescription','required'=>'required'))}}

                                    </div>


                                    <div class="col-md-12 text-end">
                                        {{Form::hidden('lang',null)}}
                                        <input type="submit" value="{{__('Save')}}" class="btn btn-print-invoice  btn-primary">
                                    </div>

                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
