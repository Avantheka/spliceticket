@extends('layouts.admin')
@section('page-title')
    {{ __('Manage User Log') }}
@endsection



@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('User Log') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['userlog'], 'method' => 'get', 'id' => 'userlogin_filter']) }}

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-5">
                                <div class="row">

                                    <div class="col-xl-6 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('select_month', __('Select Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : '', ['class' => 'form-control']) }}

                                        </div>
                                    </div>

                                    <div class="col-xl-6 col-lg-3 col-md-6 col-sm-12 col-12">

                                        <div class="btn-box">
                                            {{ Form::label('user', __('Select User'), ['class' => 'form-label']) }}
                                            {{ Form::select('user', $usersList, isset($_GET['user']) ? $_GET['user'] : '', ['class' => 'form-control select ', 'id' => 'user_id']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('userlogin_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('userlog') }}" class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
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
                                <th> {{ __('Name') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th> {{ __('Email') }}</th>
                                <th> {{ __('Ip') }}</th>
                                <th> {{ __('Last Login') }}</th>
                                <th>{{ __('Country') }}</th>
                                <th> {{ __('Device Type') }}</th>
                                <th>{{ __('OS Name') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    @php
                                        $data = json_decode($user->details);
                                        $month = date('m', strtotime($user->date));
                                    @endphp
                                    @if ($month == date('m'))
                                        <td>{{ $user->user_name }}</td>
                                        <th>
                                            <div class="badge bg-primary p-2 px-3 status-btn rounded">{{ $user->role }}
                                            </div>
                                        </th>
                                        <td>{{ $user->user_email }}</td>
                                        <td>{{ $user->ip }}</td>
                                        <td>{{ $user->date }}</td>
                                        <td>{{ $data->country }}</td>
                                        <td>{{ $data->device_type }}</td>
                                        <td>{{ $data->os_name }}</td>
                                        <td>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" data-url="{{ route('userlog.display', $user->id) }}"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    title="{{ __('View Log Details') }}" data-ajax-popup="true"
                                                    data-size="xs" data-title="{{ __('View User Log') }}">
                                                    <span class="text-white"><i class="ti ti-eye"></i></span>
                                                </a>
                                            </div>


                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{route('userlog.destroy',$user->id)}}" id="user-form-{{$user->id}}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip"
                                                    title='Delete'>
                                                        <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
