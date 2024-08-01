

{{Form::model($coupon, array('route' => array('coupon.update', $coupon->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
            <div class="float-end">
                <a class="btn btn-primary btn-sm float-end ms-2" href="#" data-size="md" data-ajax-popup-over="true" data-url="{{ route('generate',['coupon']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Content with AI') }}"><i class="fas fa-robot"> {{ __('Generate with AI') }}</i></a>
            </div>
        <div class="form-group col-md-12">
            {{Form::label('name',__('Name'),['class'=>'col-form-label'])}}
            {{Form::text('name',null,array('class'=>'form-control ','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('discount',__('Discount'),['class'=>'col-form-label'])}}
            {{Form::number('discount',null,array('class'=>'form-control','required'=>'required','min'=>'1','max'=>'100','step'=>'0.01'))}}
            <span class="small">{{__('Note: Discount in Percentage')}}</span>
        </div>
        <div class="form-group col-md-6">
            {{Form::label('limit',__('Limit'),['class'=>'col-form-label'])}}
            {{Form::number('limit',null,array('class'=>'form-control','min'=>'1','required'=>'required'))}}
        </div>
        <div class="form-group col-md-12" id="auto">
            {{Form::label('code',__('Code') ,array('class'=>'col-form-label'))}}
            <div class="input-group">
                {{Form::text('code',null,array('class'=>'form-control','id'=>'auto-code','required'=>'required'))}}
                <button class="btn btn-outline-secondary" type="button" id="code-generate"><i class="fa fa-history pr-1"></i>{{__(' Generate')}}</button>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary ms-2">
</div>

{{ Form::close() }}

<script>
    $(document).on('click', '.code', function () {
        var type = $(this).val();
        if (type == 'manual') {
            $('#manual').removeClass('d-none');
            $('#manual').addClass('d-block');
            $('#auto').removeClass('d-block');
            $('#auto').addClass('d-none');
        } else {
            $('#auto').removeClass('d-none');
            $('#auto').addClass('d-block');
            $('#manual').removeClass('d-block');
            $('#manual').addClass('d-none');
        }
    });

    $(document).on('click', '#code-generate', function () {
        var length = 10;
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        $('#auto-code').val(result);
    });
</script>
