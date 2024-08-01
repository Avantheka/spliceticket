@php
$settings = \App\Models\Utility::settings();
@endphp
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage SLA Policiy') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('SLA Policiy') }}</li>
@endsection
@section('action-button')

@endsection
@section('content')

<div class="row">
    <div class="col-3">
        @include('layouts.setup')
    </div>
    <div class="col-9">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="well_white">
                    <div class="bold"><b>Targets</b></div>
                    <div class="hint">Set a time target for each priority.</div>
                    <hr style="margin:10px 0">
                    <div class="table-responsive m-0 custom-field-table">
                        <table class="table dataTable-table" id="pc-dt-simple" data-repeater-list="fields">
                             <thead class="thead-light">
                                <tr>
                                    <th scope="col" >{{ __('Priority') }}</th>
                                    <th scope="col">{{ __('Response within') }}</th>
                                    <th scope="col">
                                        <div class="form-check form-switch custom-control-inline">
                                             {{ Form::open(['route' => 'policiy.store', 'method' => 'post']) }}
                                            {{-- <input id="" type="checkbox" checked=""> --}}
                                            <input type="checkbox" class="form-check-input" role="switch" id="resolve"  name="resolve_status" {{ $settings['resolve_status'] == 1 ? 'checked' : "" }}>
                                            <label for="resolve_status_cbox">{{ __('Resolve within')}}</label>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($priority as $key =>  $priorities)
                                <tr>
                                    <td>

                                            <input type="text" class="form-control" id="priority_id" name="{{"priority[$priorities->name][priority]"}}" required=""  maxlength="255" value="{{$priorities->name}}" readonly>

                                            <input type="hidden" name="{{"priority[$priorities->name][priority_id]"}}" value="{{$priorities->id}}">

                                    </td>
                                    {{-- @dd($value['response_time']) --}}
                                    <td>
                                        <div class="row">
                                            <div class="col-sm-5">
                                                <input type="text" class="form-control  ng-not-empty ng-valid-pattern" id="response_within" name="{{"priority[$priorities->name][response_within]"}}" value="{{$priorities->policies->response_within ?? 0}}" >
                                            </div>
                                            <div class="col-sm-7" style="
                                            padding: 0px; width: 173px;">
                                                <select class="form-select" name="{{"priority[$priorities->name][response_time]"}}" required=""  >
                                                    <option value="Hour" @if ($priorities->policies->response_time == "Hour") selected @endif
                                                    >{{ __('Hour') }}</option>
                                                    <option value="Minute" @if ($priorities->policies->response_time == "Minute") selected @endif>{{ __('Minute') }}</option>
                                                    <option value = "Day"  @if ($priorities->policies->response_time == "Day") selected @endif
                                                    >{{ __('Day') }}</option>
                                                    <option value = "Month"  @if ($priorities->policies->response_time == "Month") selected @endif
                                                    >{{ __('Month') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row resolve">
                                            <div class="col-sm-5">
                                                <input type="text" class="form-control resolve_time" id="resolve_within" name="{{"priority[$priorities->name][resolve_within]"}}"  value="{{$priorities->policies->resolve_within ?? 0}}">
                                            </div>
                                            <div class="col-sm-7" style="
                                            padding: 0px;     width: 173px;">
                                                <select class="form-select resolve_duration {{ !empty($errors->first('resolve_time')) ? 'is-invalid' : '' }}" id="resolve_duration" name="{{"priority[$priorities->name][resolve_time]"}}" required="" >
                                                    <option value="Hour" @if ($priorities->policies->resolve_time == "Hour") selected @endif>{{ __('Hour') }}</option>
                                                    <option value="Minute" @if ($priorities->policies->resolve_time == "Minute") selected @endif>{{ __('Minute') }}</option>
                                                    <option value="Day" @if ($priorities->policies->resolve_time == "Day") selected @endif>{{ __('Day') }}</option>
                                                    <option value="Month" @if ($priorities->policies->resolve_time == "Month") selected @endif> {{ __('Month') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>






                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-end">
                            {{ Form::submit(__('Save Changes'), ['class' => 'btn-submit btn btn-primary']) }}
                        </div>
                        {{ Form::close() }}
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>



$( document ).ready(function() {
   if ($('#resolve').is(":checked")) {
            //enabled
            $('.resolve_time').attr('disabled',false);
            $('.resolve_duration').attr('disabled',false);
            preview();
        } else {
            // disabled
            $('.resolve_time').attr('disabled',true);
            $('.resolve_duration').attr('disabled',true);

        }
});


$(document).on('click', '#resolve_status', function () {
        if ($('#resolve').is(":checked")) {
            //enabled
            $('.resolve_time').attr('disabled',false);
            $('.resolve_duration').attr('disabled',false);
            preview();
        } else {
            // disabled
            $('.resolve_time').attr('disabled',true);
            $('.resolve_duration').attr('disabled',true);

        }
    });
</script>
@endpush

