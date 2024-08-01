@extends('layouts.admin')
@section('page-title')
    {{__('Plan Request')}}
@endsection
@section('title')
       {{__('Plan Request')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Home')}}</a></li>
    <li class="breadcrumb-item">{{__('Plan Request')}}</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive overflow_hidden">
                            <table id="pc-dt-simple" class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th> {{__('User Name')}}</th>
                                        <th> {{__('Plan Name')}}</th>
                                        <th> {{__('Max Agent')}}</th>
                                        <th> {{__('Duration')}}</th>
                                        <th> {{__('Date')}}</th>
                                        <th> {{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($plan_requests->count() > 0)
                                        @foreach($plan_requests as $prequest)

                                            <tr>
                                                <td>
                                                    <div class="font-style font-weight-bold">{{ $prequest->user->name }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-style font-weight-bold">{{ $prequest->plan->name }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $prequest->plan->max_agent < 0 ? __('Unlimited') : $prequest->plan->max_agent }}</div>


                                                </td>
                                                <td>
                                                    <div class="font-style font-weight-bold">{{ $prequest->plan->duration }}</div>

                                                    {{-- <div class="font-style font-weight-bold">{{ ($prequest->duration == 'monthly') ? __('One Month') : __('One Year') }}</div> --}}
                                                </td>

                                                <td>{{ \App\Models\Utility::getDateFormated($prequest->created_at,true) }}</td>
                                                <td>
                                                    <div>
                                                        <a href="{{route('response.request',[$prequest->id,1])}}" title="{{__('Accept')}}" data-bs-toggle="tooltip" class="action-btn bg-success">
                                                            <i class="ti ti-check"></i>
                                                        </a>
                                                        <a href="{{route('response.request',[$prequest->id,0])}}" title="{{__('Delete')}}" data-bs-toggle="tooltip" class="action-btn bg-danger">
                                                            <i class="ti ti-x"></i>
                                                        </a>
                                                    </div>

                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <th scope="col" colspan="7"><h6 class="text-center">{{__('No Manually Plan Request Found.')}}</h6></th>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
