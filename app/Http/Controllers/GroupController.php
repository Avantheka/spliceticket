<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\OperatingHours;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin")
        {
            $groups = Group::where('created_by',\Auth::user()->id)->get();
            return view('admin.groups.index',compact('groups'));
        }
        else
        {
            return view('403');
        }
    }

    public function create()
    {
         $user = \Auth::user();
         $users = User::where('parent',\Auth::user()->createId())->get()->pluck('name','id');

         $users->users  = explode(',', $user->users);


         $opeatings = OperatingHours::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');




         $leader = User::where('parent',\Auth::user()->createId())->get()->pluck('name','id');
         $leader->leader  = explode(',', $user->leader);

         if(\Auth::user()->type == 'Admin')
         {
             return view('admin.groups.create',compact('users','leader','opeatings'));
         }
         else
         {
             return redirect()->back()->with('error', __('Permission denied.'));
         }

    }

    public  function store(Request $request)
    {
        $user = \Auth::user();

        if(\Auth::user()->type == "Admin")
        {

            $validation = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'operating_hours' => 'required|string|max:255',
                'users' => 'required|string|max:255',
                'leader' => 'required|string|max:100',
                'description' => 'required',
            ];

            // $validator = \Validator::make($request->all(), $validation);
            // if ($validator->fails()) {
            //     $messages = $validator->getMessageBag();
            //     return redirect()->back()->with('error', $messages->first());
            // }

            $post = [
                'name' => $request->name,
                'email' => $request->email,
                'operating_hours' => $request->operating_hours,
                'users'     => implode(",", $request->users),
                'leader' =>  implode(",", $request->leader),
                'description' => $request->description,
                'created_by' => \Auth::user()->createId(),
            ];

            Group::create($post);

            return redirect()->route('group')->with('success', __('Group created successfully'));

        }
    }

     public function edit($id)
     {
        $userObj = \Auth::user();
        $users = User::where('parent',\Auth::user()->createId())->get()->pluck('name','id');
        $users->users  = explode(',', $userObj->users);
        $opeatings = OperatingHours::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');

        $leader = User::where('parent',\Auth::user()->createId())->get()->pluck('name','id');
        $leader->leader  = explode(',', $userObj->leader);

        if(\Auth::user()->type == 'Admin')
        {
            $groups = Group::find($id);


            return view('admin.groups.edit',compact('groups','users','leader','opeatings'));

        }
        else
        {
            return view('403');
        }
     }

     public function update(Request $request, $id)
     {
        $userObj = \Auth::user();
        if(\Auth::user()->type == 'Admin')
        {
            $groups = Group::find($id);
            $groups->name = $request->name;
            $groups->email = $request->email;
            $groups->operating_hours = $request->operating_hours;
            $groups->users = implode(",", $request->users);
            $groups->leader =  implode(",", $request->leader);
            $groups->description = $request->description;


            $groups->save();
            return redirect()->route('group')->with('success', __('Group updated successfully'));

        }
        else
        {
            return view('403');
        }
     }

     public function destroy($id)
      {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin")
        {
            $groups = Group::find($id);
            $groups->delete();

            return redirect()->back()->with('success', __('Group deleted successfully'));
        }
        else
        {
            return view('403');
        }
      }
}
