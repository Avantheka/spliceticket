{{Form::model($opeatings, array('route' => array('operating_hours.update', $opeatings->id), 'method' => 'PUT')) }}


    <div class="modal-body">
        <div class="row">
            <div class="row">
                <div class="col-md-4">
                    <div class="from-group">
                        <label class="col-form-label"for="name">{{ __('Operating Hour Name') }}</label>
                        <span class="red" style="color: red;">*</span>
                        <input type="text" name="name" id="name"
                            value="{{ $opeatings->name }}"class="form-control" required />
                    </div>
                </div>
                @php
                    $contents = json_decode($opeatings->content, true);
                @endphp

                <div class="col-md-8">
                    <div class="accordion-body">
                       @foreach ($days as $key => $day)
                        <div class="row align-items-center gy-4">
                            <div class="col-xs-12 col-sm-3">
    
                                <div class="form-check">
                                    <!-- Check if the current day exists in the content array -->
                                    <input class="form-check-input" type="checkbox" value="{{ $day }}" name="days[{{ $day }}]" id="content" {{ isset($contents[$day]) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="flexCheckDefault">
                                        {{ $day }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-8 col-sm-4" style="
                            padding-bottom: 11px;">
                                <div class="form-group mb-0">
                                    <input type="time" id="days_{{ $day }}_start" data-id="days_{{ $day }}_start" name="content[{{ $day }}][start_time]"    value="{{ isset($contents[$day]['start_time']) ? $contents[$day]['start_time'] : '' }}" class="form-control timepicker" placeholder="08:10" value="" >
                                </div>
                            </div>
                            <div class="col-xs-8 col-sm-4" style="
                            padding-bottom: 11px;">
                                <div class="form-group mb-0">
                                    <input type="time" id="days_{{ $day }}_end" data-id="days_{{ $day }}_end" name="content[{{ $day }}][end_time]"  value= "{{ isset($contents[$day]['end_time']) ? $contents[$day]['end_time'] : '' }}" class="form-control timepicker" placeholder="08:10" value="" >
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <hr class="my-3">
        </div>

        <div class="row">
            <div class="d-flex justify-content-end text-end">
                <a class="btn btn-secondary btn-light btn-submit" href="">{{ __('Cancel') }}</a>
                <button class="btn btn-primary btn-submit ms-2" type="submit">{{ __('Update') }}</button>
            </div>
        </div>
    </div>
{{ Form::close() }}
