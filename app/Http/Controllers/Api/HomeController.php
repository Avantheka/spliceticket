<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Api\User;
use App\Models\Api\Category;
use App\Models\Api\Ticket;
use Carbon\Carbon;

class HomeController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {

        $user = User::find($request['id']);

   

        $categories   = Category::where('created_by',$user->id)->count();
        $open_ticket  = Ticket::where('created_by',$user->id)->whereIn('status', ['On Hold','In Progress'])->count();
        $close_ticket = Ticket::where('created_by',$user->id)->where('status', '=', 'Closed')->count();
        $agents       = User::where('parent',$user->id)->where('type', '=', 'Agent')->count();
        $today_ticket = Ticket::where('created_by',$user->id)->whereDate('created_at', Carbon::today())->count();

        // Latest Ticket
        $tickets = Ticket::select('id','ticket_id','name','email','category','subject','status','description','note','attachments')->orderBy('id', 'desc')->take(5)->get();



        // Start Categories Analytics
            $categoriesChart = Ticket::select(
                [
                    'tickets.category',
                    'categories.name',
                    'categories.color',
                    \DB::raw('count(*) as total'),
                ]
            )->join('categories', 'categories.id', '=', 'tickets.category')->where('categories.created_by',$user->id)->groupBy('categories.id')->get();
			
			
		


            $anew_ticket = Ticket::where('created_by',$user->id)->whereDate('created_at', Carbon::today())->count();
            $aopen_ticket  = Ticket::where('created_by',$user->id)->whereIn('status', ['On Hold','In Progress'])->count();
            $aclose_ticket = Ticket::where('created_by',$user->id)->where('status', '=', 'Closed')->count();



            $total_cat_ticket   = Ticket::where('created_by',$user->id)->count();

            $chartData = [];
            if(count($categoriesChart) > 0)
            {
                foreach($categoriesChart as $category)
                {

                    $cat_ticket = round((float)(($category->total / 100) * $total_cat_ticket) * 100);

                    $chartData[]=[
                        'category' => $category->name,
                        'color' => $category->color,
                        'value' => $cat_ticket,
                    ];
                }
            }
        // End Categories Analytics


        // Start Ticket Analytics



            $atotal_ticket = $anew_ticket+$aopen_ticket+$aclose_ticket;

            if($atotal_ticket != 0){

                $anew_ticket = round((float)((100 * $anew_ticket)/$atotal_ticket));
                $aopen_ticket = round((float)((100 * $aopen_ticket)/$atotal_ticket));
                $aclose_ticket = round((float)((100 * $aclose_ticket)/$atotal_ticket));
            }else{
                $anew_ticket = 0;
                $aopen_ticket = 0;
                $aclose_ticket = 0;
            }

            $ticket_analytics = [
                'new_ticket'=> $anew_ticket,
                'open_ticket'=> $aopen_ticket,
                'close_ticket'=> $aclose_ticket
            ];
        // End Ticket Analytics


            $datagrph = Ticket::getIncExpLineChartDate();

             $y[] = [
               'name' =>"Open Ticket",
               'color' => "#6FD943",
               'data' => $datagrph['open_ticket'],
            ];
            $y[] = [
                'name' =>"Close Ticket",
                'color' => "#FF3a6e",
                'data' => $datagrph['close_ticket'],
             ];

            $graph_data = [
                'x_axis'=> $datagrph['day'],
                'y_axis'=> $y
            ];



        $user = [
            'id'=> 1,
            'name'=> $user->name,
            'email'=> $user->email,
            'image_url'=> asset(\Storage::url('public/'.$user->avatar)),
            'total_ticket'=>$today_ticket,
        ];

        $statistics = [
            'category'=> $categories,
            'open_ticket'=> $open_ticket,
            'close_ticket'=> $close_ticket,
            'agents'=> $agents
        ];



        $data = [
            'user_data' =>$user,
            'statistics'=>$statistics,
            'last_ticket'=>$tickets,
            'graph_data'=>$graph_data,
            'category_analytics'=>$chartData,
            'ticket_analytics'=>$ticket_analytics


        ];

        return $this->success($data);

    }

    public function logout(Request $request)
    {

    }
}
