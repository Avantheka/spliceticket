@php
$plansettings = \App\Models\Utility::plansettings();

@endphp
{{ Form::open(array('url' => 'category','method' =>'post')) }}

    @csrf
    <div class="row">
        @if($plansettings["enable_chatgpt"] == 'on')

        <div class="float-end" style="margin-bottom: 15px">
            <a class="btn btn-primary btn-sm" href="#" data-size="md" data-ajax-popup-over="true" data-url="{{ route('generate',['category']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Content with AI') }}"><i class="fas fa-robot"> {{ __('Generate with AI') }}</i></a>
        </div>
        @endif
        <div class="form-group col-md-6">
            <label class="form-label">{{ __('Name') }}</label>
            <div class="col-sm-12 col-md-12">
                <input type="text" placeholder="{{ __('Name of the Category') }}" name="name"
                    class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}"
                    required>
                <div class="invalid-feedback">
                    {{ $errors->first('name') }}
                </div>
            </div>
        </div>
        <div class="form-group col-md-6">

            <label for="exampleColorInput" class="form-label">{{ __('Color') }}</label>
            <div class="col-sm-12 col-md-12">
                <input name="color" type="color"
                    class=" form-control  form-control-color {{ $errors->has('color') ? ' is-invalid' : '' }}"
                    value="255ff7" id="exampleColorInput">
                <div class="invalid-feedback">
                    {{ $errors->first('color') }}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('users', __('User'), ['class' => 'col-form-label']) }}
            {{ Form::select('users[]', $users, null, ['class' => 'form-control multi-select ', 'id' => 'choices-multiple', 'multiple' => '']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <label class="form-label"></label>
            <div class="col-sm-12 col-md-12 text-end">
                <button class="btn btn-primary btn-block btn-submit"><span>{{ __('Add') }}</span></button>
            </div>
        </div>
    </div>
{{ Form::close() }}

