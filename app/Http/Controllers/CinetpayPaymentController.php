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

class CinetpayPaymentController extends Controller
{
    public function planPayWithcinetpay(Request $request)
    {
        $payment_setting = Utility::payment_settings();

        $api_key = isset($payment_setting['cinetpay_api_key']) ? $payment_setting['cinetpay_api_key'] : '';
        $site_id = isset($payment_setting['cinetpay_site_id']) ? $payment_setting['cinetpay_site_id'] : '';

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
                    $usedCoupon = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    if ($coupons->limit == $usedCoupon) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $coupon_id = $coupons->id;
                    if( $get_amount<1){
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        $statuses = 'success';
                        if ($coupon_id != '') {

                            $userCoupon         = new UserCoupon();//UsersCoupons
                            $userCoupon->user   = $user->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order = $orderID;
                            $userCoupon->save();
                            $usedCoupon = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupon) {
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
                        $order->payment_type   = __('Cinetpay');
                       $order->payment_status  = $statuses;
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

            $call_back = route('plan.cinetpay.status', [
                $plan->id,
            ],'?_token=' . csrf_token());

            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            $cinetpay_data =  [
                "amount" => $get_amount,
                "currency" => $currency,
                "apikey" =>  $api_key,
                "site_id" =>  $site_id,
                // "secret_key"=>  $secret_key,
                "transaction_id" => $orderID,
                "description" => "TEST-Laravel",
                // "return_url" => $call_back,
                "return_url" => route('plan.cinetpay.status', [$plan->id]). '?_token=' . csrf_token(),
                "metadata" => "user001",
                'customer_surname' => "test",
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone_number' => '9658745214',
                'customer_address' => 'abu dhabi',
                'customer_city' => 'texas',
                'customer_country' => 'BF',
                'customer_state' => 'USA',
                'customer_zip_code' => '123456',
                'invoice_data' => [
                    'coupon_id' => $request->coupon,
                    'amount' => $get_amount,
                ]
            ];


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($cinetpay_data),
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => array(
                    "content-type:application/json"
                ),
            ));
            $response = curl_exec($curl);
            // dd($response);

            $err = curl_error($curl);
            curl_close($curl);

            //On recupère la réponse de CinetPay
            $response_body = json_decode($response, true);
            if ($response_body['code'] == '201') {
                $payment_link = $response_body["data"]["payment_url"]; // Retrieving the payment URL
                //Recording information in the database
                //Then redirect to the payment page
                return redirect($payment_link);
            } else {
                return back()->with('info', 'Une erreur est survenue.  Description : ' . $response_body["description"]);
            }
        }
    }


    public function planGetCinetpayStatus(Request $request, $plan_id)
    {

        $data = request()->all();
        $authuser = Auth::user();
        $plan = Plan::find($plan_id);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $payment_setting = Utility::payment_settings();

        $api_key = isset($payment_setting['cinetpay_api_key']) ? $payment_setting['cinetpay_api_key'] : '';
        $site_id = isset($payment_settting['cinetpay_site_id']) ? $payment_setting['cinetpay_site_id'] : '';
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
        $cinetpay_check = [
            "apikey" => $api_key,
            "site_id" => $site_id,
            "transaction_id" => $orderID
        ];

        $response = $this->getPayStatus($cinetpay_check);

        $response_body = json_decode($response, true);
        if ($response_body['code'] == '00') {
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
                    'price' => !empty($request->amount) ? $request->amount : 0,
                    'price_currency' => isset($admin_settings['defult_currancy']) ? $admin_settings['defult_currancy'] : '',
                    'txn_id' => '',
                    'payment_type' => __('Cinetpay'),
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'user_id' => $authuser->id,
                ]
            );

            $assignPlan = $authuser->assignPlan($plan->id);

            $coupons = Coupon::where('code',$request->coupon_id)->first();
            if (!empty($request->coupon_id)) {
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
            }


            if ($assignPlan['is_success']) {
                Utility::referraltransaction($plan);
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
            } else {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
        }
        else
        {
            return redirect()->back()->with('error','Transaction has been failed.');
        }



    }
    public function getPayStatus($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment/check',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                "content-type:application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err)
         print ($err);
        else
        return ($response);
    }
}
