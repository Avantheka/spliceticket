<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NepalstePaymentController extends Controller
{
    public function planPayWithnepalste(Request $request)
    {
        $payment_setting = Utility::payment_settings();
        $api_key = isset($payment_setting['nepalste_public_key']) ? $payment_setting['nepalste_public_key'] : '';
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'NPR';

        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();

        if ($plan) {
            $get_amount = $plan->price;
            if (!empty($request->coupon))
            {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $coupon_id = $coupons->id;
                    if( $get_amount<1){
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        $statuses = 'success';
                        if ($coupon_id != '') {
                            $userCoupon         = new UserCoupon();//UsersCoupons
                            $userCoupon->user = $user->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order = $orderID;
                            $userCoupon->save();
                            $usedCoupun = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupun) {
                                $coupons->is_active = 0;
                                $coupons->save();
                            }

                        }
                        //$user = Auth::user();

                        $order                 = new Order();
                        $order->order_id       = $orderID;
                        $order->name           = $user->name;
                        $order->card_number    = '';
                        $order->card_exp_month = '';
                        $order->card_exp_year  = '';
                        $order->plan_name      = $plan->name;
                        $order->plan_id        = $plan->id;
                        $order->price          =  $get_amount;
                        $order->price_currency = $currency;
                        $order->txn_id         = '';
                        $order->payment_type   = __('Neplaste');
                        $order->payment_status = $statuses;
                        $order->receipt        = '';
                        $order->user_id        = $user->id;

                        $order->save();

                        $assignPlan = $user->assignPlan($plan->id);

                        if($assignPlan['is_success'])
                        {
                            return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                        }
                        else
                        {
                            return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                        }

                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }

            }
            $response = ['orderId' => $orderID, 'user' => $user, 'get_amount' => $get_amount, 'plan' => $plan, 'currency' => $currency, 'coupon_id'=>$request->coupon];
            $api_key = isset($payment_setting['nepalste_public_key']) ? $payment_setting['nepalste_public_key'] : '';


        }

        $parameters = [
            'identifier' => 'DFU80XZIKS',
            'currency' => $currency,
            'amount' => $get_amount,
            'details' => $plan->name,
            'ipn_url' => route('nepalste.status',$response),
            'cancel_url' => route('nepalste.cancel'),
            'success_url' => route('nepalste.status',$response),
            'public_key' => $api_key,
            'site_logo' => 'https://nepalste.com.np/assets/images/logoIcon/logo.png',
            'checkout_theme' => 'dark',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
        ];


        //test end point
        $url = "https://nepalste.com.np/sandbox/payment/initiate";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if(isset($result['success'])){
            return redirect($result['url']);
        }else{
            return redirect()->back()->with('error',__($result['message']));
        }
    }



    public function planGetNepalsteStatus(Request $request)
    {
       
        $payment_setting = Utility::payment_settings();
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : '';

        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        $getAmount = $request->get_amount;
        $authuser = \Auth::user();
        $plan = Plan::find($request->plan);

            $order = new Order();
            $order->order_id = $orderID;
            $order->name = $authuser->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $getAmount;
            $order->price_currency = $currency;
            // $order->txn_id = $orderID;
            $order->payment_type = __('Neplaste');
            $order->payment_status = 'success';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $authuser->id;
            $order->save();

            $assignPlan = $authuser->assignPlan($plan->id);

            $coupons = Coupon::where('code',$request->coupon_id)->first();

            if (!empty($request->coupon_id)) {
                if (!empty($coupons)) {
                    $userCoupon         = new UserCoupon();//UsersCoupons
                    $userCoupon->user = $authuser->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $orderID;
                    $userCoupon->save();
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                }
            }

            if ($assignPlan['is_success'])
            {
                Utility::referraltransaction($plan);
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
            } else
            {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
    }

    public function planGetNepalsteCancel(Request $request)
    {
        return redirect()->back()->with('error',__('Transaction has failed'));
    }
}
