<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Api\User;
use App\Models\Api\Category;
use App\Models\Api\Ticket;
use App\Models\Api\Utility;


class CategoryController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {

       // $user = User::find($request['id']);
		   $user = \Auth::user();

        $categories = Category::select('id','name','color')->where('created_by',$user->id)->paginate(10);

          // Start Categories Analytics

          // Start Categories Analytics
            $categoriesChart = Ticket::select(
                [
                    'tickets.category',
                    'categories.name',
                    'categories.color',
                    \DB::raw('count(*) as total'),
                ]
            )->join('categories', 'categories.id', '=', 'tickets.category')->where('categories.created_by',$user->id)->where('tickets.created_by',$user->id)->groupBy('categories.id')->get();
		
		


        $total_cat_ticket   = Ticket::count();

        $chartData=[];
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


        $data = [
            'category' =>$categories,
            'category_analytics'=>$chartData,
        ];

        return response()->json([
            'status'=> 1,
            'message'=>'successfull',
            'data'=>$data
        ]);
    }

    public function getcategory(Request $request)
    {
        $categories = Category::getCategory($request->all());

        $data = [
            'category' =>$categories
        ];

        return $this->success($data);
    }



    public function store(Request $request)
    {

        $user = \Auth::user();
        if($request->id == null){
            $validation = [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'color' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ];
            $request->validate($validation);


            $post = [
                'name' => $request->name,
                'color' => $request->color,
                'created_by'=>$user->id
            ];
            $category = Category::create($post);


            $data = [
                'category' =>$category
            ];

            return response()->json([
                'status'=> 1,
                'message'=>'successfull',
                'data'=>$data
            ]);


        }
        else{


            $category        = Category::find($request['id']);
            $category->name  = $request->name;
            $category->color = $request->color;
            $category->created_by = $user->color;
            $category->save();


            $data = [
                'category' =>$category
            ];

            return response()->json([
                'status'=> 1,
                'message'=>'successfull',
                'data'=>$data
            ]);

        }



    }


    public function ticketStatus()
    {
        $status = Utility::status();

        $data = [
            'status' =>$status
        ];

        return response()->json([
            'status'=> 1,
            'message'=>'successfull',
            'data'=>$data
        ]);
    }

    public function destroy(Request $request)
    {
        $category = Category::find($request->id);
        $category->delete();

        $data = [
            'message'=>"Category delete successfully",
        ];

        return $this->success($data);
    }
}
