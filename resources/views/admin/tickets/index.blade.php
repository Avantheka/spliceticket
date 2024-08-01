@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Tickets') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Tickets') }}</li>
@endsection

@section('multiple-action-button')
    <div class="row justify-content-end">
        <div class="col-auto">
            @can('create-tickets')
                <div class="btn btn-sm btn-primary btn-icon m-1 float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create Ticket')}}">
                    <a href="{{route('tickets.create')}}" class=""><i class="ti ti-plus text-white"></i></a>
                </div>
            @endcan
            <div class="btn btn-sm btn-primary btn-icon m-1 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Export Tickets CSV file')}}">
                <a href="{{route('tickets.export')}}" class="" ><i class="ti ti-file-export text-white"></i></a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            @if(session()->has('ticket_id') || session()->has('smtp_error'))
                <div class="alert alert-info">
                    @if(session()->has('ticket_id'))
                        {!! Session::get('ticket_id') !!}
                        {{ Session::forget('ticket_id') }}
                    @endif
                    @if(session()->has('smtp_error'))
                        {!! Session::get('smtp_error') !!}
                        {{ Session::forget('smtp_error') }}
                    @endif
                </div>
            @endif
        </div>
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                    {{ Form::open(array('route' => array('tickets.index'),'method' => 'GET','id'=>'ticket_index')) }}

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-5">
                                <div class="row">

                                    <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                                {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                                {{ Form::select('category',$categories,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12 col-12">

                                        <div class="btn-box">
                                            {{ Form::label('status', __('Status'),['class'=>'form-label']) }}
                                            <select name="status" class="form-control select" id="">
                                                <option value="All">{{__('Select Status')}}</option>
                                                @foreach ($statues as $item)
                                                    <option {{ isset($_GET['status']) && $_GET['status'] == $item ? 'selected' : ""}}  value="{{ $item }}">{{ $item }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                                {{ Form::label('priority', __('Priority'),['class'=>'form-label']) }}
                                                {{ Form::select('priority',$priorities,isset($_GET['priority'])?$_GET['priority']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('ticket_index').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('tickets.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                            <tr>

                                <th>{{ __('Ticket ID') }}</th>
                                <th class="w-10">{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Timing') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th class="text-end me-3">{{ __('Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td class="Id sorting_1">
                                        <a class="btn btn-outline-primary" href="{{route('tickets.edit',$ticket->id)}}">
                                            {{$ticket->ticket_id}}
                                        </a>
                                    </td>
                                    <td><span class="white-space">{{$ticket->name}}</span></td>
                                    <td>{{$ticket->email}}</td>

                                    <td>
                                        <span class="badge  p-2 px-3 rounded fix_badge" style="background: {{$ticket->color}}">{{$ticket->category_name}}</span>
                                    </td>


                                    <td><span class="badge fix_badge @if($ticket->status == 'New Ticket') bg-secondary @elseif($ticket->status == 'In Progress')bg-info  @elseif($ticket->status == 'On Hold') bg-warning @elseif($ticket->status == 'Closed') bg-primary @else bg-success @endif  p-2 px-3 rounded">{{__($ticket->status)}}</span></td>
                                    <td>
                                        <span class="badge  p-2 px-3 rounded fix_badge" style="background: {{$ticket->priorities_color}}">{{$ticket->priorities_name}}</span>
                                    </td>

                                    {{-- @dd($ticket->groupTime) --}}

                                    <td>
                                        <span>
                                            @if((string)$ticket->responseTimeconvertinhours == 'off')
                                            {{__('Response In')}}: {{$ticket->priorities->policies->response_within}} {{$ticket->priorities->policies->response_time}} <br>

                                            @else
                                                @if($ticket->responseTimeconvertinhours)
                                                <span class="text-danger" >{{ __('Response Overdue') }}</span>
                                                @else
                                                 {{ __('Response time') }}
                                                @endif : {{ $ticket->responsetime }}  <br>
                                            @endif
                                        </span>
                                        <span>

                                            @if($ticket->status=='Resolved')
                                                @if($ticket->resolveTimeconvertinhours)
                                                <span class="text-danger" > {{ __('Resolve Overdue') }}</span>
                                                @else
                                                {{__('Resolve Time')}}
                                                @endif : {{$ticket->resolvetime}}
                                            @else
                                            {{__('Resolve In')}}: {{$ticket->priorities->policies->resolve_within}} {{$ticket->priorities->policies->resolve_time}}
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{$ticket->created_at->diffForHumans()}}</td>
                                    <td class="text-end">
                                        @can('reply-tickets')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('tickets.edit', $ticket->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center"  data-bs-toggle="tooltip" title="{{ __('Reply') }}"> <span class="text-white"> <i class="ti ti-corner-up-left"></i></span></a>
                                            </div>
                                        @endcan
                                        @can('delete-tickets')
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{route('tickets.destroy',$ticket->id)}}" id="user-form-{{$ticket->id}}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"  data-bs-toggle="tooltip"
                                                    title='Delete'>
                                                        <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
