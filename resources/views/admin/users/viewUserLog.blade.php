@php
    $data = json_decode($userlog->details);
@endphp

<div class="row">
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Status')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->status}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Country')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->country}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Country Code')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->countryCode}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Region')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->region}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Region Name')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->regionName}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('City')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->city}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Pincode')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->zip}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Lat')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->lat}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Lon')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->lon}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Timezone')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->timezone}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Isp')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->isp}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('org')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->org}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('As')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->as}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('IP')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->query}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Browser')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->browser_name}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('OS')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->os_name}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Browser Name')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->browser_language}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Pincode')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->device_type}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Referrer Host')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->referrer_host}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Referrer Path')}}</b></div>
        <p class="text-muted mb-4">
            {{$data->referrer_path}}
        </p>
    </div>
</div>
