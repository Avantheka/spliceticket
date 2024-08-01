@extends('layouts.admin')
@section('page-title')
    {{ __('Custom Domain Request') }}
@endsection
@section('title')
    {{ __('Custom Domain Request') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Custom Domain Request') }}</li>
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
                                        <th> {{ __('Company Name') }}</th>
                                        <th> {{ __('Custom Domain') }}</th>
                                        <th> {{ __('Status') }}</th>
                                        <th> {{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($custom_domain_requests as $custom_domain_request)
                                        <tr>
                                            <td>
                                                <div class="font-style font-weight-bold">
                                                    {{ $custom_domain_request->user->name }}</div>
                                            </td>
                                            <td>
                                                <div class="font-style font-weight-bold">
                                                    {{ $custom_domain_request->custom_domain }}</div>
                                            </td>
                                            <td>
                                                @if ($custom_domain_request->status == 0)
                                                    <span
                                                        class="badge fix_badges bg-danger p-2 px-3 rounded">{{ __(App\Models\CustomDomainRequest::$statues[$custom_domain_request->status]) }}</span>
                                                @elseif($custom_domain_request->status == 1)
                                                    <span
                                                        class="badge fix_badges bg-primary p-2 px-3 rounded">{{ __(App\Models\CustomDomainRequest::$statues[$custom_domain_request->status]) }}</span>
                                                @elseif($custom_domain_request->status == 2)
                                                    <span
                                                        class="badge fix_badges bg-warning p-2 px-3 rounded">{{ __(App\Models\CustomDomainRequest::$statues[$custom_domain_request->status]) }}</span>
                                                @endif
                                            </td>
                                            <td class="Action">
                                                @if($custom_domain_request->status == 0)
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="{{route('custom_domain_request.request',[$custom_domain_request->id,1])}}"
                                                        title="{{__('Accept')}}" data-bs-toggle="tooltip">
                                                       <span> <i class="ti ti-check btn btn-sm text-white"></i></span>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{route('custom_domain_request.request',[$custom_domain_request->id,0])}}"
                                                        title="{{__('Reject')}}" data-bs-toggle="tooltip">
                                                       <span> <i class="ti ti-x btn btn-sm text-white"></i></span>
                                                    </a>
                                                </div>
                                                @endif
                                                <div class="action-btn bg-danger ms-2">
                                                    <form method="POST" action="{{route('custom_domain_request.destroy',$custom_domain_request->id) }}" id="user-form-{{$custom_domain_request->id}}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip"
                                                        title="{{ __('Delete') }}" data-bs-toggle="tooltip">
                                                            <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
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
