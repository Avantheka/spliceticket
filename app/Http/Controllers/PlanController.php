<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;

class PlanController extends Controller
{

    public function index()
    {

         if (\Auth::user()->parent == 0 || \Auth::user()->parent == 1)
        {
            if(\Auth::user()->parent == 0)
            {
                $plans = Plan::get();
            }
            else{
                $plans = Plan::where('plan_disable',1)->get();
            }
            $payment_setting = Utility::set_payment_settings();

            return view('plan.index', compact('plans','payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->parent == 0)
        {
            $arrDuration = Plan::$arrDuration;

            return view('plan.create', compact('arrDuration'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {

        if (\Auth::user()->parent == 0)
        {
            $payment = Utility::set_payment_settings();
            if(count($payment)>0 || $request->price <= 0){

                $validation                = [];
                $validation['name']        = 'required|unique:plans';
                $validation['price']       = 'required|numeric|min:0';
                $validation['duration']    = 'required';
                $validation['max_agent']    = 'required|numeric';
                $validation['description']   = 'max:100';
                $validation['storage_limit']    = 'required';


                if($request->image)
                {
                    $validation['image'] = 'required|max:20480';
                }
                $request->validate($validation);
                $post = $request->all();

                if($request->hasFile('image'))
                {
                    $filenameWithExt = $request->file('image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = 'plan_' . time() . '.' . $extension;

                    $dir = storage_path('uploads/plan/');
                    if(!file_exists($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    $path          = $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);
                    $post['image'] = $fileNameToStore;
                }

                // if (!isset($request->enable_custdomain)) {
                //     $post['enable_custdomain'] = 'off';
                // }

                // if (!isset($request->enable_custsubdomain)) {
                //     $post['enable_custsubdomain'] = 'off';
                // }

                // if (!isset($request->enable_chatgpt)) {
                //     $post['enable_chatgpt'] = 'off';
                // }


                if(Plan::create($post))
                {

                    return redirect()->back()->with('success', __('Plan Successfully created.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Please set payment api key & secret key for add new plan.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($plan_id)
    {
        if (\Auth::user()->parent == 0)
        {
            $arrDuration = Plan::$arrDuration;
            $plan        = Plan::find($plan_id);

            return view('plan.edit', compact('plan', 'arrDuration'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $plan_id)
    {
        if (\Auth::user()->parent == 0)
        {
            $plan = Plan::find($plan_id);

            $payment = Utility::set_payment_settings();
            if(count($payment)>0 || $request->price <= 0){
                if(!empty($plan))
                {
                    $validation                = [];
                    $validation['name']        = 'required|unique:plans,name,' . $plan_id;
                    $validation['duration']    = 'required';
                    $validation['max_agent']    = 'required|numeric';

                    $request->validate($validation);

                    $post = $request->all();


                    if($request->hasFile('image'))
                    {
                        $filenameWithExt = $request->file('image')->getClientOriginalName();
                        $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension       = $request->file('image')->getClientOriginalExtension();
                        $fileNameToStore = 'plan_' . time() . '.' . $extension;

                        $dir = storage_path('uploads/plan/');
                        if(!file_exists($dir))
                        {
                            mkdir($dir, 0777, true);
                        }
                        $image_path = $dir . '/' . $plan->image;  // Value is not URL but directory file path
                        if(\File::exists($image_path))
                        {

                            chmod($image_path, 0755);
                            \File::delete($image_path);
                        }
                        $path = $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);

                        $post['image'] = $fileNameToStore;
                    }

                    if (!isset($request->enable_custdomain)) {
                        $post['enable_custdomain'] = 'off';
                    }

                    if (!isset($request->enable_custsubdomain)) {
                        $post['enable_custsubdomain'] = 'off';
                    }

                    if (!isset($request->enable_chatgpt)) {
                        $post['enable_chatgpt'] = 'off';
                    }

                    if($plan->update($post))
                    {
                        return redirect()->back()->with('success', __('Plan successfully updated.'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Something is wrong.'));
                    }
                }
                else
                {
                    return redirect()->back()->with('error', __('Plan not found.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Please set payment api key & secret key for add new plan.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function userPlan(Request $request)
    {
        $objUser = \Auth::user();
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($request->code);
        $plan    = Plan::find($planID);
        if($plan)
        {
            if($plan->price <= 0)
            {
                $objUser->assignPlan($plan->id);

                return redirect()->route('plans.index')->with('success', __('Plan successfully activated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Plan not found.'));
        }
    }

    public function getpaymentgatway($code)
    {
       $plan_id = \Illuminate\Support\Facades\Crypt::decrypt($code);
        $plan    = Plan::find($plan_id);
        if($plan)
        {
            $admin_payment_setting = Utility::payment_settings();
            return view('plan/payments', compact('plan','admin_payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Plan is deleted.'));
        }
    }

    public function planTrial($plan)
    {
        $objUser = \Auth::user();
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);

        if($plan)
        {
            if($plan->price > 0)
            {
                $user = User::find($objUser->id);

                $user->trial_plan = $planID;
                $currentDate = date('Y-m-d');
                $numberOfDaysToAdd = $plan->trial_days;

                $newDate = date('Y-m-d', strtotime($currentDate . ' + ' . $numberOfDaysToAdd . ' days'));
                $user->trial_expire_date = $newDate;
                $user->save();

                $objUser->assignPlan($planID);

                return redirect()->route('plan.index')->with('success', __('Plan successfully activated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Plan not found.'));
        }
    }

    public function PlanUnable(Request $request)
    {
        $users = User::where('plan',$request->id)->get();
        $plan = Plan::find($request->id);
        if($plan->plan_disable == 1)
        {
            if(count($users) == 0)
            {
                    $plan->plan_disable = 0;
                    $plan->save();
                    return response()->json(['success' => __('Plan Successfully Unable.')]);


            }
            else {
                return response()->json(['error' => __('The admin has subscribed to this plan, so it cannot be unable.')]);
            }
        }
        else
            {
                $plan->plan_disable = 1;
                $plan->save();
                return response()->json(['success' => __('Plan Successfull Disable.')]);

        }

    }
    public function destroy($plan_id)
    {
        $users = User::where('plan',$plan_id)->get();
        if(count($users) == 0)
        {
            $plan = Plan::find($plan_id);
            $plan->delete();
            return redirect()->route('plan.index')->with('success', __('Plan deleted successfully'));
        }else {
            return redirect()->back()->with('error',__('The admin has subscribed to this plan, so it cannot be deleted.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }



}
