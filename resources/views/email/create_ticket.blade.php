@component('mail::message')
{{__('Hello')}}, {{ $ticket->name }}

{{ __('A request for support has been created and assigned') }} #{{$ticket->ticket_id}}. {{ __('A representative will follow-up with you as soon as possible. You can  view this ticket progress online.') }}

@component('mail::button', ['url' => route('home.view',\Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id))])
 {{ __('Check Your Ticket Now') }}
@endcomponent

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
