@component('mail::message')
{{__('Hello')}}, {{ $ticket->name }}<br>

{{ __('A request for support has been closed') }} #{{$ticket->ticket_id}}<br><br>

<b style="font-size:15px">{{$ticket->name}}</b>&nbsp;&nbsp;<small>{{$ticket->created_at->diffForHumans()}}</small>
<p>{!! $ticket->description !!}</p>

@foreach($ticket->conversions as $conversion)
<b style="font-size:15px">{{$conversion->replyBy()->name}}</b>&nbsp;&nbsp;<small>{{$conversion->created_at->diffForHumans()}}</small>
<p>{!! $conversion->description !!}</p>
@endforeach

<br>
<b style="font-size: 15px">Notes</b>
<p>{!! $ticket->note !!}</p>
<br>



{{__('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
