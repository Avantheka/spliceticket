@component('mail::message')
{{ __('Hello') }}, {{ $ticket->name }}

{{ __('Ticket Subject') }} : {{$ticket->subject}}

<div>
{!! $conversion->description !!}
</div>

@component('mail::button', ['url' => route('home.view',\Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id))])
    {{ __('Check Your Ticket Now') }}
@endcomponent

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
