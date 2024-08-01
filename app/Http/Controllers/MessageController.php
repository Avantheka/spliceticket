<?php

namespace App\Http\Controllers;

use App\Models\FloatingChatMessage;
use App\Models\FloatingChatUser;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;
use App\Models\User;


class MessageController extends Controller
{
    public function index()
    {
        if(!empty(\Auth::user()))
        {
         // $setting = Utility::settings();
            $setting = Utility::superAdminsettings();


            if($setting['CHAT_MODULE'] == 'yes')
            {
                $objUser = Auth::user();
                //        $users   = User::where('users.id', '!=', $objUser->id)->get();
                $users = FloatingChatUser::where('is_end', '!=', '1')->where('company_id', \Auth::user()->createId())->orderBy('id', 'DESC')->get();

                return view('admin.chats.index', compact('users','setting'));
            }
            else
            {
                return redirect()->back();
            }
        }else{
            return redirect()->back();

        }
    }

    public function getMessage($user_id)
    {
        $my_id = 0;

        // Make read all unread message
        FloatingChatMessage::where(
            [
                'from' => $user_id,
                'to' => $my_id,
            ]
        )->update(['is_read' => 1]);

        // Get all message from selected user
        $messages = FloatingChatMessage::where(
            function ($query) use ($user_id, $my_id){
                $query->where('from', $user_id)->where('to', $my_id)->where('company_id', \Auth::user()->createId());
            }
        )->oRwhere(
            function ($query) use ($user_id, $my_id){
                $query->where('from', $my_id)->where('to', $user_id)->where('company_id', \Auth::user()->createId());
            }
        )->get();

        $messagehtml = view('admin.chats.message', compact('messages'))->render();

        $deletehtml = '';
        if($messages->count() > 0)
        {
            $url = route("delete.user.message",$user_id);
            $deletehtml = '<div class="action-btn bg-danger ms-2"> '.
                                     ' <form method="POST" action="'. $url .'" id="delete-form-'. $user_id .'"> '.
                                    //  csrf_token() .
                                    '<input type="hidden" name="_token" value="' . csrf_token() . '">'.
                                    '<input type="hidden" name="_method" value="DELETE">'.
                                    ' <input name="_method" type="hidden" value="DELETE"> '.
                                    ' <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip" title="Delete"> '.
                                    '<span class="text-white"> <i class="ti ti-trash"></i></span> '.
                                       '</button>'.
                                       '</form>'.
                                       '</div>';
        }

        $response = [
            'messagehtml' => $messagehtml,
            'deletehtml' => $deletehtml,
        ];

        return json_encode($response);
    }

    public function sendMessage(Request $request)
    {
        $setting      = Utility::settings();
        $supsetting      = Utility::superAdminsettings();


        $from    = 0;
        $to      = $request->receiver_id;
        $message = $request->message;

        $data          = new FloatingChatMessage();
        $data->from    = $from;
        $data->to      = $to;
        $data->message = $message;
        $data->is_read = 0; // message will be unread when sending message
        $data->company_id = \Auth::user()->createId();
        $data->save();

        // pusher
        $options = array(
            'cluster' => $supsetting['PUSHER_APP_CLUSTER'],
            'useTLS' => true,
        );

        $pusher = new Pusher(
            $supsetting['PUSHER_APP_KEY'], $supsetting['PUSHER_APP_SECRET'], $supsetting['PUSHER_APP_ID'], $options
        );

        $data = [
            'from' => $from,
            'to' => $to,
        ]; // sending from and to user id when pressed enter
        $pusher->trigger('my-channel', 'my-event', $data);
    }

    // For Floating Chat
    public function store(Request $request)
    {

        if(!empty($request->email))
        {
            $user = User::where('slug', $request->slug)->first();

            $floatingUser = FloatingChatUser::create(['email' => $request->email,'company_id'=> $user->id]);

            $floatingUser = [
                'email' => $floatingUser->email,
                'id' => $floatingUser->id,
            ];

            return $floatingUser;
            // return 'true';
        }
        else
        {
            return 'false';
        }
    }

    public function getFloatingMessage()
    {
        if (isset($_COOKIE['chat_user'])) {
            $cookie_val = json_decode($_COOKIE['chat_user']);
        }
        // $cookie_val = json_decode($_COOKIE['chat_user']);

        $user_id = $cookie_val->id;
        $my_id   = 0;

        // Make read all unread message
        FloatingChatMessage::where(
            [
                'from' => $user_id,
                'to' => $my_id,
            ]
        )->update(['is_read' => 1]);

        // Get all message from selected user
        $messages = FloatingChatMessage::where(
            function ($query) use ($user_id, $my_id){
                $query->where('from', $user_id)->where('to', $my_id);
            }
        )->oRwhere(
            function ($query) use ($user_id, $my_id){
                $query->where('from', $my_id)->where('to', $user_id);
            }
        )->get();

        return view('admin.chats.floating_message', ['messages' => $messages]);
    }

    public function sendFloatingMessage(Request $request)
    {
        $user = User::where('slug', $request->slug)->first();

        $cookie_val = json_decode($_COOKIE['chat_user']);
        $setting      = Utility::settings();
        $supsetting   = Utility::superAdminsettings();

        $from    = empty($_COOKIE['chat_user']) ? 0 : $cookie_val->id;
        $to      = (!empty($request->receiver_id)) ? $request->receiver_id : 0;
        $message = $request->message;

        if($message != null)
        {

            $data          = new FloatingChatMessage();
            $data->from    = $from;
            $data->to      = $to;
            $data->message = $message;
            $data->is_read = 0;
            $data->company_id = $user->id;
            $data->save();
        }

        // pusher
        $options = array(
            'cluster' => $supsetting['PUSHER_APP_CLUSTER'],
            'useTLS' => true,
        );

        $pusher = new Pusher(
            $supsetting['PUSHER_APP_KEY'], $supsetting['PUSHER_APP_SECRET'], $supsetting['PUSHER_APP_ID'], $options
        );

        $data = [
            'from' => $from,
            'to' => $to,
        ]; // sending from and to user id when pressed enter
        $pusher->trigger('my-channel', 'my-event', $data);
    }

    // End Floating Chat
    public function deleteUserMessage(Request $request, $user_id)
    {
        // return redirect()->back()->with('error', __('This operation is not perform due to demo mode.'));

        FloatingChatMessage::where('from', $user_id)->oRwhere('to', $user_id)->delete();
        return redirect()->back()->with('success', __('Chat deleted Successfully'));
    }
}
