@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Groups') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Groups') }}</li>
@endsection
@php
    $logos = \App\Models\Utility::get_file('public/');
@endphp
@section('action-button')

        <div class="float-end">
            <div class="col-auto=">
                <a href="#"  class="btn btn-sm btn-primary btn-icon" title="{{__('Create')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-ajax-popup="true" data-title="{{__('Create Group')}}"
                data-url="{{ route('groups.create') }}" data-size="lg"><i class="ti ti-plus"></i></a>
            </div>
        </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Email') }}</th>
                                    <th scope="col">{{ __('Operating Hours') }}</th>
                                    <th scope="col">{{ __('Agents') }}</th>
                                    <th scope="col" class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $index => $group)


                                    <tr>
                                        <th scope="row">{{ ++$index }}</th>
                                        <td>{{ $group->name }}</td>
                                        <td>{{ $group->email }}</td>

                                        <td>{{ isset($group->operating->name) ? $group->operating->name : '' }}</td>
                                        <td>
                                         @php
                                         $users = $group->getUser($group->users);
                                         @endphp
                                             @foreach($users as $user)

                                             <a href="{{ !empty($user->avatar) ? $logos . $user->avatar : $logos . 'avatar.png' }}"
                                                  target="_blank">
                                                  <img src="{{ !empty($user->avatar) ? $logos . $user->avatar : $logos . 'avatar.png' }}"
                                                  data-bs-toggle="tooltip" data-bs-placement="top" title="{{$user->name}}"  class="img-fluid rounded-circle card-avatar" width="30"
                                                      id="blah3">
                                            </a>
                                              @endforeach
                                         </td>
                                         <td class="text-end">

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-url="{{ route('groups.edit', $group->id) }}" data-ajax-popup="true" data-title="{{__('Edit Group')}}" data-size="lg"><span class="text-white"><i class="ti ti-edit"></i></span></a>
                                                </div>





                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{route('groups.destroy',$group->id) }}" id="user-form-{{$group->id}}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}">
                                                            <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                        </button>
                                                    </form>
                                                </div>

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
