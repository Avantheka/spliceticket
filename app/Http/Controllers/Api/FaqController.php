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
use App\Models\Api\Faq;


class FaqController extends Controller
{
    use ApiResponser;

    public function indexs(Request $request)
    {

        $user = \Auth::user();


        $faqs = Faq::query();


        if($request->search){

            $faqs->where('title', 'like', "%{$request->search}%");
        }


        if($user->plan){
            $faqs->where('created_by', $user->id);
        }

        $faqs = $faqs->paginate(2);



        $data = [
            'faq'=>$faqs,
        ];

        return $this->success($data);
    }

    public function store(Request $request)
    {

        $user = \Auth::user();
        if($request->id == null){

            $validation = [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required'],
            ];
            $request->validate($validation);

            $post = [
                'title' => $request->title,
                'description' => $request->description,
            ];


            $post['created_by'] = $user->id;


            $faq = Faq::create($post);

            $data = [
                'faq' =>$faq
            ];

            return $this->success($data);

        }else{

            $faq = Faq::find($request->id);

            $faq->title = $request->title;
            $faq->description = $request->description;
            $faq->created_by =  $user->id;
            $faq->save();

             $data = [
                'faq' =>$faq
            ];

            return $this->success($data);
        }
    }

    public function destroy(Request $request)
    {
        $faq = Faq::find($request->id);
        $faq->delete();

        $data = [
            'faq'=>[],
        ];

        return $this->success($data);
    }


}
