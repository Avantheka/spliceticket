<form class="pl-3 pr-3 mt-2 animated" method="post" action="{{ route('lang.store') }}">
    @csrf
    <div class="form-group p-2">
        <label for="code" class="form-label">{{ __('Language Code') }}</label>
        <input class="form-control" type="text" id="code" name="code" required="" placeholder="{{ __('Language Code') }}">
    </div>
    <div class="form-group p-2">
        <label for="code" class="form-label">{{ __('Language Full Name') }}</label>
        <input class="form-control" type="text" id="fullName" name="fullName" required="" placeholder="{{ __('Language fullName') }}">
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>

</form>
