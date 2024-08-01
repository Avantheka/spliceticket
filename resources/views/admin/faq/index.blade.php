@extends('layouts.admin')

@section('page-title')
    {{ __('Manage FAQ') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('FAQ') }}</li>
@endsection


@section('action-button')
    @can('create-faq')
        <div class="btn btn-sm btn-primary btn-icon m-1 float-end"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create FAQ') }}">
            <a href="{{route('faq.create')}}" class=""><i class="ti ti-plus text-white"></i></a>
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
                                    <th class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($faqs as $index => $faq)
                                    <tr>
                                        <th scope="row">{{++$index}}</th>
                                        <td><span class="font-weight-bold white-space">{{$faq->title}}</span></td>
                                        <td class="faq_desc">{!! $faq->description !!}</td>
                                        <td class="text-end">
                                            @can('edit-faq')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('faq.edit',$faq->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"><span class="text-white"> <i class="ti ti-edit"></i></span></a>
                                                </div>
                                            @endcan
                                            @can('delete-faq')
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{route('faq.destroy',$faq->id) }}" id="user-form-{{$faq->id}}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip"
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
