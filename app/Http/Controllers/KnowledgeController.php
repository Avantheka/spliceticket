<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Knowledge;
use App\Models\Knowledgebasecategory;

class KnowledgeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin") {
        if($user->can('manage-knowledge')) {

            $knowledges = Knowledge::where('knowledge.created_by',\Auth::user()->createId());
            $knowledges = $knowledges->get();
            return view('admin.knowledge.index', compact('knowledges'));
        }else{
            return view('403');
        }
       }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = Knowledgebasecategory::get();
        return view('admin.knowledge.create',compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if($user->can('create-knowledge')) {
            $validation = [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required'],
                'category' => ['required', 'string', 'max:255'],
            ];
            $request->validate($validation);

            $post = [
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'created_by' => \Auth::user()->createId(),
            ];

            Knowledge::create($post);
            return redirect()->route('knowledge')->with('success',  __('Knowledge created successfully'));
        }else{
            return view('403');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $userObj = \Auth::user();
        if($userObj->can('edit-knowledge')) {
            $knowledge = Knowledge::find($id);
            $category = Knowledgebasecategory::get();
            return view('admin.knowledge.edit', compact('knowledge','category'));
        }else{
            return view('403');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $userObj = \Auth::user();
        if($userObj->can('edit-knowledge')) {
            $knowledge = Knowledge::find($id);
            $knowledge->update($request->all());
            return redirect()->route('knowledge')->with('success', __('Knowledge updated successfully'));
        }
        else{
            return view('403');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return redirect()->back()->with('error', __('This operation is not perform due to demo mode.'));

        $user = \Auth::user();
        if($user->can('delete-knowledge')) {
            $knowledge = Knowledge::find($id);
            $knowledge->delete();
            return redirect()->route('knowledge')->with('success', __('Knowledge deleted successfully'));
        }else{
            return view('403');
        }
    }

}
