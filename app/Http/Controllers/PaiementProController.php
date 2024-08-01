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

class PaiementProController extends Controller
{
    public function planPayWithPaiementPro(Request $request)
    {
        $payment_setting = Utility::payment_settings();
        $merchant_id = isset($payment_setting['paiementpro_merchant_id']) ? $payment_setting['paiementpro_merchant_id'] : '' ;
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';

        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();
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

                    $coupon_id = $coupons->id;

                    if($get_amount <= 0){
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        $statuses = 'success';
                        if ($coupon_id != '') {


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
                        $order->payment_type   = __('Paiement Pro');
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
            $merchant_id = isset($payment_setting['paiementpro_merchant_id']) ? $payment_setting['paiementpro_merchant_id'] : '';

            $call_back = route('plan.get.paiementpro.status', [
                $plan->id,
            ]);
            $data = array(
                'merchantId' =>$merchant_id,
                'amount' =>  $get_amount,
                'description' => "Api PHP",
                'channel' => $request->channel,
                'countryCurrencyCode' => !empty($admin_settings['defult_currancy'])?$admin_settings['defult_currancy']:'',
                'referenceNumber' => "REF-" . time(),
                'customerEmail' => $user->email,
                'customerFirstName' => $user->name,
                'customerLastname' =>  $user->name,
                'customerPhoneNumber' => $request->mobile_number,
                'notificationURL' => $call_back,
                'returnURL' => $call_back,
                'returnContext' => json_encode(['coupon_code' => $request->coupon ]),
            );

            $data = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paiementpro.net/webservice/onlinepayment/init/curl-init.php");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);

            curl_close($ch);
            $response = json_decode($response);

            if (isset($response->success) && $response->success == true) {

                return redirect($response->url);

                return redirect()
                    ->route('plan.index', \Illuminate\Support\Facades\Crypt::encrypt($plan->id))
                    ->with('error', 'Something went wrong. OR Unknown error occurred');
            } else {
                return redirect()
                    ->route('plan.index', \Illuminate\Support\Facades\Crypt::encrypt($plan->id))
                    ->with('error', $response->message ?? 'Something went wrong.');
            }

        }
    }

    // public function planGetPaiementProStatus(Request $request,$plan_id)
    // {

    //     $data = request()->all();
    //     dd($data['returnContext']);
    //     $payment_setting = Utility::payment_settings();
    //     $authuser=Auth::user();
    //     $plan = Plan::find($plan_id);
    //     $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

    //        $res= Order::create(
    //             [
    //                 'order_id' => $orderID,
    //                 'name' => null,
    //                 'email' => null,
    //                 'card_number' => null,
    //                 'card_exp_month' => null,
    //                 'card_exp_year' => null,
    //                 'plan_name' => $plan->name,
    //                 'plan_id' => $plan->id,
    //                 'price' => !empty($request->amount)?$request->amount:0,
    //                 'price_currency' =>!empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
    //                 'txn_id' => '',
    //                 'payment_type' => __('Paiement Pro'),
    //                 'payment_status' => 'succeeded',
    //                 'receipt' => null,
    //                 'user_id' => $authuser->id,
    //             ]
    //         );


    //         $assignPlan = $authuser->assignPlan($plan->id);

    //         $coupons = Coupon::where('code',$request->coupon_id)->first();
    //         if (!empty($request->coupon_id)) {
    //             if (!empty($coupons)) {
    //                 $userCoupon = new UserCoupon();
    //                 $userCoupon->user = $authuser->id;
    //                 $userCoupon->coupon = $coupons->id;
    //                 $userCoupon->order = $orderID;

    //                 $userCoupon->save();
    //                 $usedCoupun = $coupons->used_coupon();
    //                 if ($coupons->limit <= $usedCoupun) {
    //                     $coupons->is_active = 0;
    //                     $coupons->save();
    //                 }
    //             }
    //         }

    //         if($assignPlan['is_success'])
    //         {
    //             Utility::referraltransaction($plan);
    //             return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
    //         }
    //         else
    //         {
    //             return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
    //         }
    // }

    public function planGetPaiementProStatus(Request $request, $plan_id)
    {
        $data = request()->all();
        // Extracting the coupon code from the JSON string
        $returnContext = json_decode($data['returnContext'], true);
        $couponCode = isset($returnContext['coupon_code']) ? $returnContext['coupon_code'] : null;

        // Fetching payment settings, authenticated user, and plan
        $payment_setting = Utility::payment_settings();
        $authuser = Auth::user();
        $plan = Plan::find($plan_id);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        // Creating order
        $res = Order::create([
            'order_id' => $orderID,
            'name' => null,
            'email' => null,
            'card_number' => null,
            'card_exp_month' => null,
            'card_exp_year' => null,
            'plan_name' => $plan->name,
            'plan_id' => $plan->id,
            'price' => !empty($request->amount) ? $request->amount : 0,
            'price_currency' => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
            'txn_id' => '',
            'payment_type' => __('Paiement Pro'),
            'payment_status' => 'succeeded',
            'receipt' => null,
            'user_id' => $authuser->id,
        ]);

        // Assigning plan to the user
        $assignPlan = $authuser->assignPlan($plan->id);

        // Fetching coupon based on the extracted coupon code
        $coupons = null;
        if (!empty($couponCode)) {
            $coupons = Coupon::where('code', $couponCode)->first();
        }

        // Processing coupon if found
        if (!empty($coupons)) {
            $userCoupon = new UserCoupon();
            $userCoupon->user = $authuser->id;
            $userCoupon->coupon = $coupons->id;
            $userCoupon->order = $orderID;

            $userCoupon->save();
            $usedCoupon = $coupons->used_coupon();
            if ($coupons->limit <= $usedCoupon) {
                $coupons->is_active = 0;
                $coupons->save();
            }
        }

        // Handling plan assignment result
        if ($assignPlan['is_success']) {
            Utility::referraltransaction($plan);
            return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
        } else {
            return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
        }
    }

}
