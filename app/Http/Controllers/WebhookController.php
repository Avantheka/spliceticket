<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Webhook;

class WebhookController extends Controller
{
    //
    //  public function index()
    // {
    //     $webhooks = Webhook::where('created_by', Auth::user()->id)->get();
    //     return view('settings.index', compact('webhooks'));
    // }
    public function create()
    {
        $module = [
            'New User' => 'New User ', 'New Ticket' => 'New Ticket ', 'New Ticket Reply' => 'New Ticket Reply'
        ];
        $method = ['GET' => 'GET', 'POST' => 'POST'];
        return view('admin.webhook.create', compact('module', 'method'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required',
                'url' => 'required|url',
                'method' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $webhook = new Webhook();
        $webhook->module = $request->module;
        $webhook->url = $request->url;
        $webhook->method = $request->method;
        $webhook->created_by = \Auth::user()->id;
        // dd($webhook);
        $webhook->save();
        return redirect()->back()->with('success', __('Webhook Successfully Created.'));
    }

    public function edit(Request $request, $id)
    {
        $module = [
            'New User' => 'New User ', 'New Ticket' => 'New Ticket ', 'New Ticket Reply' => 'New Ticket Reply'
        ];
        $method = ['GET' => 'GET', 'POST' => 'POST'];
        $webhook = Webhook::find($id);
        return view('admin.webhook.edit', compact('webhook', 'module', 'method'));
    }
    public function update(Request $request, $id)
    {
        //
        // dd($request->all());
                $webhook['module']       = $request->module;
                $webhook['url']       = $request->url;
                $webhook['method']      = $request->method;
                $webhook['created_by'] = \Auth::user()->id;
                Webhook::where('id', $id)->update($webhook);
                return redirect()->back()->with('success', __('Webhook Setting Succssfully Updated'));
    }
    public function destroy($id)
    {
        $webhook = Webhook::find($id);
        $webhook->delete();
        return redirect()->back()->with('success', __('Webhook successfully deleted.'));
    }

}
