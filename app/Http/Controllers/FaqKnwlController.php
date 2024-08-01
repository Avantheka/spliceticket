<?php

namespace App\Http\Controllers;

use App\Mail\SendTicketReply;
use Illuminate\Http\Request;
use App\Models\Conversion;
use App\Models\Utility;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Faq;
use App\Models\Knowledge;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Priority;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class FaqKnwlController extends Controller
{

    public function ticket_slug($slug)
    {

        $user         = User::where('slug', $slug)->first();
        $setting      = Utility::ticketFrontSettings($user->id);
        $customFields = CustomField::where('created_by',$user->id)->orderBy('order')->get();
        $categories   = Category::where('created_by',$user->id)->get();
        $priorities = Priority::where('created_by',$user->id)->get();

        $faq          = Faq::get()->pluck('id');

        return view('faq_knwl.faq_knwl', compact('setting','customFields','categories','faq','slug','priorities'));

    }

    public function knowledges($slug, Request $request)
    {
        $user = User::where('slug', $slug)->first();

        $setting      = Utility::ticketFrontSettings($user->id);
        if($setting['Knowlwdge_Base'] == 'on')
        {
            $knowledges = Knowledge::select('category')->groupBy('category')->where('created_by',$user->id)->get();
            $knowledges_detail=knowledge::where('created_by',$user->id)->get();

            return view('faq_knwl.knowledge', compact('knowledges','knowledges_detail','setting','slug'));
        }
        else
        {
            return redirect('/');
        }
    }

    public function knowledgeDescription($slug, Request $request)
    {

        $descriptions = Knowledge::find($request->id);
        return view('faq_knwl.knowledgedesc', compact('descriptions'));
    }

    public function faqs($slug)
    {
        $user = User::where('slug', $slug)->first();

        $setting      = Utility::ticketFrontSettings($user->id);

        if($setting['FAQ'] == 'on')
        {
            $faqs = Faq::where('created_by', $user->id)->get();

            return view('faq_knwl.faq', compact('faqs','setting','slug','user'));
        }
        else
        {
            return redirect('/');
        }
    }

    public function searches($slug)
    {

        $user = User::where('slug', $slug)->first();
        $setting      = Utility::ticketFrontSettings($user->id);

        return view('faq_knwl.search',compact('setting','user','slug'));
    }


    public function ticketsSearches(Request $request)
    {

        $validation = [
            'ticket_id' => ['required'],
            'email' => ['required'],
        ];

        $this->validate($request, $validation);
        $ticket = Ticket::where('ticket_id', '=', $request->ticket_id)->where('email', '=', $request->email)->first();

        if($ticket)
        {
            return redirect()->route('home.view', Crypt::encrypt($ticket->ticket_id));
        }
        else
        {
            return redirect()->back()->with('info', __('Invalid Ticket Number'));
        }

        return view('faq_knwl.search');
    }

    public function view($ticket_id)
    {

        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket    = Ticket::where('ticket_id', '=', $ticket_id)->first();
        if($ticket)
        {
            return view('faq_knwl.show', compact('ticket'));
        }
        else
        {
            return redirect()->back()->with('error', __('Some thing is wrong'));
        }
    }

    public function reply(Request $request, $ticket_id)
    {
        $ticket = Ticket::where('ticket_id', '=', $ticket_id)->first();
        if($ticket)
        {
            $validation = ['reply_description' => ['required']];
            if($request->hasfile('reply_attachments'))
            {
                $validation['reply_attachments.*'];
            }
            $this->validate($request, $validation);

            $post                = [];
            $post['sender']      = 'user';
            $post['ticket_id']   = $ticket->id;
            $post['description'] = $request->reply_description;
            $data                = [];
            if($request->hasfile('reply_attachments'))
            {
                foreach($request->file('reply_attachments') as $filekey => $file)
                {
                        $file_size = $file->getSize();
                        $result = Utility::updateStorage($ticket->created_by,$file_size);

                    if($result==1)
                    {
                        $imageName = $file->getClientOriginalName();
                        $dir        = ('reply_tickets/' . $post['ticket_id']);
                        $path = Utility::multipalFileUpload($request,'reply_attachments',$imageName,$dir,$filekey,[]);
                        if($path['flag'] == 1){
                            $data[] = $path['url'];
                        }
                        elseif($path['flag'] == 0){
                            $errors = __($path['msg']);
                        }

                    }
                }
            }
            $post['attachments'] = json_encode($data);
            $conversion          = Conversion::create($post);


            // webhook //
             $module = 'New Ticket Reply';
             $webhook =  Utility::webhookSetting($module,$ticket->created_by);

             if ($webhook) {
                 $parameter = json_encode($conversion);
                 // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                 $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                 if ($status == true) {
                     return redirect()->back()->with('success', __('Reply successfully added!') . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : ''));
                 } else {
                     return redirect()->back()->with('error', __('Webhook call failed.'));
                 }
             }

            //slack
            $settings  = Utility::non_auth_settings($ticket->created_by);
            if(isset($settings['reply_notification']) && $settings['reply_notification'] ==1){
                $uArr = [
                    'name' => $ticket->name,
                    'ticket_id' => $ticket->id,
                    'email' => $ticket->email,
                    'description' => $request->reply_description,
                    'user_name'  => 'user',
                ];
                Utility::send_slack_msg('new_ticket_reply', $uArr,$ticket->created_by);
            }
            // telegram //
             $settings  = Utility::non_auth_settings($ticket->created_by);
             if(isset($settings['telegram_reply_notification']) && $settings['telegram_reply_notification'] ==1){
                 $uArr = [
                     'name' => $request->name,
                     'ticket_id' => $ticket->id,
                     'email' => $ticket->email,
                     'description' => $request->reply_description,
                     'user_name'  => 'user',
                 ];

                 Utility::send_telegram_msg('new_ticket_reply', $uArr,$ticket->created_by);
             }
            Utility::getSMTPDetails(1);
            // Send Email to User
            try
            {
                $users = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')->where('model_has_roles.model_type', '=', 'App\Models\User')->where('role_id', '=', 1)->get();

                foreach($users as $user)
                {
                    Mail::to($user->email)->send(new SendTicketReply($user, $ticket, $conversion));
                }
            }
            catch(\Exception $e)
            {
                $error_msg = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Reply added successfully') . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : '') .((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));

        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

}
