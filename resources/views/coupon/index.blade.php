@extends('layouts.admin')

    @section('page-title')
        {{__('Manage Coupons')}}
    @endsection
    @section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Home')}}</a></li>    
        <li class="breadcrumb-item active" aria-current="page">{{__('Coupons')}}</li>
    @endsection
    @section('action-button')
        <div>
            <div class="row">
                    <div class="col-auto=">
                        <a href="#"  class="btn btn-sm btn-primary btn-icon" title="{{__('Create')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-ajax-popup="true" data-title="{{__('Create New Coupon')}}" data-url="{{route('coupon.create')}}"><i class="ti ti-plus"></i></a>
                    </div>   
            </div>
        </div>
    @endsection

    @section('content')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table id="pc-dt-simple" class="table dataTable">
                                <thead>
                                <tr>
                                    <th> {{__('Name')}}</th>
                                    <th> {{__('Code')}}</th>
                                    <th> {{__('Discount (%)')}}</th>
                                    <th> {{__('Limit')}}</th>
                                    <th> {{__('Used')}}</th>
                                    <th> {{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr class="">
                                        <td>{{ $coupon->name }}</td>
                                        <td>{{ $coupon->code }}</td>
                                        <td>{{ $coupon->discount }}</td>
                                        <td>{{ $coupon->limit }}</td>
                                        <td>{{ $coupon->used_coupon() }}</td>
                                        <td>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('coupon.show',$coupon->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('Detail')}}" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-white"><i class="ti ti-eye"></i></span></a>
                                            </div>
                                            
                                                
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" title="{{__('Edit')}}" data-bs-toggle="tooltip" data-bs-placement="top" data-url="{{ route('coupon.edit',$coupon->id) }}" data-ajax-popup="true" data-title="{{__('Edit Coupon')}}" data-size="md"><span class="text-white"><i class="ti ti-edit"></i></span></a>
                                                </div>
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['coupon.destroy', $coupon->id]]) !!}
                                                        <a href="#!" class="mx-3 btn btn-sm  align-items-center text-white show_confirm" data-bs-toggle="tooltip" title='Delete'>
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                    {!! Form::close() !!}
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
