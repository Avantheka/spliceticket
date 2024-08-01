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
class FedapayPaymentController extends Controller
{
    public function planPaywithFedapay(Request $request)
    {

        $payment_setting = Utility::payment_settings();
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'XOF';
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $authuser = Auth::user();

        if ($plan) {
            $get_price = $plan->price;
            $price = intval($get_price);

            // try{
            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $price = $plan->price - $discount_value;
                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = Auth::user()->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $orderID;
                    $userCoupon->save();
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }
            try {
                $fedapay = isset($payment_setting['fedapay_secret_key']) ? $payment_setting['fedapay_secret_key'] : '';
                $fedapay_mode = !empty($payment_setting['fedapay_mode']) ? $payment_setting['fedapay_mode'] : 'sandbox';
                \FedaPay\FedaPay::setApiKey($fedapay);

                \FedaPay\FedaPay::setEnvironment($fedapay_mode);

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $transaction = \FedaPay\Transaction::create([
                    "description" => "Fedapay Payment",
                    "amount" => $price,
                    "currency" => ["iso" => $currency],

                    "callback_url" => route('plan.get.fedapay.status', [
                        'order_id' => $orderID,
                        'plan_id' => $plan->id,
                        'coupon_code' => !empty($request->coupon) ? $request->coupon : '',
                        'net_price' => $price,
                    ]),
                    "cancel_url" => route('plan.get.fedapay.status', [
                        'order_id' => $orderID,
                        'plan_id' => $plan->id,
                        'coupon_code' => !empty($request->coupon_code) ? $request->coupon_code : '',
                    ]),

                ]);

                $token = $transaction->generateToken();

                return redirect($token->url);
            } catch (\Exception $e) {
                return redirect()->route('plan.index')->with('error', $e->getMessage());
            }
        } else {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }



    public function planGetFedapayStatus(Request $request, $plan_id)
    {
   
            if ($request->status == "approved") {
                $data = request()->all();
                $getAmount = $request->net_price;
                $authuser = Auth::user();
                $plan = Plan::find($plan_id);
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                Order::create(
                    [
                        'order_id' => $orderID,
                        'name' => null,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $getAmount,
                        'price_currency' => isset($admin_settings['defult_currancy']) ? $admin_settings['defult_currancy'] : '',
                        'txn_id' => '',
                        'payment_type' => __('Fedapay'),
                        'payment_status' => 'succeeded',
                        'receipt' => null,
                        'user_id' => $authuser->id,
                    ]
                );


                $assignPlan = $authuser->assignPlan($plan->id);

                $coupons = Coupon::where('code', $request->coupon_code)->first();

                if (!empty($request->coupon_code)) {
                    if (!empty($coupons)) {
                        $userCoupon = new UserCoupon();
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

                if ($assignPlan['is_success']) {
                    Utility::referraltransaction($plan);
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                } else {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }
            } else {

                return redirect()->route('plan.index')->with('error', __('Payment failed'));
            }

    }
}
