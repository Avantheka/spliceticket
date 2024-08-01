<div class="card sticky-top" style="top:30px">
    <div class="list-group list-group-flush" id="useradd-sidenav">
        <a href="{{route('category')}}" class="list-group-item list-group-item-action border-0 {{ (Request::route()->getName() == 'category' ) ? ' active' : '' }}">{{__('Category')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

        <a href="{{ route('group') }}" class="list-group-item list-group-item-action border-0 {{ (Request::route()->getName() == 'group' ) ? 'active' : '' }}">{{__('Group')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

        <a href="{{ route('operating_hours.index') }}" class="list-group-item list-group-item-action border-0 {{ (Request::route()->getName() == 'operating_hours.index' ) ? 'active' : '' }}">{{__('Operating Hours')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

        <a href="{{ route('priority.index') }}" class="list-group-item list-group-item-action border-0 {{ (Request::route()->getName() == 'priority.index' ) ? 'active' : '' }}">{{__('Priority')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

        <a href="{{ route('policiy.index') }}" class="list-group-item list-group-item-action border-0 {{ (Request::route()->getName() == 'policiy.index' ) ? 'active' : '' }}">{{__('SLA Policy Setting')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

    </div>
</div>

