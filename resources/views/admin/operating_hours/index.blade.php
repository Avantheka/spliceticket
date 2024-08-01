@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Opearating Hours') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Opearating Hours') }}</li>
@endsection
@php
    $logos = \App\Models\Utility::get_file('public/');
@endphp
@section('action-button')
        <div class="float-end">
            <div class="col-auto=">
                <a href="#"  class="btn btn-sm btn-primary btn-icon" title="{{__('Create')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-ajax-popup="true" data-title="{{__('Create Operating Hour')}}"
                data-url="{{ route('operating_hours.create') }}" data-size="xl"><i class="ti ti-plus"></i></a>
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
                                      <th>#</th>
                                      <th scope="col">{{ __('Name') }}</th>

                                    <th scope="col" class="text-end me-3">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($opeatings as $index => $opeating)
                                 <tr>
                                        <th scope="row">{{++$index}}</th>
                                        <td>{{ $opeating->name }}</td>
                                        <td class="text-end">

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('View')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-url="{{route('operating_hours.show',$opeating->id)}}" data-ajax-popup="true" data-title="{{ $opeating-> name }}" data-size="md"><span class="text-white"><i class="ti ti-eye"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-url="{{route('operating_hours.edit',$opeating->id) }}" data-ajax-popup="true" data-title="{{__('Edit Operating Hour')}}" data-size="xl"><span class="text-white"><i class="ti ti-edit"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="{{route('operating_hours.destroy',$opeating->id) }}" id="">
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

