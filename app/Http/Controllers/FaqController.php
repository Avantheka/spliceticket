<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class FaqController extends Controller
{

    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->type == "Admin") {
        if($user->can('manage-faq'))
        {
            $faqs = Faq::where('faqs.created_by',\Auth::user()->createId())->get();
            return view('admin.faq.index', compact('faqs'));
        }
        else{
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
        $user = \Auth::user();
        if($user->can('create-faq')) {
            return view('admin.faq.create');
        }else{
            return view('403');
        }
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
                'description' => ['required'],
            ];

            $request->validate($validation);

            $post = [
                'title' => $request->title,
                'description' => $request->description,
                'created_by' => \Auth::user()->createId(),
            ];

            Faq::create($post);
            return redirect()->route('faq')->with('success',  __('Faq created successfully'));
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
            $faq = Faq::find($id);
            return view('admin.faq.edit', compact('faq'));
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
            $faq = Faq::find($id);
            $faq->update($request->all());
            return redirect()->route('faq')->with('success', __('Faq updated successfully'));
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
            $faq = Faq::find($id);
            $faq->delete();
            return redirect()->route('faq')->with('success', __('Faq deleted successfully'));
        }else{
            return view('403');
        }
    }
}
