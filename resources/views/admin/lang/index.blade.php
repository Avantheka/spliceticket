@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Languages') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Languages') }}</li>
@endsection

@section('multiple-action-button')
    @if ($currantLang != (Utility::getSettingValByName('DEFAULT_LANG') ?? 'en'))
        <div class="action-btn btn btn-sm btn-danger btn-icon m-1 float-end ms-2">
            <form method="POST" action="{{ route('lang.destroy', $currantLang) }}" id="delete-form-{{ $currantLang }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                    data-toggle="tooltip" title='Delete'>
                    <span class="text-white"> <i class="ti ti-trash"></i></span>
                </button>
            </form>
        </div>
    @endif

    @if ($currantLang != (!empty($settings['default_language']) ? $settings['default_language'] : 'en'))
        <div class="form-check form-switch custom-switch-v1 float-end" style="
    padding-top: 7px;">
            <input type="hidden" name="disable_lang" value="off">
            <input type="checkbox" class="form-check-input input-primary" name="disable_lang" data-bs-placement="top"
                title="{{ __('Enable/Disable') }}" id="disable_lang" data-bs-toggle="tooltip"
                {{ !in_array($currantLang, $disabledLang) ? 'checked' : '' }}>
            <label class="form-check-label" for="disable_lang"></label>
        </div>
    @endif
@endsection

@section('content')
    <div class="section-body">
        <div class="row">

            <div class="col-lg-2">
                <div class="card sticky-top" style="top:30px">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        @foreach ($languages as $code => $lang)
                            <a href="{{ route('lang.index', [$code]) }}"
                                class="list-group-item list-group-item-action border-0 @if ($currantLang == $code) active @endif">{{ ucFirst($lang) }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-10">
                <div class="card  p-3">
                    <div class="card-body">
                        <ul class="nav nav-pills nav-fill my-4 lang-tab">
                            <li class="nav-item">
                                <a data-href="#labels" class="nav-link active">{{ __('Labels') }}</a>
                            </li>
                            <li class="nav-item">
                                <a data-toggle="tab" data-href="#messages" class="nav-link">{{ __('Messages') }} </a>
                            </li>
                        </ul>
                        <form method="post" action="{{ route('lang.store.data', [$currantLang]) }}">
                            @csrf
                            <div class="tab-content">
                                <div class="tab-pane active" id="labels">
                                    <div class="row">
                                        @foreach ($arrLabel as $label => $value)
                                            <div class="col-lg-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label text-dark">{{ $label }}</label>
                                                    <input type="text" class="form-control"
                                                        name="label[{{ $label }}]" value="{{ $value }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="tab-pane" id="messages">
                                    @foreach ($arrMessage as $fileName => $fileValue)
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <h6>{{ ucfirst($fileName) }}</h6>
                                            </div>
                                            @foreach ($fileValue as $label => $value)
                                                @if (is_array($value))
                                                    @foreach ($value as $label2 => $value2)
                                                        @if (is_array($value2))
                                                            @foreach ($value2 as $label3 => $value3)
                                                                @if (is_array($value3))
                                                                    @foreach ($value3 as $label4 => $value4)
                                                                        @if (is_array($value4))
                                                                            @foreach ($value4 as $label5 => $value5)
                                                                                <div class="col-lg-6">
                                                                                    <div class="form-group mb-3">
                                                                                        <label
                                                                                            style="overflow-wrap: break-word;width: 100%;"
                                                                                            class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}.{{ $label3 }}.{{ $label4 }}.{{ $label5 }}</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}][{{ $label3 }}][{{ $label4 }}][{{ $label5 }}]"
                                                                                            value="{{ $value5 }}">
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <div class="col-lg-6">
                                                                                <div class="form-group mb-3">
                                                                                    <label
                                                                                        style="overflow-wrap: break-word;width: 100%;"
                                                                                        class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}.{{ $label3 }}.{{ $label4 }}</label>
                                                                                    <input type="text"
                                                                                        class="form-control"
                                                                                        name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}][{{ $label3 }}][{{ $label4 }}]"
                                                                                        value="{{ $value4 }}">
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                @else
                                                                    <div class="col-lg-6">
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                style="overflow-wrap: break-word;width: 100%;"
                                                                                class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}.{{ $label3 }}</label>
                                                                            <input type="text" class="form-control"
                                                                                name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}][{{ $label3 }}]"
                                                                                value="{{ $value3 }}">
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <div class="col-lg-6">
                                                                <div class="form-group mb-3">
                                                                    <label style="overflow-wrap: break-word;width: 100%;"
                                                                        class="form-label text-dark">{{ $fileName }}.{{ $label }}.{{ $label2 }}</label>
                                                                    <input type="text" class="form-control"
                                                                        name="message[{{ $fileName }}][{{ $label }}][{{ $label2 }}]"
                                                                        value="{{ $value2 }}">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div class="col-lg-6">
                                                        <div class="form-group mb-3">
                                                            <label style="overflow-wrap: break-word;width: 100%;"
                                                                class="form-label text-dark">{{ $fileName }}.{{ $label }}</label>
                                                            <input type="text" class="form-control"
                                                                name="message[{{ $fileName }}][{{ $label }}]"
                                                                value="{{ $value }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="text-end">
                                <input type="submit" value="{{ __('Save Changes') }}" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div> <!-- end card-->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.lang-tab .nav-link', function() {
            $('.lang-tab .nav-link').removeClass('active');
            $('.tab-pane').removeClass('active');
            $(this).addClass('active');
            var id = $('.lang-tab .nav-link.active').attr('data-href');
            $(id).addClass('active');
        });
    </script>
    <script>
        $(document).on('change', '#disable_lang', function() {
            var val = $(this).prop("checked");
            if (val == true) {
                var langMode = 'on';
            } else {

                var langMode = 'off';
            }
            $.ajax({
                type: 'POST',
                url: "{{ route('disablelanguage') }}",
                datType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "mode": langMode,
                    "lang": "{{ $currantLang }}"
                },
                success: function(data) {
                    show_toastr('Success', data.message, 'success')
                }
            });
        });
    </script>
@endpush
