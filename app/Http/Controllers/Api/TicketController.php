<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendCloseTicket;
use App\Mail\SendTicket;
use Illuminate\Support\Facades\Mail;
use App\Models\Api\User;
use App\Models\Api\Category;
use App\Models\Api\Ticket;
use App\Models\CustomField;
use App\Models\Conversion;
use App\Mail\SendTicketAdminReply;
use Carbon\Carbon;


class TicketController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {

        $user = \Auth::user();

            $ticket_query = Ticket::query()->select('id','ticket_id','name','email','category','subject','status','description','note','attachments');


            if($request->search){

                $ticket_query->where('name', 'like', "%{$request->search}%")->orWhere('ticket_id','like', "%{$request->search}%");
            }

            if($request->status || $request->period){


                if($request->period == "today"){

                    $ticket_query->whereDate( 'created_at', '>', Carbon::now()->subDays(1)->toDateString());
                }

                if($request->period == "week"){
                    $ticket_query->whereDate( 'created_at', '>', Carbon::now()->subDays(7)->toDateString());

                }

                if($request->period == "month"){
                    $ticket_query->whereDate( 'created_at', '>', Carbon::now()->subDays(30)->toDateString() );
                }


                if($request->period == "progress"){

                    $ticket_query->where( 'status', 'In Progress');
                }

                if($request->period == "closed"){
                    $ticket_query->where( 'status', 'Closed');

                }

                if($request->period == "hold"){
                    $ticket_query->where( 'status' , "On Hold");
                }

            }

            $ticket_query->where('created_by',$user->id);
            $tickets = $ticket_query->paginate(10);

            if(count($tickets) == 0){
                $tickets = "";
            }


            $ticket_in_progress = Ticket::select('id','ticket_id','name','email','category','subject','status','description','note','attachments')->where('created_by',$user->id)->where('status','In Progress')->take(10)->get();
            $ticket_tickets = Ticket::select('id','ticket_id','name','email','category','subject','status','description','note','attachments')->where('created_by',$user->id)->where('status','On Hold')->take(10)->get();
            $ticket_close = Ticket::select('id','ticket_id','name','email','category','subject','status','description','note','attachments')->where('created_by',$user->id)->where('status','Closed')->take(10)->get();


            $new_ticket = Ticket::where('created_by',$user->id)->whereDate('created_at', Carbon::today())->count();
            $open_ticket  = Ticket::where('created_by',$user->id)->whereIn('status', ['On Hold','In Progress'])->count();
            $close_ticket = Ticket::where('created_by',$user->id)->where('status', '=', 'Closed')->count();



        $total_ticket = $new_ticket+$open_ticket+$close_ticket;

        if($total_ticket != 0){

            $new_ticket = round((float)((100 * $new_ticket)/$total_ticket));
            $open_ticket = round((float)((100 * $open_ticket)/$total_ticket));
            $close_ticket = round((float)((100 * $close_ticket)/$total_ticket));
        }else{
            $new_ticket = 0;
            $open_ticket = 0;
            $close_ticket = 0;
        }


        $statistics = [
            'new_ticket'=> $new_ticket,
            'open_ticket'=> $open_ticket,
            'close_ticket'=> $close_ticket,
        ];

        $status[] = [
                'status'=> 'in_progress',
                'tickets'=>$ticket_in_progress,
            ];

        $status[] = [
                'status'=> 'hold',
                'tickets'=>$ticket_tickets,
            ];

        $status[] =[
                'status'=> 'close',
                'tickets'=>$ticket_close,
            ];



