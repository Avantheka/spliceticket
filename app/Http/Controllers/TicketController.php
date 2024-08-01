<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CustomField;
use App\Mail\SendCloseTicket;
use App\Mail\SendTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Exports\TicketsExport;
use App\Models\UserCatgory;
use App\Models\Priority;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Conversion;
use Illuminate\Support\Facades\Auth;


class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = \Auth::user();
        $status = '';
        if($user->can('manage-tickets'))
        {
            if(\Auth::user()->type == "Admin")
            {
                $categories = Category::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');
                $categories->prepend('Select Category','All');
                $priorities = Priority::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');
                $priorities->prepend('Select Priority','All');
                $statues = Ticket::$statues;

                $tickets = Ticket::select(
                    [
                        'tickets.*',
                        'categories.name as category_name',
                        'categories.color',
                        'priorities.color as priorities_color',
                        'priorities.name as priorities_name',
                    ]
                )->join('categories', 'categories.id', '=', 'tickets.category')->join('priorities', 'priorities.id', '=', 'tickets.priority');

                if($request->category != 'All' && $request->all() != null){
                    $tickets->where('category',$request->category);

                }

                if($request->priority != 'All' && $request->all() != null){
                    $tickets->where('priority',$request->priority);

                }

                if($request->status != 'All' && $request->all() != null){
                    $tickets->where('status',$request->status);
                }

            $tickets->where('tickets.created_by',\Auth::user()->createId());
            $tickets = $tickets->orderBy('id', 'desc')->get();


            return view('admin.tickets.index', compact('tickets','categories','priorities','statues'));
           }
           else{


            $categories1 = UserCatgory::where('user_id',auth()->user()->id)->pluck('category_id');

            $categories = \DB::table('categories')
            ->join('user_categories', 'user_categories.category_id', '=', 'categories.id')
            ->select(['user_categories.category_id', 'categories.name','categories.id'])
            ->where('user_id', auth()->user()->id)
            ->pluck('categories.name','categories.id');
            $categories->prepend('Select Category','All');


            $priorities = Priority::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');
            $priorities->prepend('Select Priority','All');
            $statues = Ticket::$statues;


            $tickets = Ticket::select(
                [
                    'tickets.*',
                    'categories.name as category_name',
                    'categories.color',
                    'priorities.color as priorities_color',
                    'priorities.name as priorities_name',
                ]
            )->join('categories', 'categories.id', '=', 'tickets.category')->join('priorities', 'priorities.id', '=', 'tickets.priority')->whereIn('category',$categories1);


            if($request->category != 'All' && $request->all() != null){

                $tickets->where('category',$request->category);

            }

            if($request->priority != 'All' && $request->all() != null){
                $tickets->where('priority',$request->priority);

            }

            if($request->status != 'All' && $request->all() != null){
                $tickets->where('status',$request->status);
            }
            $tickets->where('tickets.created_by',\Auth::user()->createId());
            $tickets = $tickets->orderBy('id', 'desc')->get();

            return view('admin.tickets.index', compact('tickets','categories','priorities','statues','categories1'));
           }

        }
        else
        {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();

        if($user->can('create-tickets'))
        {
            $customFields = CustomField::where('created_by',\Auth::user()->createId())->where('custom_id', '>', '7')->get();

            if(\Auth::user()->type == 'Admin')
            {

            $categories = Category::where('created_by',\Auth::user()->createId())->get();
            }
            else
            {

                $usercategorys = UserCatgory::where('user_id',\Auth::user()->id)->get();
                foreach($usercategorys as $usercategory)
                {
                    $categories = Category::where('id',$usercategory->category_id)->get();
                }
            }

            $priorities = Priority::where('created_by',\Auth::user()->createId())->get();

            return view('admin.tickets.create', compact('categories', 'customFields','priorities'));
        }
        else
        {
            return view('403');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if($user->can('create-tickets'))
        {
            $validation = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'category' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'status' => 'required|string|max:100',
                'description' => 'required',
                'priority' => 'required|string|max:255',
            ];


            $this->validate($request, $validation);

            $post              = $request->all();
            $post['ticket_id'] = time();
            $post['created_by'] =\Auth::user()->createId();
            $data              = [];
            if($request->hasfile('attachments'))
            {
                $errors=[];
                     foreach($request->file('attachments') as $filekey => $file)
                    {
                      $file_size = $file->getSize();
                       $result = Utility::updateStorage(\Auth::user()->createId(), $file_size);


                      if($result==1)
                       {
                        $imageName = $file->getClientOriginalName();
                        $dir        = ('tickets/' . $post['ticket_id']);
                        $path = Utility::multipalFileUpload($request,'attachments',$imageName,$dir,$filekey,[]);
                        if($path['flag'] == 1){
                            $data[] = $path['url'];
                        }
                        elseif($path['flag'] == 0){
                            $errors = __($path['msg']);
                        }
                        else{
                            return redirect()->route('tickets.store', \Auth::user()->id)->with('error', __($path['msg']));
                        }
                        $file   = 'tickets/'.$imageName;

                       }
                    }
            }

            $post['attachments'] = json_encode($data);

            $ticket              = Ticket::create($post);


            CustomField::saveData($ticket, $request->customField);
            // slack //
            $settings  = Utility::settings(\Auth::user()->createId());
            if(isset($settings['ticket_notification']) && $settings['ticket_notification'] ==1){
                $uArr = [
                    'name' => $request->name,
                    'email' => $user->email,
                    'category' => $request->category,
                    'subject' => $request->subject,
                    'status' => $request->status,
                    'description' => $request->description,
                    'user_name'  => \Auth::user()->name,
                ];
                Utility::send_slack_msg('new_ticket', $uArr);
            }
            // telegram //
            $settings  = Utility::settings(\Auth::user()->createId());
            if(isset($settings['telegram_ticket_notification']) && $settings['telegram_ticket_notification'] ==1){
                $uArr = [
                    'name' => $request->name,
                    'email' => $user->email,
                    'category' => $request->category,
                    'subject' => $request->subject,
                    'status' => $request->status,
                    'description' => $request->description,
                    'user_name'  => \Auth::user()->name,
                ];
                Utility::send_telegram_msg('new_ticket', $uArr);
            }
            $module = 'New Ticket';


            $webhook =  Utility::webhookSetting($module,$user->created_by);

            if ($webhook) {
                $parameter = json_encode($ticket);

                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);

                if ($status == true) {

                    return redirect()->back()->with('success', __('ticket successfully created!'));
                } else {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }
            // Send Email to User
                $uArr = [
                    'ticket_id' =>time(),
                    'ticket_name' => $request->name,
                    'email' => $request->email,
                    'category' => $request->category,
                    'priority' => $request->priority,
                    'subject' => $request->subject,
                    'status' => $request->status,
                    'description' => $request->description,
                ];
           //Mail Send Agent
            $userids = UserCatgory::where('category_id',$request->category)->pluck('user_id');
            $agents = User::whereIn('id',$userids)->get();
             foreach($agents as $agent)
             {
                Utility::sendEmailTemplate('new_ticket', [$agent->email], $uArr, \Auth::user());
             }
            // Mail Send  Ticket User
                Utility::sendEmailTemplate('new_ticket', [$request->email], $uArr, \Auth::user());

            //Mail Send Auth User
                Utility::sendEmailTemplate('new_ticket', [\Auth::user()->email], $uArr, \Auth::user());
            // Send Email to
            if(isset($error_msg))
            {
                Session::put('smtp_error', '<span class="text-danger ml-2">' . $error_msg . '</span>');
            }
            Session::put('ticket_id', ' <a class="text text-primary" target="_blank" href="' . route('home.view', \Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id)) . '"><b>' . __('Your unique ticket link is this.') . '</b></a>');

            return redirect()->route('tickets.index')->with('success', __('Ticket created successfully'). ((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
        }
        else
        {
            return view('403');
        }
    }

    public function storeNote($ticketID, Request $request)
    {
        $user = \Auth::user();
        if($user->can('reply-tickets'))
        {
            $validation = [
                'note' => ['required'],
            ];
            $this->validate($request, $validation);

            $ticket = Ticket::find($ticketID);
            if($ticket)
            {
                $ticket->note = $request->note;
                $ticket->save();

                return redirect()->back()->with('success', __('Ticket note saved successfully'));
            }
            else
            {
                return view('403');
            }
        }
        else
        {
            return view('403');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function editTicket($id)
    {

        $user = \Auth::user();
        if($user->can('edit-tickets'))
        {
            $ticket = Ticket::find($id);
            if($ticket)
            {
                $customFields        = CustomField::where('created_by',\Auth::user()->createId())->where('custom_id', '>', '7')->get();
                $ticket->customField = CustomField::getData($ticket);
                $categories          = Category::where('created_by',\Auth::user()->createId())->get();
                $priorities = Priority::where('created_by',\Auth::user()->createId())->get();
                return view('admin.tickets.edit', compact('ticket', 'categories', 'customFields','priorities'));
            }
        }
        else
        {
                return redirect()->back('403');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTicket(Request $request,$id)
    {
         $user = \Auth::user();
         if($user->can('edit-tickets'))
         {
             $ticket = Ticket::find($id);
             if($ticket)
             {
                 $validation = [
                     'name' => 'required|string|max:255',
                     'email' => 'required|string|email|max:255',
                     'category' => 'required|string|max:255',
                     'priority' => 'required|string|max:255',
                     'subject' => 'required|string|max:255',
                     'status' => 'required|string|max:100',
                     'description' => 'required',
                 ];
                 $post = $request->all();

                 $post['created_by'] = \Auth::user()->createId();
                 $data              = [];
                 if($request->hasfile('attachments'))
                 {
                     $data = json_decode($ticket->attachments, true);

                      foreach($request->file('attachments') as $filekey => $file)
                     {
                     $file_size = $file->getSize();
                     $result = Utility::updateStorage(\Auth::user()->createId(), $file_size);


                     if($result==1)
                     {
                         $imageName = $file->getClientOriginalName();
                         $dir        = ('tickets/' . $ticket->ticket_id);
                         $path = Utility::multipalFileUpload($request,'attachments',$imageName,$dir,$filekey,[]);
                         if($path['flag'] == 1){
                             $data[] = $path['url'];
                         }
                         elseif($path['flag'] == 0){
                             $errors = __($path['msg']);
                         }
                         else{
                             return redirect()->route('tickets.store', \Auth::user()->id)->with('error', __($path['msg']));
                         }
                         $file   = 'tickets/'.$imageName;

                     }
                   }
                 }
                 $post['attachments'] = json_encode($data);

                 if($request->status == 'Resolved')
                 {
                     $ticket->reslove_at = now();
                 }
                 $ticket->update($post);
                 $ticket->save();

                 CustomField::saveData($ticket, $request->customField);

                 $error_msg = '';
                 if($ticket->status == 'Closed')
                 {
                     // Send Email to User
                     try
                     {
                        $settings = Utility::settings();

                        $isAllEmpty = empty($settings['mail_driver']);

                        if ($isAllEmpty) {
                            $settings = Utility::settingsById(1);
                        }

                        config([
                            'mail.driver'       => $settings['mail_driver'],
                            'mail.host'         => $settings['mail_host'],
                            'mail.port'         => $settings['mail_port'],
                            'mail.username'     => $settings['mail_username'],
                            'mail.password'     => $settings['mail_password'],
                            'mail.encryption'   => $settings['mail_encryption'],
                            'mail.from.address' => $settings['mail_from_address'],
                            'mail.from.name'    => $settings['mail_from_name'],
                        ]);

                         Mail::to($ticket->email)->send(new SendCloseTicket($ticket));
                     }
                     catch(\Exception $e)
                     {
                         $error_msg = "E-Mail has been not sent due to SMTP configuration ";
                     }
                 }
                 // return redirect()->route('tickets.index')->with('success', __('Ticket updated successfully.') . ((isset($error_msg) && !empty($error_msg)) ? '<span class="text-danger">' . $error_msg . '</span>' : ''));
                 return redirect()->route('tickets.index')->with('success', __('Ticket Updated Successfully'). ((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
             }
         }
         else
         {
             return view('403');
         }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Ticket $ticket
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = \Auth::user();
        if($user->can('edit-tickets'))
        {
            $ticket = Ticket::find($id);
            $attachments = json_decode($ticket->attachments);
            if(($attachments))
            {
                foreach($attachments as $attachment)
                {
                    $file_path = '/tickets/'.$ticket->ticket_id . '/'.$attachment;
                    $result = Utility::changeStorageLimit(\Auth::user()->createId(), $file_path);
                        unset($attachment);
                        $ticket->attachments = json_encode(array_values($attachments));
                }
            }
            $ticket->delete();
            return redirect()->back()->with('success', __('Ticket deleted successfully'));
        }
        else
        {
            return view('403');
        }
    }

    public function attachmentDestroy($ticket_id, $id)
    {
        $user = \Auth::user();
        if($user->can('edit-tickets'))
        {
            $ticket      = Ticket::find($ticket_id);
            $attachments = json_decode($ticket->attachments);
            if(isset($attachments[$id]))
            {

            $file_path = '/tickets/'.$ticket->ticket_id . '/'.$attachments[$id];
            $result = Utility::changeStorageLimit(\Auth::user()->createId(), $file_path);
                unset($attachments[$id]);
                $ticket->attachments = json_encode(array_values($attachments));

                $ticket->save();
                return redirect()->back()->with('success', __('Attachment deleted successfully'));
            }

            else
            {
                return redirect()->back()->with('error', __('Attachment is missing'));
            }
        }
        else
        {
            return view('403');
        }
    }
    public function export()
    {
        $name = 'Tickets' . date('Y-m-d i:h:s');
        $data = Excel::download(new TicketsExport(), $name . '.csv');
        return $data;
    }


}
