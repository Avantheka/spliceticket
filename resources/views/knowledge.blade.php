@extends('layouts.auth')
@section('page-title')
    {{ __('Knowledge') }}
@endsection

@php
    $logos=\App\Models\Utility::get_file('uploads/logo/');
@endphp
@section('content')
    <div class="auth-wrapper auth-v1">
        <div class="bg-auth-side bg-primary"></div>
        <div class="auth-content">

            <nav class="navbar navbar-expand-md navbar-dark default dark_background_color">
                <div class="container-fluid pe-2">
                    <a class="navbar-brand" href="#">
                        <img src="{{ $logos.'logo/logo-light.png'.'?'.time() }}" alt="logo"  height="41px" width="150px"/>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                        <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Agent Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">{{ __('Create Ticket') }}</a>
                            </li>
                            <li class="nav-item">
                                @if ($setting['FAQ'] == 'on')
                                    <a class="nav-link" href="{{route('faq')}}">{{ __('FAQ') }}</a>
                                @endif
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('search') }}">{{ __('Search Ticket') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="row align-items-center justify-content-center text-start">
                <div class="col-xl-12 text-center">
                    <div class="mx-3 mx-md-5">
                        <h2 class="mb-3 text-white f-w-600">{{ __('Knowledge') }}</h2>
                    </div>

                    <div class="text-start">
                        @if ($knowledges->count())
                            <div class="row">
                                @foreach ($knowledges as $index => $knowledge)
                                    <div class="col-md-4">
                                        <div class="card" style="min-height: 200px;">
                                            <div class="card-header py-3 mb-3" id="heading-{{ $index }}"role="button"
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}">
                                                <div class="row m-auto">
                                                    <h6 class="mr-3">{{ App\Models\Knowledge::knowlege_details($knowledge->title)}}  ( {{ App\Models\Knowledge::category_count($knowledge->category)}} ) </h6>
                                                </div>
                                            </div>
                                            <ul class="knowledge_ul">
                                                @foreach ($knowledges_detail as $details)
                                                    @if ($knowledge->category == $details->category)
                                                        <li style="list-style: none;" class="child">
                                                            <a href="{{ route('knowledgedesc',['id'=>$details->id])}}">
                                                                <i class="far fa-file-alt ms-3"></i>  {{ !empty($details->title) ? $details->title : '-' }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0 text-center">{{ __('No Knowledges found.') }}</h6>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <div class="auth-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-6">

                            <p class="text-muted">{{env('FOOTER_TEXT')}}</p>
                        </div>
                        <div class="col-6 text-end">

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
