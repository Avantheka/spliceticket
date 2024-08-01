@extends('layouts.admin')

@section('page-title')
    {{ __('Manage KnowledgeBase Category') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('knowledge') }}">{{ __('Knowledge') }}</a></li>
    <li class="breadcrumb-item">{{ __('Category') }}</li>
@endsection

@section('multiple-action-button')
    @can('create-knowledgecategory')
        <div class="btn btn-sm btn-primary btn-icon float-end" data-bs-toggle="tooltip"
        data-bs-placement="top" title="{{ __('Create Knowledgebase Category') }}">
            <a href="{{ route('knowledgecategory.create') }}" class="" ><i
                    class="ti ti-plus text-white"></i></a>
        </div>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th class="w-50">{{ __('Title') }}</th>
                                    <th class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($knowledges_category as $index => $knowledge)
                                    <tr>
                                        <th scope="row">{{ ++$index }}</th>
                                        <td><span class="font-weight-bold white-space">{{ $knowledge->title }}</span></td>
                                        <td class="text-end">
                                            @can('edit-knowledgecategory')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('knowledgecategory.edit', $knowledge->id) }}"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        data-bs-toggle="tooltip" title="{{ __('Edit') }}"> <span
                                                            class="text-white"> <i class="ti ti-edit"></i></span></a>
                                                </div>
                                            @endcan
                                            @can('delete-knowledgecategory')
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST"
                                                        action="{{ route('knowledgecategory.destroy', $knowledge->id) }}"
                                                        id="user-form-{{ $knowledge->id }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        <button type="submit"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                            data-bs-toggle="tooltip" title="{{ __('Delete') }}">
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
