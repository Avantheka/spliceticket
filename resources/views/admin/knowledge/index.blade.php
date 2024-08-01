@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Knowledge') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Knowledge') }}</li>
@endsection

@section('multiple-action-button')
    @can('create-knowledge')
        <div class="btn btn-sm btn-primary btn-icon float-end ms-2" data-bs-toggle="tooltip"
        data-bs-placement="top" title="{{ __('Create Knowledge') }}">
            <a href="{{ route('knowledge.create') }}" class="" ><i class="ti ti-plus text-white"></i></a>
        </div>
    @endcan

    @can('manage-knowledgecategory')
        <div class="btn btn-sm btn-primary btn-icon float-end" data-bs-toggle="tooltip"
        data-bs-placement="top" title="{{ __('Knowledge Category') }}">
            <a href="{{ route('knowledgecategory') }}" class="" ><i
                    class="ti ti-vector-bezier text-white"></i></a>
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
                                    <th class="w-25">{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($knowledges as $index => $knowledge)
                                    <tr>
                                        <th scope="row">{{ ++$index }}</th>
                                        <td><span class="font-weight-bold white-space">{{ $knowledge->title }}</span>
                                        </td>
                                        <td class="knowledge_desc space_desc">{!! $knowledge->description !!}</td>
                                        <td>
                                            <span class="font-weight-normal">
                                                {{ !empty($knowledge->getCategoryInfo) ? $knowledge->getCategoryInfo->title : '-' }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            @can('edit-knowledge')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('knowledge.edit', $knowledge->id) }}"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                       data-bs-toggle="tooltip" title="{{ __('Edit') }}"><span
                                                            class="text-white"> <i class="ti ti-edit"></i></span></a>
                                                </div>
                                            @endcan
                                            @can('delete-knowledge')
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST"
                                                        action="{{ route('knowledge.destroy', $knowledge->id) }}"
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
