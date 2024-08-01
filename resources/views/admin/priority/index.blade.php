@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Opearating Hours') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Priority') }}</li>
@endsection
@section('action-button')
    <div class="float-end">
        <div class="col-auto=">
            <a href="#" class="btn btn-sm btn-primary btn-icon" title="{{ __('Create') }}" data-bs-toggle="tooltip"
                data-bs-placement="top" data-ajax-popup="true" data-title="{{ __('Create Priority') }}"
                data-url="{{ route('priority.create') }}" data-size="md"><i class="ti ti-plus"></i></a>
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
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col">{{ __('Color') }}</th>
                                    <th scope="col" class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($priority as $index => $priorities)
                                    <tr>
                                        <th scope="row">{{ ++$index }}</th>
                                        <td>{{ $priorities->name }}</td>
                                        <td><span class="badge"
                                                style="background: {{ $priorities->color }}">&nbsp;&nbsp;&nbsp;</span></td>

                                        <td class="text-end">
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        title="{{ __('Edit') }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        data-url="{{ route('priority.edit', $priorities->id) }}"
                                                        data-ajax-popup="true" data-title="{{ __('Edit Category') }}"
                                                        data-size="md"><span class="text-white"><i
                                                                class="ti ti-edit"></i></span></a>

                                                </div>

                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{route('priority.destroy',$priorities->id) }}" id="">
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
