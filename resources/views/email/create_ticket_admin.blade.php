@component('mail::message')
{{__('Hello')}}, {{ $user->name }}

{{ __('A request for support has been created and assigned') }} #{{$ticket->ticket_id}}. {{ __('Please follow-up as soon as possible.') }}

@component('mail::button', ['url' => route('tickets.edit',$ticket->id)])
{{ __('Open Ticket Now') }}
@endcomponent

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent



