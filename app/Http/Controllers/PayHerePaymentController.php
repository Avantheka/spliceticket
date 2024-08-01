<?php

namespace App\Http\Controllers;


use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\User;
use Lahirulhr\PayHere\PayHere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayHerePaymentController extends Controller
{
    public function planPayWithPayHere(Request $request)
    {
        $payment_setting = Utility::payment_settings();
        $payhere_merchant_secret_key = isset($payment_setting['payhere_merchant_secret_key']) ? $payment_setting['payhere_merchant_secret_key'] : '';
        $payhere_merchant_id = isset($payment_setting['payhere_merchant_id']) ? $payment_setting['payhere_merchant_id'] : '';
        $payhere_app_id = isset($payment_setting['payhere_app_id']) ? $payment_setting['payhere_app_id'] : '';
        $payhere_app_secret_key = isset($payment_setting['payhere_app_secret_key']) ? $payment_setting['payhere_app_secret_key'] : '';
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'LKR';

        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();
        if ($plan) {
            $price = $plan->price;
            $get_amount = intval($price);

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();

                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    if ($coupons->limit == $usedCoupun) {

                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }

                    $coupon_id = $coupons->id;

                    if ($get_amount < 1) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        $statuses = 'success';
                        if ($coupon_id != '') {


                            $userCoupon = new UserCoupon();
                            $userCoupon->user = Auth::user()->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order = $orderID;
                            $userCoupon->save();
                            $usedCoupun = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupun) {
                                $coupons->is_active = 0;
                                $coupons->save();
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
                        $order->price          =  $get_amount;
                        $order->price_currency = $currency;
                        $order->txn_id         = '';
                        $order->payment_type   = __('PayHere');
                        $order->payment_status = $statuses;
                        $order->receipt        = '';
                        $order->user_id        = $user->id;

                        $order->save();

                        $assignPlan = $user->assignPlan($plan->id);

                        if ($assignPlan['is_success']) {
                            return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                        } else {
                            return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                        }
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }

            // $call_back = route('plan.get.payhere.status', [
            //     $plan->id,
            // ]);
            try {

                $config = [
                    'payhere.api_endpoint' =>  $payment_setting['payhere_mode'] === 'sandbox'
                        ? 'https://sandbox.payhere.lk/'
                        : 'https://www.payhere.lk/',
                ];

                $config['payhere.merchant_id'] =  $payment_setting['payhere_merchant_id'] ?? '';
                $config['payhere.merchant_secret'] =  $payment_setting['payhere_merchant_secret_key'] ?? '';
                $config['payhere.app_secret'] =  $payment_setting['payhere_app_secret_key'] ?? '';
                $config['payhere.app_id'] =  $payment_setting['payhere_app_id'] ?? '';
                config($config);


                $hash = strtoupper(
                    md5(
                        $payment_setting['payhere_merchant_id'] .
                            $orderID .
                            number_format($get_amount, 2, '.', '') .
                            'LKR' .
                            strtoupper(md5($payment_setting['payhere_merchant_secret_key']))
                    )
                );
                $call_back = route('plan.get.payhere.status', [
                    $plan->id,
                    'amount' => $get_amount,
                    'coupon_code' => $request->coupon_code,
                ]);

                $data = [
                    'first_name' => $user->name,
                    'last_name' => '',
                    'email' => $user->email,
                    'phone' => $user->mobile_no ?? '',
                    'address' => 'Main Rd',
                    'city' => 'Anuradhapura',
                    'country' => 'Sri lanka',
                    'order_id' => $orderID,
                    'items' => $plan->name ?? 'Free Plan',
                    'currency' => 'LKR',
                    'amount' => $get_amount,
                    'hash' => $hash,
                    'return_url' =>$call_back,
                ];
                // dd($call_back);
                return PayHere::checkOut()
                    ->data($data)
                    ->successUrl(route('plan.get.payhere.status', [
                        $plan->id,
                        'amount' => $get_amount,
                        'coupon_code' => $request->coupon_code,
                    ]))
                    ->failUrl(route('plan.get.payhere.status', [
                        $plan->id,
                        'amount' => $get_amount,
                        'coupon_code' => $request->coupon_code,
                    ]))
                    ->renderView();
            } catch (\Exception $e) {
                dd($e);
                \Log::debug($e->getMessage());
                return redirect()->route('plan.index')->with('error', $e->getMessage());
            }
        } else {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetPayHereStatus(Request $request, $plan_id)
    {

            if ($request->status == "approved") {
                $user = Auth::user();
                $plan = Plan::find($plan_id);
                $price = $plan->price;
                $get_amount = intval($price);
                $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'LKR';

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $order = Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' => !empty($plan->name) ? $plan->name : 'Basic Package',
                            'plan_id' => $plan->id,
                            'price' => $get_amount,
                            'price_currency' => $currency,
                            'txn_id' => '',
                            'payment_type' => __('PayHere'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $user->id,
                        ]
                    );
                    $type = 'Subscription';
                    $user = User::find($user->id);
                    $assignPlan = $user->assignPlan($plan->id);
                    $coupons = Coupon::where('code', $request->coupon_code)->first();

                    if (!empty($request->coupon_code)) {
                        if (!empty($coupons)) {
                            $userCoupon = new UserCoupon();
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
                    }

                    if ($assignPlan['is_success']) {
                        Utility::referraltransaction($plan);
                        return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                    } else {
                        return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                    }
                } catch (\Exception $e) {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed.'));
                }
            } else {
                return redirect()->route('plan.index')->with('error', __('Payment failed'));

            }

    }
}
