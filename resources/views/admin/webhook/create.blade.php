{{Form::open(array('url'=>'webhook','method'=>'post','enctype'=>'multipart/form-data'))}}
<div class="form-group">
    {{Form::label('module',__('Module'),['class'=>'form-label']) }}
    {{Form::select('module',$module,null,array('class'=>'form-control','required'=>'required'))}}
</div>
<div class="form-group">
    {{Form::label('url',__('URL'),['class'=>'form-label']) }}
    {{Form::text('url',null,array('class'=>'form-control','placeholder'=>__('Enter Url'),'required'=>'required'))}}
</div>
<div class="form-group">
    {{Form::label('method',__('Method'),['class'=>'form-label']) }}
    {{Form::select('method',$method,null,array('class'=>'form-control','required'=>'required'))}}
</div>
{{--  --}}
    <div class="modal-footer">
        <button type="button" class="btn  btn-light"
            data-bs-dismiss="modal">Close</button>
                {{Form::submit(__('Save'),array('class'=>'btn  btn-primary  '))}}
    </div>
</div>
{{Form::close()}}
