@extends('layouts.admin')
@push('scripts')
    <script type="text/javascript">

        $(document).on("click", ".email-template-checkbox", function () {
            var chbox = $(this);
            $.ajax({
                url: chbox.attr('data-url'),
                data: {_token: $('meta[name="csrf-token"]').attr('content'), status: chbox.val()},
                type: 'post',
                success: function (response) {
                    if (response.is_success) {
                        toastr('Success', response.success, 'success');
                        if (chbox.val() == 1) {
                            $('#' + chbox.attr('id')).val(0);
                        } else {
                            $('#' + chbox.attr('id')).val(1);
                        }
                    } else {
                        toastr('Error', response.error, 'error');
                    }
                },
                error: function (response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        toastr('Error', response.error, 'error');
                    } else {
                        toastr('Error', response, 'error');
                    }
                }
            })
        });

    </script>
@endpush
@section('page-title')
    {{__('Email Notification')}}
@endsection
@section('title')
    {{__('Email Notification')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{__('Email Notification')}}</li>
@endsection
@section('action-btn')

@endsection
@section('content')

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <h5></h5>
                <div class="table-responsive">
                    <table id="datatable" class="table datatable align-items-center">
                        <thead>
                            <tr>
                                <th scope="col" class="sort" data-sort="name"> {{__('Name')}}</th>
                                @if(\Auth::user()->type =='owner')
                                    <th class="text-end">{{__('On / Off')}}</th>
                                @else
                                    <th class="text-end">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($EmailTemplates as $EmailTemplate)
                                <tr>
                                    <td>{{ $EmailTemplate->name }}</td>
                                    <td>
                                        @if(\Auth::user()->type=='super admin')
                                        <div class="text-end">
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('manage.email.language',[$EmailTemplate->id,\Auth::user()->lang]) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip" title="{{__('View')}}">
                                                <i class="ti ti-eye text-white"></i>
                                            </a>
                                        </div>
                                        </div>
                                        @endif
                                        @if(\Auth::user()->type=='owner')
                                        <div class="text-end">
                                            <div class="form-check form-switch d-inline-block">
                                                <label class="form-check-label form-switch">
                                                    <input type="checkbox" class="form-check-input email-template-checkbox" id="email_tempalte_{{$EmailTemplate->template->id}}"
                                                     @if(isset($EmailTemplate->template->is_active) == 1) checked="checked" @endif type="checkbox" value="{{$EmailTemplate->template->is_active}}"
                                                     data-url="{{route('status.email.language',[$EmailTemplate->template->id])}}"/>
                                                    <span class="slider1 round"></span>
                                                </label>


                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

