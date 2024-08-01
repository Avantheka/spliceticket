@component('mail::message')
{{ __('Hello') }}, {{ $user->name }}

{{ __('Ticket Subject') }} : {{$ticket->subject}}

<div>
{!! $conversion->description !!}
</div>

@component('mail::button', ['url' => route('tickets.edit',$ticket->id)])
    {{ __('Open Ticket Now') }}
@endcomponent

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
