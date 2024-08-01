@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Category') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Category') }}</li>
@endsection
@php
    $logos = \App\Models\Utility::get_file('public/');
@endphp
@section('multiple-action-button')
    @can('create-category')
            <div class="float-end">
                    <div class="col-auto=">
                        <a href="#"  class="btn btn-sm btn-primary btn-icon" title="{{__('Create')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-ajax-popup="true" data-title="{{__('Create Category')}}"
                        data-url="{{route('category.create')}}"><i class="ti ti-plus"></i></a>
                    </div>
            </div>
    @endcan

@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{{ __('Name') }}</th>
                                <th scope="col">{{ __('Color') }}</th>
                                <th scope="col">{{ __('User') }}</th>
                                <th scope="col" class="text-end me-3">{{ __('Action') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                                @foreach($categories as $index => $category)
                                    <tr>
                                        <th scope="row">{{++$index}}</th>
                                        <td>{{$category->name}}</td>
                                        <td><span class="badge" style="background: {{$category->color}}">&nbsp;&nbsp;&nbsp;</span></td>
                                       <td>

                                           @foreach ($category->users as $categories)
                                           <a href="{{ !empty($user->avatar) ? $logos . $user->avatar : $logos . 'avatar.png' }}"
                                                target="_blank">
                                                <img src="{{ !empty($categories->avatar) ? $logos . $categories->avatar : $logos . 'avatar.png' }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="{{$categories->name}}"  class="img-fluid rounded-circle card-avatar" width="30"
                                                    id="blah3">
                                          </a>

                                            @endforeach
                                        </td>

                                        <td class="text-end">
                                            @can('edit-category')

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-url="{{ route('category.edit', $category->id) }}" data-ajax-popup="true" data-title="{{__('Edit Category')}}" data-size="md"><span class="text-white"><i class="ti ti-edit"></i></span></a>

                                                </div>
                                            @endcan
                                            @can('delete-category')
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{route('category.destroy',$category->id) }}" id="user-form-{{$category->id}}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip"
                                                        title="{{ __('Delete') }}">
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


