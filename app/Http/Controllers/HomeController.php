<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserCatgory;
use App\Models\Conversion;
use App\Models\CustomField;
use App\Models\Faq;
use App\Mail\SendTicket;
use App\Mail\SendTicketAdmin;
use App\Mail\SendTicketReply;
use App\Models\CustomDomainRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Knowledge;
use App\Models\Utility;
use App\Models\Settings;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    private  $language;
    public function __construct()
    {
        if (!file_exists(storage_path() . "/installed")) {
            return redirect('install');
        }
        $language = Utility::getSettingValByName('DEFAULT_LANG');
        \App::setLocale(isset($language) ? $language : 'en');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        if (!file_exists(storage_path() . "/installed")) {
            return redirect('install');
        }

        $uri = url()->full();
        $segments = explode('/', str_replace('' . url('') . '', '', $uri));
        $segments = $segments[1] ?? null;


        $local = parse_url(config('app.url'))['host'];

        $remote = request()->getHost();
        $remote = str_replace('www.', '', $remote);
        if (Auth::user() && $local != $remote) {
            $subdomain = Settings::where('name', 'subdomain')->where('value', $remote)->first();
            $settings = Utility::settingsById(Auth::user()->id);
            $domain = CustomDomainRequest::where('status', '1')->where('user_id', Auth::user()->id)->where('custom_domain', $remote)->first();
            // If the domain exists

            if (!empty($domain) && $settings['domain_switch'] == 'on') {
                if ($subdomain || $domain) {

                    $admin_user = "";
                    if ($subdomain) {
                        $admin_user = User::find($subdomain->created_by);
                    }
                    if ($domain) {
                        $admin_user = User::find($domain->user_id);
                    }
                }
                $admin_user = "";
                if ($admin_user) {
                    return app('App\Http\Controllers\FaqKnwlController')->ticket_slug($admin_user->slug);
                }
            } else {
                return abort('404', 'Not Found');
            }
        } elseif ($local != $remote) {
            $subdomain = Settings::where('name', 'subdomain')->where('value', $remote)->first();
            $domain = CustomDomainRequest::where('status', '1')->where('custom_domain', $remote)->first();
            // If the domain exists
            if (!empty($domain)) {
                $settings = Utility::settingsById($domain->user_id);

                if ($subdomain || $domain  && $settings['domain_switch'] == 'on') {

                    $admin_user = "";
                    if ($subdomain) {

                        $admin_user = User::find($subdomain->created_by);
                    }
                    if ($domain) {
                        $admin_user = User::find($domain->user_id);
                    }
                }
                $admin_user = "";
                if ($admin_user) {
                    return app('App\Http\Controllers\FaqKnwlController')->ticket_slug($admin_user->slug);
                }
            } else {
                return abort('404', 'Not Found');
            }
        }
        if (Auth::user()) {
            return redirect()->route('dashboard');
        } else {
            $settings = Utility::settings();
            if ($settings['display_landing'] == 'on') {


                return view('landingpage::layouts.landingpage');
            } else {
                return redirect('login');
            }
        }
        $customFields = CustomField::orderBy('order')->get();
        $categories   = Category::get();
        $setting      = Utility::settings();

        return view('layouts.landing', compact('categories', 'customFields', 'setting'));
    }

    // public function search()
    // {
    //     $setting      = Utility::settings();
    //     return view('search',compact('setting'));
    // }

    public function search($lang = '')
    {

        $setting      = Utility::settings();
        if ($lang == '') {
            $lang = Utility::getSettingValByName('DEFAULT_LANG') ?? 'en';
        }
        \App::setLocale($lang);
        // return view('auth.passwords.email');
        return view('search', compact('setting'));
    }


    public function faq()
    {
        $setting      = Utility::settings();
        if ($setting['FAQ'] == 'on') {
            $faqs = Faq::get();

            return view('faq', compact('faqs', 'setting'));
        } else {
            return redirect('/');
        }
    }

    public function ticketSearch(Request $request)
    {
        $validation = [
            'ticket_id' => ['required'],
            'email' => ['required'],
        ];

        $this->validate($request, $validation);
        $ticket = Ticket::where('ticket_id', '=', $request->ticket_id)->where('email', '=', $request->email)->first();

        if ($ticket) {
            return redirect()->route('home.view', Crypt::encrypt($ticket->ticket_id));
        } else {
            return redirect()->back()->with('info', __('Invalid Ticket Number'));
        }

        return view('search');
    }

    public function store(Request $request)
    {
        if (\Auth::user()) {
            $user = \Auth::user();
            $user_id = \Auth::user()->createId();
        } else {
            $user = User::where('slug', $request->slug)->first();
            $user_id = $user->id;
        }
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'status' => 'required|string|max:100',
            'description' => 'required',
            'priority' => 'required|string|max:255',
        ];

        if (Utility::getValByName('RECAPTCHA_MODULE') == 'yes') {
            $validation['g-recaptcha-response'] = 'required';
        }

        $this->validate($request, $validation);

        $post              = $request->all();
        $post['ticket_id'] = time();
        $post['created_by'] = $user_id;
        $data              = [];
        if ($request->hasfile('attachments')) {


            $errors = [];
            foreach ($request->file('attachments') as $filekey => $file) {
                $file_size = $file->getSize();
                $result = Utility::updateStorage($user_id, $file_size);
                if ($result == 1) {
                    $imageName = $file->getClientOriginalName();
                    $dir        = ('tickets/' . $post['ticket_id']);
                    $path = Utility::multipalFileUpload($request, 'attachments', $imageName, $dir, $filekey, []);
                    if ($path['flag'] == 1) {
                        $data[] = $path['url'];
                    } elseif ($path['flag'] == 0) {
                        $errors = __($path['msg']);
                    }
                }
            }
        }
        $post['attachments'] = json_encode($data);
        $ticket              = Ticket::create($post);
        CustomField::saveData($ticket, $request->customField);

        $uArr = [

            'name' => $request->name,
            'email' => $request->email,
            'category' => $request->category,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => $request->status,
            'description' => $request->description,
        ];

        // slack //
        $settings  = Utility::non_auth_settings($ticket->created_by);
        if (isset($settings['ticket_notification']) && $settings['ticket_notification'] == 1) {
            $uArr = [
                'name' => $request->name,
                'email' => $user->email,
                'category' => $request->category,
                'subject' => $request->subject,
                'status' => $request->status,
                'description' => $request->description,
                'user_name'  => $request->slug,
            ];
            Utility::send_slack_msg('new_ticket', $uArr, $ticket->created_by);
        }

        // telegram //
        $settings  = Utility::non_auth_settings($ticket->created_by);
        if (isset($settings['telegram_ticket_notification']) && $settings['telegram_ticket_notification'] == 1) {
            $uArr = [
                'name' => $request->name,
                'email' => $user->email,
                'category' => $request->category,
                'subject' => $request->subject,
                'status' => $request->status,
                'description' => $request->description,
                'user_name'  => $request->slug,
            ];
            Utility::send_telegram_msg('new_ticket', $uArr, $ticket->created_by);
        }



        try {

            //Mail Send Agent
            $userids = UserCatgory::where('category_id', $request->category)->pluck('user_id');
            $agents = User::whereIn('id', $userids)->get();
            Utility::getSMTPDetails(1);
            foreach ($agents as $agent) {
                // dd($agent->email);
                Mail::to($agent->email)->send(new SendTicketAdmin($agent, $ticket));
            }
            // Mail Send  Ticket User
            Mail::to($ticket->email)->send(new SendTicket($ticket));
        } catch (\Exception $e) {

            $error_msg = __('E-Mail has been not sent due to SMTP configuration');
        }



        //webhook
        $module = 'New Ticket';
        $webhook =  Utility::webhookSetting($module, $ticket->created_by);

        if ($webhook) {
            $parameter = json_encode($ticket);
            // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
            $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
            if ($status == true) {

                return redirect()->back()->with('success', __('ticket successfully created!'));
            } else {
                return redirect()->back()->with('error', __('Webhook call failed.'));
            }
        }
        return redirect()->back()->with('create_ticket', __('Ticket created successfully') . ' <a href="' . route('home.view', Crypt::encrypt($ticket->ticket_id)) . '"><b>' . __('Your unique ticket link is this.') . '</b></a> ' . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : '') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
    }

    public function view($ticket_id)
    {

        $ticket_id = Crypt::decrypt($ticket_id);

        $ticket    = Ticket::where('ticket_id', '=', $ticket_id)->first();
        if ($ticket) {
            return view('show', compact('ticket'));
        } else {
            return redirect()->back()->with('error', __('Some thing is wrong'));
        }
    }

    public function reply(Request $request, $ticket_id)
    {


        $ticket = Ticket::where('ticket_id', '=', $ticket_id)->first();


        if ($ticket) {
            $validation = ['reply_description' => ['required']];
            if ($request->hasfile('reply_attachments')) {
                $validation['reply_attachments.*'] = '';
            }
            $this->validate($request, $validation);

            $post                = [];
            $post['sender']      = 'user';
            $post['ticket_id']   = $ticket->id;
            $post['description'] = $request->reply_description;
            $data                = [];
            if ($request->hasfile('reply_attachments')) {


                foreach ($request->file('reply_attachments') as $filekey => $file) {
                    $file_size = $file->getSize();
                    $result = Utility::updateStorage($ticket->created_by, $file_size);

                    if ($result == 1) {
                        $imageName = $file->getClientOriginalName();
                        $dir        = ('reply_tickets/' . $post['ticket_id']);
                        $path = Utility::multipalFileUpload($request, 'reply_attachments', $imageName, $dir, $filekey, []);
                        if ($path['flag'] == 1) {
                            $data[] = $path['url'];
                        } elseif ($path['flag'] == 0) {
                            $errors = __($path['msg']);
                        }
                    }
                }
            }

            $post['attachments'] = json_encode($data);
            $conversion          = Conversion::create($post);
            $ticket->status = 'In Progress';
            $ticket->update();


            // webhook //
            $module = 'New Ticket Reply';
            $webhook =  Utility::webhookSetting($module, $ticket->created_by);

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
            if (isset($settings['reply_notification']) && $settings['reply_notification'] == 1) {
                $uArr = [
                    'name' => $ticket->name,
                    'ticket_id' => $ticket->id,
                    'email' => $ticket->email,
                    'description' => $request->reply_description,
                    'user_name'  => 'user',
                ];
                Utility::send_slack_msg('new_ticket_reply', $uArr, $ticket->created_by);
            }
            // telegram //
            $settings  = Utility::non_auth_settings($ticket->created_by);
            if (isset($settings['telegram_reply_notification']) && $settings['telegram_reply_notification'] == 1) {
                $uArr = [
                    'name' => $request->name,
                    'ticket_id' => $ticket->id,
                    'email' => $ticket->email,
                    'description' => $request->reply_description,
                    'user_name'  => 'user',
                ];

                Utility::send_telegram_msg('new_ticket_reply', $uArr, $ticket->created_by);
            }
            Utility::getSMTPDetails(1);

            // Send Email to User
            try {


                $users = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')->where('model_has_roles.model_type', '=', 'App\Models\User')->where('role_id', '=', 1)->get();


                foreach ($users as $user) {
                    Mail::to($user->email)->send(new SendTicketReply($user, $ticket, $conversion));
                }
            } catch (\Exception $e) {
                // Mail::to($ticket->email)->send(new SendTicketAdminReply($ticket,$conversion));
                $error_msg = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Reply added successfully') . ((isset($error_msg)) ? '<br> <span class="text-danger">' . $error_msg . '</span>' : '') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function knowledge(Request $request)
    {
        $setting      = Utility::settings();
        if ($setting['Knowlwdge_Base'] == 'on') {
            $knowledges = Knowledge::select('category')->groupBy('category')->get();
            $knowledges_detail = knowledge::get();

            return view('knowledge', compact('knowledges', 'knowledges_detail', 'setting'));
        } else {
            return redirect('/');
        }
    }

    public function knowledgeDescription(Request $request)
    {
        $descriptions = Knowledge::find($request->id);
        return view('knowledgedesc', compact('descriptions'));
    }
}
