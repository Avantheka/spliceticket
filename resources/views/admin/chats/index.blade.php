@extends('layouts.admin')

@section('page-title')
    {{ __('Messenger') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Messenger') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card chat-div-main">
                <div class="card-body chat-div-wrap">

                    <div class="chat-wrap">
                        <div class="chat-user">
                            <div class=chat-head-left>
                                <nav>
                                    <nav class="m-header-right">
                                        <a href="#" class="listView-x"><i class="fas fa-times"></i></a>
                                    </nav>
                                </nav>
                                <input type="text" id="myInput" class="messenger-search" onkeyup="myFunction()"  placeholder="Search." title="Type in a name">

                            </div>
                             @foreach ($users as $user)

                            <div class="chat-persons scrollbar-inner" data-view="users">
                                <ul class="users" id="myUL">
                                        <li class="user_chat" id="{{ $user->id }}">
                                            <div class="peroson">
                                                <img class="avatar-sm rounded-circle mr-3" style="width:40px;"
                                                src="{{ asset(Storage::url('avatar.png')) }}"
                                                alt="{{$user->email}}">
                                                <div class="agent-div w-100">
                                                    @if (!empty($user->name))
                                                        <h6 class="m-0 chat_user">{{ $user->name }}
                                                        </h6>
                                                        <span class="text-muted text-small">{{ $user->email }}</span>
                                                    @else
                                                        <h6 class="m-0 ">

                                                            <span class="chat_users" style="font-size: 14px; line-height: 14px; color: #606679;"> {{ $user->email }} </span>

                                                            <span class="text-small">You</span>
                                                        </h6>

                                                        <span class="chat-msg">
                                                            <span class="lastMessageIndicator">You :</span>test
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($user->unread() > 0)
                                                    <div class="status">
                                                        <span class="badge badge-info pending">{{ $user->unread() }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </li>
                                </ul>
                            </div>
                             @endforeach
                        </div>

                        <div class="chat-screen">
                            <div class="chat-head">
                                <div class="peroson">
                                    <div class="float-left">
                                        <a href="#" class="show-listView"><i class="ti ti-arrow-left"></i></a>
                                        <img class="avatar-sm rounded-circle mr-3" style="width:40px;"
                                        src="{{ asset(Storage::url('avatar.png')) }}"
                                        alt="">
                                        <h6 class="m-0 chat_head">{{ __('Please Select User') }}</h6>
                                    </div>
                                    <div class="float-right">
                                        <a href="#" id="image" class="add-to-favorite favorite"
                                            style="display: inline; color: #606679;"><i id="foo"
                                                class="fa fa-star"></i></a>
                                        <a href="#" class="show-infoSide header-icon" style="color: #6FD943;">
                                            <svg class="svg-inline--fa fa-info-circle fa-w-16" aria-hidden="true"
                                                focusable="false" data-prefix="fas" data-icon="info-circle" role="img"
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                                <path fill="currentColor"
                                                    d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>

                                </div>
                            </div>
                            <div class="chat-body scrollbar-inner message-wrapper">
                                <div id="messages">
                                    <div class="text-center pt-5">
                                        {{ __('No Message Found..!') }}
                                    </div>
                                </div>
                            </div>
                            <div class="chat-footer" style="display: none;">
                                <div class="send-msg-box">
                                    <input type="text" class="form-control send_msg"
                                        placeholder="{{ __('Type Message here ') }}" name="message" /><i
                                        class="ti ti-brand-telegram send_msg_btn"></i>
                                </div>
                            </div>
                        </div>

                        <div class="messenger-infoView">
                            <nav>
                                <a href="#"><i class="fas fa-times"></i></a>
                            </nav>

                            <img class="avatar-sm rounded-circle mr-3" style="width:100px;"
                            src="{{ asset(Storage::url('avatar.png')) }}"
                            alt="">
                            <p class="info-name chat_head">{{!empty($user->email) ? $user->email : 'Messenger'}}</p>

                            @if(!empty($user->email))
                            <div class="messenger-infoView-btns">
                                <a href="#" class="danger delete-conversation delete-user" style="display: inline;"><i
                                    class="ti ti-trash"></i></a>
                                </div>
                                <div class="messenger-infoView-shared" style="display: block;">
                                    <p class="messenger-title">shared photos</p>
                                    <div class="shared-photos-list">
                                        <p class="message-hint"><span>Nothing shared yet</span></p>
                                    </div>
                                </div>
                            </div>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {

            $('.messenger-infoView nav a , .show-infoSide').on('click', function() {
                $('.messenger-infoView').toggle();
            });


            $('.messenger-infoView nav a').on('click', function() {
                $('.show-infoSide').show();
            });


            $('.show-infoSide').on('click', function() {
                $(this).hide();
            });

        });
        $(document).ready(function() {
            $(".listView-x").click(function() {
                $(".chat-user").hide();
            });
            $(".show-listView").click(function() {
                $(".chat-user").show();
            });
        });
    </script>
    <script>
       function myFunction() {
        var input, filter, ul, li, a, i, txtValue;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        ul = document.getElementById("myUL");
        li = ul.getElementsByTagName("li");

        for (i = 0; i < li.length; i++) {
            h6 = li[i].getElementsByTagName("h6")[0];
            txtValue = h6.textContent ||h6.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = "";
            } else {
                li[i].style.display = "none";
            }
        }
       }
    </script>
    <script>
        var receiver_id = '';
        var my_id = 0;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Enable pusher logging - don't include this in production
            Pusher.logToConsole = false;

            // var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {

                var pusher = new Pusher('{{ $setting['PUSHER_APP_KEY'] }}', {
                cluster: '{{ $setting['PUSHER_APP_CLUSTER'] }}',
                // cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                forceTLS: true
                });


            var channel = pusher.subscribe('my-channel');
            channel.bind('my-event', function(data) {
                if (my_id == data.from) {
                    $('#' + data.to).click();
                } else if (my_id == data.to) {
                    if (receiver_id == data.from) {
                        // if receiver is selected, reload the selected user ...
                        $('#' + data.from).click();
                    } else {
                        // if receiver is not seleted, add notification for that user
                        var pending = parseInt($('#' + data.from + ' .peroson').find('.pending').html());
                        if (pending) {
                            $('#' + data.from + ' .peroson').find('.pending').html(pending + 1);
                        } else {
                            $('#' + data.from + ' .peroson').append(
                                ' <span class="badge badge-info pending">1</span>');
                        }
                    }
                }
            });

            

            $('.user_chat').click(function() {
                var name = $(this).find('.chat_users').text();
                $('.user_chat').removeClass('active-person');
                $(this).addClass('active-person');
                $(this).find('.pending').remove();

                receiver_id = $(this).attr('id');

                $.ajax({
                    type: "get",
                    url: "{{ url('/message/') }}" + '/' + receiver_id, // need to create this route
                    data: "",
                    cache: false,
                    dataType: 'json',
                    success: function(data) {
                        $('.delete-user').html(data.deletehtml);
                        $('#messages').html(data.messagehtml);
                        $('.chat_head').text(name);


                        loadConfirm();
                        scrollToBottomFunc();
                    }
                });
            });

            $(document).on('keyup', '.send-msg-box input', function(e) {
                var message = $(this).val();

                // check if enter key is pressed and message is not null also receiver is selected
                if (e.keyCode == 13 && message != '' && receiver_id != '') {

                    $(this).val(''); // while pressed enter text box will be empty

                    var datastr = "&receiver_id=" + receiver_id + "&message=" + message;
                    $.ajax({
                        type: "post",
                        url: "message", // need to create this post route
                        data: datastr,
                        cache: false,
                        success: function(data) {
                            loadConfirm();
                        },
                        error: function(jqXHR, status, err) {},
                        complete: function() {
                            scrollToBottomFunc();
                        }
                    });
                }
            });




            $(document).on('click', '.send_msg_btn', function() {

                var message = $('.send_msg').val();
                // check if enter key is pressed and message is not null also receiver is selected
                $('.send_msg').val(''); // while pressed enter text box will be empty

                var datastr = "&receiver_id=" + receiver_id + "&message=" + message;
                $.ajax({
                    type: "post",
                    url: "message", // need to create this post route
                    data: datastr,
                    cache: false,
                    success: function(data) {
                        loadConfirm();
                    },
                    error: function(jqXHR, status, err) {},
                    complete: function() {
                        scrollToBottomFunc();
                    }
                });

            });
        });

        // make a function to scroll down auto
        function scrollToBottomFunc() {
            $('.message-wrapper').animate({
                scrollTop: $('.message-wrapper').get(0).scrollHeight
            }, 10);
        }

        $(".user_chat").click(function() {
            $(".chat-footer").removeAttr("style")
        });
    </script>
@endpush
