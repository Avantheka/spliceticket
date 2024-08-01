<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $objUser = \Auth::user();
        if ($objUser) {
            $categories   = Category::where('created_by', \Auth::user()->createId())->count();
            $open_ticket  = Ticket::whereIn('status', ['On Hold', 'In Progress'])->where('created_by', \Auth::user()->createId())->count();
            $close_ticket = Ticket::where('status', '=', 'Closed')->where('created_by', \Auth::user()->createId())->count();
            // $new_ticket   = Ticket::where('status','=','New Ticket')->where('created_by', \Auth::user()->createId())->count();
            $agents       = User::where('type', 'Agent')->where('parent', \Auth::user()->createId())->count();

            $categoriesChart = Ticket::select(
                [
                    'categories.name',
                    'categories.color',
                    \DB::raw('count(*) as total'),
                ]
            )->join('categories', 'categories.id', '=', 'tickets.category')->where('tickets.created_by', \Auth::user()->createId())->groupBy('categories.id')->get();

            $chartData = [];
            $chartData['color'] = [];
            $chartData['name']  = [];
            $chartData['value'] = [];

            if (count($categoriesChart) > 0) {
                foreach ($categoriesChart as $category) {
                    $chartData['name'][]  = $category->name;
                    $chartData['value'][] = $category->total;
                    $chartData['color'][] = $category->color;
                }
            }

            $monthData = [];
            $barChart  = Ticket::select(
                [
                    \DB::raw('MONTH(created_at) as month'),
                    \DB::raw('YEAR(created_at) as year'),
                    \DB::raw('count(*) as total'),
                ]
            )->where('created_at', '>', \DB::raw('DATE_SUB(NOW(),INTERVAL 1 YEAR)'))->groupBy(
                [
                    \DB::raw('MONTH(created_at)'),
                    \DB::raw('YEAR(created_at)'),
                ]
            )->get();


            $users = User::find(\Auth::user()->createId());
            $plan = Plan::find($users->plan);
            if($users->storage_limit > 0)
            {
                 $storage_limit = ($users->storage_limit / $plan->storage_limit) * 100;
                 $storage_limit = number_format($storage_limit , 2);

            }
            else
            {
                 $storage_limit = 0;
            }


            $start = \Carbon\Carbon::now()->startOfYear();

            for ($i = 0; $i <= 11; $i++) {

                $monthData[$start->format('M')] = 0;
                foreach ($barChart as $chart) {
                    if (intval($chart->month) == intval($start->format('m'))) {
                        $monthData[$start->format('M')] = $chart->total;
                    }
                }
                $start->addMonth();
            }

            $user                       = \Auth::user();
            $user['total_user']         = $user->countCompany();
            $user['total_paid_user']    = $user->countPaidCompany();
            $user['total_orders']       = Order::total_orders();
            $user['total_orders_price'] = Order::total_orders_price();
            $user['total_plan']         = Plan::total_plan();
            $user['most_purchese_plan'] = (!empty(Plan::most_purchese_plan()) ? Plan::most_purchese_plan()->total : 0);
            $chartDatas                  = $this->getOrderChart(['duration' => 'week']);

            return view('admin.dashboard.index', compact('categories', 'open_ticket', 'close_ticket', 'agents', 'chartData', 'monthData', 'user', 'chartDatas','storage_limit','plan'));
        } else {
            return redirect()->route('login');
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime("-1 week +1 day");
                for ($i = 0; $i < 7; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-m', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach ($arrDuration as $date => $label) {
            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = __($label);
            $arrTask['data'][]  = $data->total;
        }
        return $arrTask;
    }
}
