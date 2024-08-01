<?php

namespace App\Http\Controllers;


use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Settings;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class BanktransferController extends Controller
{
    //
    public $is_enabled;
    public $currancy;

    public function planPayWithBanktransfer(Request $request)
    {
            $validator = \Validator::make(
                $request->all(), [
                                    'payment_receipt' => 'required',
                                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());

            }


        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan           = Plan::find($planID);


        $authuser       = Auth::user();
        $coupon_id = '';
        $user = Auth::user();
        $orderID = time();
         if($request->payment_receipt)
                {

                    $filenameWithExt = $request->file('payment_receipt')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('payment_receipt')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $url = '';
                    $dir        = 'uploads/order';
                    $path = Utility::upload_file($request,'payment_receipt',$fileNameToStore,$dir,[]);

                    $post['payment_receipt'] = $fileNameToStore;
                }

        if ($plan)
        {

            $price = $plan->price;

            if(isset($request->coupon) && !empty($request->coupon))
            {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();


                if(!empty($coupons))
                {

                    $usedCoupun     = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $price          = $plan->price - $discount_value;



                    if($coupons->limit == $usedCoupun)
                    {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $coupon_id = $coupons->id;
                }
                else
                {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }

            }



            $order                 = new Order();
                    $order->order_id       = $orderID;
                    $order->name           = $user->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = $price;
                    $order->price_currency = !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : 'USD';
                    $order->txn_id         = isset($request->TXNID) ? $request->TXNID : '';
                    $order->payment_type   = __('Bank Transfer');
                    $order->payment_status = 'Pending';
                    $order->receipt        = $fileNameToStore;
                    $order->user_id        = $user->id;
                    $order->save();

                    if(!empty($request->coupon))
                    {
                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $authuser->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order  = $orderID;
                        $userCoupon->save();

                        $usedCoupun = $coupons->used_coupon();
                        if($coupons->limit <= $usedCoupun)
                        {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }

                    }

            return redirect()->route('plan.index')->with('success', __('Plan request send Successfully!'));
        }
    }

    public function action($id)
    {
        $order     = Order::find($id);
        $admin_payment_setting = Utility::payment_settings();

        return view('order.action', compact('order','admin_payment_setting'));
    }

    public function changeStatus(Request $request , $order_id)
    {


        $order = Order::find($request->order_id);

        if($request->status == 'Approval')
        {
            $plan       = Plan::find($order->plan_id);


            $authuser   = User::find($order->user_id);

            // $authuser       = \Auth::user();
            $authuser->plan = $plan->id;

            $assignPlan = $authuser->assignPlan($plan->id);
            Utility::referraltransaction($plan,$authuser);
            $order->payment_status           = 'Approved';
        }
        else
        {
            $order->payment_status           = 'Rejected';
        }

        $order->save();

        return redirect()->route('order.index')->with('success', __('Plan payment status updated successfully.'));
    }

    public function destroy(order $order)
    {
        if ($order) {
            $order->delete();
            return redirect()->back()->with('success', __('Order Successfully Deleted.'));
        } else {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
    }



}
