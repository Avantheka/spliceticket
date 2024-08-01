<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use YooKassa\Client;

class YooKassaController extends Controller
{
    public function planPayWithYooKassa(Request $request)
    {
        $payment_setting = Utility::payment_settings();
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret'];
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';


        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authuser = Auth::user();
        $plan = Plan::find($planID);
        if ($plan) {

            $get_amount = $plan->price;


            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();

                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();

                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;

                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    if ($get_amount <= 0) {
                        $authuser = \Auth::user();
                        $authuser->plan = $plan->id;
                        $authuser->save();
                        $assignPlan = $authuser->assignPlan($plan->id);
                        if ($assignPlan['is_success'] == true && !empty($plan)) {
                            if (!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '') {
                                try {
                                    $authuser->cancel_subscription($authuser->id);
                                } catch (\Exception $exception) {
                                    \Log::debug($exception->getMessage());
                                }
                            }
                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                            $userCoupon = new UserCoupon();
                            $userCoupon->user = $authuser->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order = $orderID;
                            $userCoupon->save();
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
                                    'price' => $get_amount == null ? 0 : $get_amount,
                                    'price_currency' => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                                    'txn_id' => '',
                                    'payment_type' => 'YooKassa',
                                    'payment_status' => 'success',
                                    'receipt' => null,
                                    'user_id' => $authuser->id,
                                ]
                            );


                            $assignPlan = $authuser->assignPlan($plan->id);
                            return redirect()->route('plan.index')->with('success', __('Plan Successfully Activated'));
                        }
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }

            try {

                if (is_int((int)$yookassa_shop_id)) {
                    $client = new Client();
                    $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $product = !empty($plan->name) ? $plan->name : 'Life time';
                    $payment = $client->createPayment(
                        array(
                            'amount' => array(
                                'value' => $get_amount,
                                'currency' => $currency,
                            ),
                            'confirmation' => array(
                                'type' => 'redirect',
                                'return_url' => route('plan.get.yookassa.status', [$plan->id, 'order_id' => $orderID, 'price' => $get_amount]),
                            ),
                            'capture' => true,
                            'description' => 'Заказ №1',
                        ),
                        uniqid('', true)
                    );

                    $authuser = \Auth::user();
                    $authuser->plan = $plan->id;
                    $authuser->save();


                    if (!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '') {
                        try {
                            $authuser->cancel_subscription($authuser->id);
                        } catch (\Exception $exception) {
                            Log::debug($exception->getMessage());
                        }
                    }

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
                            'price' => $get_amount == null ? 0 : $get_amount,
                            'price_currency' => $currency,
                            'txn_id' => '',
                            'payment_type' => __('YooKassa'),
                            'payment_status' => 'success',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );


                    Session::put('payment_id', $payment['id']);

                    if ($payment['confirmation']['confirmation_url'] != null) {
                        return redirect($payment['confirmation']['confirmation_url']);
                    } else {
                        return redirect()->route('plans.index')->with('error', 'Something went wrong, Please try again');
                    }

                    // return redirect()->route('plans.index')->with('success', __('Plan Successfully Activated'));

                } else {
                    return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
                }
            }
            catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Currency Not Supported.Contact To Your Site Admin');
            }
        }
    }
    public function planGetYooKassaStatus(Request $request, $planId)
    {
        $payment_setting = Utility::payment_settings();
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret'];
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';

        if (is_int((int)$yookassa_shop_id)) {
            $client = new Client();
            $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
            $paymentId = Session::get('payment_id');
            Session::forget('payment_id');
            if ($paymentId == null) {
                return redirect()->back()->with('error', __('Transaction Unsuccesfull'));
            }

            $payment = $client->getPaymentInfo($paymentId);

            if (isset($payment) && $payment->status == "succeeded") {

                $plan = Plan::find($planId);
                $user = \Auth::user();
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $Order                 = Order::where('order_id', $request->order_id)->first();
                    $Order->payment_status = 'succeeded';
                    $Order->save();

                    $assignPlan = $user->assignPlan($plan->id);
                    $coupons = Coupon::find($request->coupon_id);

                    if (!empty($request->coupon_id)) {
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
                    return redirect()->route('plan.index')->with('error', __($e->getMessage()));
                }
            } else {
                return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
            }
        }
    }
}