        if(!empty($tickets)){

            $data = [

                'status'=>$status,
                'ticket'=>$tickets,
                'analytics'=>$statistics,
            ];

            return $this->success($data);
        }else{
		
            $data = [
                'status'=>[],
                'ticket'=> (object)$tickets=[],
                "analytics"=> $statistics
            ];

            return $this->success($data);
        }

    }



    public function store(Request $request)
    {

        $user = \Auth::user();

     if($request->id == null){

         $validation = [
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255',
             'category' => 'required|string|max:255',
             'subject' => 'required|string|max:255',
             'status' => 'required|string|max:100',
             'description' => 'required',
         ];

         if($request->hasfile('attachments'))
         {
             $validation['attachments.*'] = 'mimes:zip,rar,jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,application/octet-stream,audio/mpeg,mpga,mp3,wav|max:204800';
         }

         $this->validate($request, $validation);

         $post              = $request->all();
         $post['ticket_id'] = time();
         $data              = [];
         if($request->hasfile('attachments'))
         {
             foreach($request->file('attachments') as $file)
             {

                 $name = $file->getClientOriginalName();
                 $file->storeAs('/tickets/' . $post['ticket_id'], $name);
                 $data[] = $name;
             }
         }
         $post['attachments'] = json_encode($data);



        $post['created_by'] = $user->id;

         $ticket              = Ticket::create($post);

          CustomField::saveData($ticket, $request->custom_field);

         // Send Email to User
         try
         {
             Mail::to($ticket->email)->send(new SendTicket($ticket));
         }
         catch(\Exception $e)
         {
			 
             $error_msg = "E-Mail has been not sent due to SMTP configuration ";
         }

         // Send Email to
         if(isset($error_msg))
         {
             Session::put('smtp_error', '<span class="text-danger ml-2">' . $error_msg . '</span>');
         }
         Session::put('ticket_id', ' <a class="text text-primary" target="_blank" href="' . route('home.view', \Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id)) . '"><b>' . __('Your unique ticket link is this.') . '</b></a>');


         if(!empty($ticket)){

            $data = [
                'ticket'=>$ticket,
            ];

            return $this->success($data);
        }else{
            $data = [
                'ticket'=>[],
            ];

            return $this->error($data);
        }

     }else{

        $ticket = Ticket::find($request->id);
            if($ticket)
            {
                $validation = [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255',
                    'category' => 'required|string|max:255',
                    'subject' => 'required|string|max:255',
                    'status' => 'required|string|max:100',
                    'description' => 'required',
                ];

                if($request->hasfile('attachments'))
                {
                    $validation['attachments.*'] = 'mimes:zip,rar,jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,application/octet-stream,audio/mpeg,mpga,mp3,wav|max:204800';
                }

                $this->validate($request, $validation);

                $post = $request->all();
                if($request->hasfile('attachments'))
                {
                    // $data = json_decode($ticket->attachments, true);

                    $data=[];
                    foreach($request->file('attachments') as $file)
                    {
                        $name = $file->getClientOriginalName();
                        $file->storeAs('/tickets/' . $ticket->ticket_id, $name);
                        $data[] = $name;
                    }
                    $post['attachments'] = json_encode($data);
                }


                $post['created_by'] = $user->id;


                $ticket->update($post);
                CustomField::saveData($ticket, $request->custom_field);

                $error_msg = '';
                if($ticket->status == 'Closed')
                {
                    // Send Email to User
                    try
                    {
                        Mail::to($ticket->email)->send(new SendCloseTicket($ticket));
                    }
                    catch(\Exception $e)
                    {
                        $error_msg = "E-Mail has been not sent due to SMTP configuration ";
                    }
                }

                $data = [
                    'ticket'=>$ticket,
                ];

                return $this->success($data);

            }
            else
            {
                $data = [
                    'ticket'=>$ticket,
                ];

                return $this->error($data);
            }
     }

    }



    public function destroy(Request $request)
    {
        $ticket = Ticket::find($request->id);
        $ticket->delete();

        $data = [
            'ticket'=>[],
        ];

        return $this->success($data);

    }


    public function openTicket(Request $request)
    {
        $ticket = Ticket::find($request->id);

        $conversions= $ticket->conversions;

        $conversions_data=[];
        foreach($conversions as $conversion){

            $attachment = json_decode($conversion->attachments, true);

            $attachments=[];
            foreach ($attachment as $key => $value) {
                $attachments[]=$value;
            }

            $conversions_data[]=[
                'id'            => $conversion->id != null ? $conversion->id :'',
                'ticket_id'     => $conversion->ticket_id,
                'description'   => $conversion->description,
                'attachments'   => $attachments,
                'email'         => $ticket->email,
            ];

        }

        $ticket=[
            'id'            => $ticket->id != null ? $ticket->id :'',
            'ticket_id'     => $ticket->ticket_id,
            'name'          => $ticket->name,
            'email'         => $ticket->email,
            'category'      => $ticket->tcategory->name,
            'color'         => $ticket->tcategory->color,
            'subject'       => $ticket->subject,
            'status'        => $ticket->status,
            'description'   => $ticket->description,
            'attachments'   => $attachments,
            'note'          => $ticket->note,
        ];

        $data = [
            'ticket'=>$ticket,
            'conversion'=>$conversions_data,

        ];

        return $this->success($data);

    }

    public function replayTicket(Request $request)
    {


            $user = User::find($request->id);

            if($user || $user->can('reply-tickets')) {
                $ticket = Ticket::find($request->ticket_id);
                if($ticket) {
                    $validation = ['reply_description' => ['required']];
                    if ($request->hasfile('reply_attachments')) {
                        $validation['reply_attachments.*'] = 'mimes:zip,rar,jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,application/octet-stream,audio/mpeg,mpga,mp3,wav|max:204800';
                    }
                    $this->validate($request, $validation);

                    $post = [];
                    $post['sender'] = ($user)?$user->id:'user';
                    $post['ticket_id'] = $ticket->id;
                    $post['description'] = $request->reply_description;

                    $data = [];
                    if ($request->hasfile('reply_attachments')) {

                        foreach ($request->file('reply_attachments') as $file) {
                            $name = $file->getClientOriginalName();
                            $file->storeAs('/tickets/' . $ticket->ticket_id, $name);
                            $data[] = $name;
                        }
                    }
                    $post['attachments'] = json_encode($data);


                    $conversion = Conversion::create($post);

                    // Send Email to User
                    try {
                        Mail::to($ticket->email)->send(new SendTicketAdminReply($ticket,$conversion));
                    }catch (\Exception $e){
                        $error_msg = "E-Mail has been not sent due to SMTP configuration ";
                    }


                    $data = [
                        'replay'=>$conversion,
                    ];

                    return $this->success($data);


                }else{
                    return view('403');
                }
            }else{
                return view('403');
            }

        $data = [
            'ticket'=>$ticket,
        ];

        return $this->success($data);

    }


    public function getCoustomField(Request $request){

        $user = \Auth::user();

        $customFields = CustomField::select('id','name','type','placeholder')->where('custom_id', '>', '6')->where('created_by',$user->id)->get();

        $data = [
            'custom_fields'=>$customFields,
        ];

        return $this->success($data);

    }


}
