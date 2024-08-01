
<form method="post" action="{{ route('groups.update', $groups->id) }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-6 form-group">
            <label class="col-form-label" for="name">{{ __('Group Name') }}</label>
            <input type="text" class="form-control" id="name" value="{{ $groups->name }}" name="name" required />
        </div>

        <div class="col-6 form-group">
            <label class="require form-label">{{ __('Description') }}</label>
            <textarea name="description" class="form-control ckdescription" value="{{ $groups->description }}"></textarea>
        </div>
        <div class="col-6 form-group">
            {{ Form::label('operating_hours', __('Operating Hours'),['class'=>'col-form-label']) }}
            {{ Form::select('operating_hours', $opeatings,null, array('class' => 'form-control select2','required'=>'required')) }}

        </div>
        <div class="col-6 form-group">
            {{ Form::label('users', __('Agent'), ['class' => 'col-form-label']) }}

            {{ Form::select('users[]', $users, explode(',', $groups->users), ['class' => 'form-control multi-select ', 'id' => 'choices-multiple1', 'multiple' => '']) }}

        </div>
        <div class="col-6 form-group">
            {{ Form::label('leader', __('Group leader'), ['class' => 'col-form-label']) }}
            {{ Form::select('leader[]', $leader, explode(',', $groups->leader), ['class' => 'form-control multi-select ', 'id' => 'choices-multiple2', 'multiple' => '']) }}
        </div>
        <div class="form-group col-md-6">
            <label class="col-form-label" for="email">{{ __('E-Mail Address') }}</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $groups->email }}" />
        </div>
    </div>

    <div class="row">
        <div class="d-flex justify-content-end text-end">
            <a class="btn btn-secondary btn-light btn-submit" href="{{ route('group') }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary btn-submit ms-2" type="submit">{{ __('Add') }}</button>
        </div>
    </div>
</form>
