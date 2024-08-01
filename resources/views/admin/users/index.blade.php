@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Users') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Users') }}</li>
@endsection
@php
    $logos = \App\Models\Utility::get_file('public/');
@endphp
@section('action-button')
    @can('create-users')
        <a href="{{ route('users.create') }}" class="">
            <div class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Create User') }}">
                <i class="ti ti-plus text-white"></i>
            </div>
        </a>
    @endcan
    @if (\Auth::user()->type == 'Admin')
        <a href="{{ route('userlog') }}" class="btn btn-sm btn-primary btn-icon" title="{{ __('User Login History') }}"
            data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="ti ti-user-check"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('Picture') }}</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Email') }}</th>
                                    @if (\Auth::user()->type == 'Admin')
                                        <th scope="col">{{ __('Category') }}</th>
                                    @endif
                                    <th scope="col">{{ __('Role') }}</th>
                                    <th scope="col" class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $index => $user)
                                    <tr>
                                        <th scope="row">{{ ++$index }}</th>
                                        <td>
                                            <a href="{{ !empty($user->avatar) ? $logos . $user->avatar : $logos . 'avatar.png' }}"
                                                target="_blank">
                                                <img src="{{ !empty($user->avatar) ? $logos . $user->avatar : $logos . 'avatar.png' }}"
                                                    class="img-fluid rounded-circle card-avatar" width="30"
                                                    id="blah3">
                                            </a>
                                        </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        @if (\Auth::user()->type == 'Admin')
                                            <td>
                                                @foreach ($user->categories as $category)
                                                    <span class="badge badge-white p-2 px-3 rounded fix_badge"
                                                        style="background: {{ $category->color }}">{{ $category->name }}</span>
                                                @endforeach
                                            </td>
                                        @endif
                                        <td>
                                            <span class="badge bg-primary p-2 px-3 rounded rounded">
                                                {{ $user->type }}
                                            </span>
                                        </td>
                                        <td class="text-end me-3">


                                            @if ($user->is_disable == 1 || \Auth::user()->type == 'Super Admin')
                                                @if (\Auth::user()->type == 'Super Admin')
                                                    <div class="action-btn bg-primary ms-2">


                                                        <a title="Admin Hub" data-size="lg" href="#"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center text-white"
                                                            data-url="{{ route('user.info', $user->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Admin Info') }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Admin Hub') }}"><i
                                                                class="ti ti-atom"></i></a>

                                                    </div>

                                                    <div class="action-btn bg-secondary ms-2">
                                                        <a href="{{ route('login.with.company', $user->id) }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Login As Admin') }}"> <span
                                                                class="text-white"><i class="ti ti-replace"></i></a>
                                                    </div>
                                                    <div class="action-btn bg-secondary ms-2">
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center text-white"
                                                            data-size="md"
                                                            data-url="{{ route('plan.upgrade', $user->id) }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            data-title="{{ __('Upgrade Plan') }}"
                                                            title="{{ __('Upgrade Plan') }}">
                                                            <i class="ti ti-trophy"></i>
                                                        </a>
                                                    </div>
                                                @endif



                                                @if ($user->is_enable_login == 1)
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Login Disable') }}"> <span
                                                                class="text-white"><i class="ti ti-road-sign"></i></a>
                                                    </div>
                                                @elseif ($user->is_enable_login == 0 && $user->password == null)
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" data-url="{{ route('user.reset',\Crypt::encrypt($user->id))}}"
                                                            data-ajax-popup="true" data-size="md" class="mx-3 btn btn-sm d-inline-flex align-items-center login_enable" data-title="{{ __('New Password') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('New Password')}}"> <span class="text-white"><i class="ti ti-road-sign"></i></a>
                                                    </div>
                                                @else

                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center login_enable"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Login Enable') }}"> <span
                                                                class="text-white"> <i class="ti ti-road-sign"></i>
                                                        </a>
                                                    </div>

                                                @endif
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-size="md"
                                                        data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"
                                                        data-ajax-popup="true" data-title="{{ __('Reset Password') }}"
                                                        data-bs-toggle="tooltip"  title="{{ __('Reset Password') }}" data-bs-placement="top">
                                                        <span class="text-white"> <i class="ti ti-key"></i> </span>
                                                    </a>



                                                </div>
                                                @can('edit-users')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route('users.edit', $user->id) }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                            data-bs-toggle="tooltip"  title="{{ __('Edit') }}"> <span
                                                                class="text-white"> <i class="ti ti-edit"></i></span></a>
                                                    </div>
                                                @endcan
                                                @can('delete-users')
                                                    <div class="action-btn bg-danger ms-2">
                                                        <form method="POST" action="{{ route('users.destroy', $user->id) }}"
                                                            id="delete-form-{{ $user->id }}">
                                                            @csrf
                                                            <input name="_method" type="hidden" value="DELETE">
                                                            <button type="submit"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endcan
                                            @else
                                                <div class="text-end me-3">
                                                    <i class="ti ti-lock"></i>
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
    </div>
@endsection
