<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Priority;
use App\Models\Policies;

class PriorityController extends Controller
{
    //
    public function index(Request $request)
    {
        if(\Auth::user()->type == 'Admin')
        {
            $priority = Priority::where('created_by',\Auth::user()->id)->get();

            return view('admin.priority.index',compact('priority'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $user = \Auth::user();
        if(\Auth::user()->type == 'Admin')
        {
            return view('admin.priority.create');
        }
        else
        {
            return view('403');
        }

    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if(\Auth::user()->type == 'Admin')
        {
           $validation = [
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:255',
          ];
          $priority = new Priority();
          $priority->name = $request->name;
          $priority->color = $request->color;
          $priority->created_by = \Auth::user()->createId();
          $priority->save();


          $policies = new Policies();
          $policies->priority_id = $priority->id;
          $policies->response_time = 'Hour';
          $policies->resolve_time = 'Hour';
          $policies->created_by = \Auth::user()->createId();
          $policies->save();

          return redirect()->route('priority.index')->with('success', __('Priority created successfully'));
        }
        else{
            return view('403');
        }

    }

    public function edit($id)
    {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin")
        {
            $priority = Priority::find($id);

            return view('admin.priority.edit', compact('priority'));

        }
        else
        {
            return view('403');
        }
    }

    public function update(Request $request,$id)
    {
        $userObj = \Auth::user();
        if(\Auth::user()->type == 'Admin')
        {
            $priority = Priority::find($id);
            $priority->name = $request->name;
            $priority->color = $request->color;
            $priority->save();
            return redirect()->route('priority.index')->with('success', __('Priority updated successfully'));

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
            $priority = Priority::find($id);
            $policies = Policies::find($id);
            $priority->delete();

            return redirect()->back()->with('success', __('Priority deleted successfully'));
        }
        else
        {
            return view('403');
        }
    }

}



