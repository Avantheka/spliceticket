<ul>
    @if(count($messages) > 0)
        @foreach($messages as $message)
            @if($message->from != 0)
                <li class="left">
                    {{ $message->message }}
                </li>
            @else
                <li class="right">
                    {{ $message->message }}
                </li>
            @endif
        @endforeach
    @else
        <li>No Message Found..!</li>
    @endif
</ul>
