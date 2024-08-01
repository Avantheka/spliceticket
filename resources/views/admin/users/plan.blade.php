@foreach($plans as $plan)
    <div class="list-group-item">
        <div class="row align-items-center">
            <div class="col ml-n2">
                <a href="#!" class="d-block h6 mb-0">{{$plan->name}}</a>
                <div>
                    <small>{{env('CURRENCY_SYMBOL').$plan->price}} {{' / '. $plan->duration}}</small>
                </div>
            </div>
            <div class="col ml-n2">
                <a href="#!" class="d-block h6 mb-0">{{__('Agent')}}</a>
                <div>
                    <span class="text-sm">{{$plan->max_agent}}</span>
                </div>
            </div>
            <div class="col-auto">
                @if($user->plan==$plan->id)
                     <span class="btn btn-sm btn-primary my-auto"><i class="ti ti-check "></i></span>

                @else
                <div class="action-btn bg-warning ms-2">
                   <a href="{{route('plan.active',[$user->id,$plan->id])}}" class="btn btn-sm btn-warning my-auto" title="{{__('Click to Upgrade Plan')}}"><i class="ti ti-shopping-cart-plus"></i></a>
                </div>

                @endif
            </div>
        </div>
    </div>
@endforeach

