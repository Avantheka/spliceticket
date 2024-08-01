@extends('layouts.admin')
@php
    $admin_payment_setting = Utility::payment_settings();
@endphp
@push('scripts')
@endpush
@section('page-title')
    {{ __('Order') }}
@endsection
@section('title')
    {{ __('Order') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Order') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive overflow_hidden">
                            <table id="pc-dt-simple" class="table datatable align-items-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" class="sort" data-sort="name"> {{ __('Order Id') }}</th>
                                        <th scope="col" class="sort" data-sort="budget">{{ __('Date') }}</th>
                                        <th scope="col" class="sort" data-sort="status">{{ __('Name') }}</th>
                                        <th scope="col">{{ __('Plan Name') }}</th>
                                        <th scope="col" class="sort" data-sort="completion"> {{ __('Price') }}</th>
                                        <th scope="col" class="sort" data-sort="completion"> {{ __('Payment Type') }}
                                        </th>
                                        <th scope="col" class="sort" data-sort="completion"> {{ __('Status') }}</th>
                                        <th scope="col" class="sort" data-sort="completion"> {{ __('Coupon') }}</th>
                                        <th scope="col" class="sort text-center" data-sort="completion">
                                            {{ __('Invoice') }}</th>
                                        @if (\Auth::user()->type == 'Super Admin')
                                            <th>{{ __('Action') }}</th>
                                        @endif

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $path = \App\Models\Utility::get_file('uploads/order');

                                    @endphp
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>
                                                {{ $order->order_id }}
                                            </td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>{{ $order->user_name }}</td>
                                            <td>{{ $order->plan_name }}</td>
                                            <td>{{ isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $order->price }}
                                            </td>

                                            <td>{{ $order->payment_type }}</td>

                                            <td>
                                                @if ($order->payment_status == 'succeeded')
                                                    <span class="d-flex align-items-center">
                                                        <span class="ms-1">{{ ucfirst($order->payment_status) }}</span>
                                                    </span>
                                                @else
                                                    <span class="d-flex align-items-center">
                                                        <span class="ms-1">{{ ucfirst($order->payment_status) }}</span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ !empty($order->total_coupon_used) ? (!empty($order->total_coupon_used->coupon_detail) ? $order->total_coupon_used->coupon_detail->code : '-') : '-' }}
                                            </td>
                                            <td class="text-center">
                                                @if ($order->receipt != 'free coupon' && $order->payment_type == 'STRIPE')
                                                    <a href="{{ $order->receipt }}" title="Invoice" target="_blank"
                                                        class=""><i class="fas fa-file-invoice"></i> </a>
                                                @elseif($order->receipt == 'free coupon')
                                                    <p>{{ __('Used 100 % discount coupon code.') }}</p>
                                                @elseif($order->payment_type == 'Manually')
                                                    <p>{{ __('Manually plan upgraded by super admin') }}</p>
                                                @elseif(!empty($order->receipt) && $order->payment_type == 'Bank Transfer')
                                                    <a href="{{ $path . '/' . $order->receipt }}" target="_blank">
                                                        <i class="ti ti-file-invoice"></i> {{ __('Receipt') }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            @if (\Auth::user()->type == 'Super Admin')
                                                <td class="Action">
                                                    @if ($order->payment_status == 'Pending' && $order->payment_type == 'Bank Transfer')
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a href="#"
                                                                data-url="{{ URL::to('order/' . $order->id . '/action') }}"
                                                                data-size="lg" data-ajax-popup="true"
                                                                data-title="{{ __('Payment Status') }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" title="{{ __('Payment Status') }}"
                                                                data-original-title="{{ __('Payment Status') }}">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif

                                                    @php
                                                        $user = App\Models\User::find($order->user_id);
                                                    @endphp
                                                    <span>
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['bank_transfer.destroy', $order->id]]) !!}
                                                            <a href="#!"
                                                                class="mx-3 btn btn-sm align-items-center show_confirm ">
                                                                <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Delete') }}"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                        @foreach ($userOrders as $userOrder)
                                                            @if ($user->plan == $order->plan_id && $order->order_id == $userOrder->order_id && $order->is_refund == 0)
                                                                <div class="badge bg-warning rounded p-2 px-3 ms-2">
                                                                    <a href="{{ route('order.refund', [$order->id, $order->user_id]) }}"
                                                                        class="mx-3 align-items-center"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Delete') }}"
                                                                        data-original-title="{{ __('Delete') }}">
                                                                        <span
                                                                            class ="text-white">{{ __('Refund') }}</span>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </span>
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
        </div>
    </div>
@endsection
