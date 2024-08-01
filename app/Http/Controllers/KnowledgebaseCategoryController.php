<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Knowledgebasecategory;

class KnowledgebaseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        if($user->can('manage-knowledge')) {
            $knowledges_category = Knowledgebasecategory::get();

            return view('admin.knowledgecategory.index', compact('knowledges_category'));
        }else{
            return view('403');
        }
        return view('admin.knowledgecategory.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.knowledgecategory.create');
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
        if($user->can('create-faq')) {
            $validation = [
                'title' => ['required', 'string', 'max:255'],
            ];
            $request->validate($validation);

            $post = [
                'title' => $request->title,
            ];

            Knowledgebasecategory::create($post);
            return redirect()->route('knowledgecategory')->with('success',  __('KnowledgeBase Category created successfully'));
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
        if($userObj->can('edit-faq')) {
            $knowledge_category = Knowledgebasecategory::find($id);
            return view('admin.knowledgecategory.edit', compact('knowledge_category'));
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
        if($userObj->can('edit-faq')) {
            $knowledge_category = Knowledgebasecategory::find($id);
            $knowledge_category->update($request->all());
            return redirect()->route('knowledgecategory')->with('success', __('KnowledgeBase Category updated successfully'));
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
        $user = \Auth::user();
        if($user->can('delete-faq')) {
            $knowledge_category = Knowledgebasecategory::find($id);
            $knowledge_category->delete();
            return redirect()->route('knowledgecategory')->with('success', __('KnowledgeBase Category deleted successfully'));
        }else{
            return view('403');
        }
    }

}
