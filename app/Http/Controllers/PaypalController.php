<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    private $_api_context;

    public function paymentConfig()
    {
        if(\Auth::check())
        {
            $payment_setting = Utility::payment_settings();

        }
        else
        {
            $payment_setting = Utility::payment_settings($this->invoiceData->created_by);
        }


        // config(
        //     [
        //         'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
        //         'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
        //         'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
        //     ]
        // );




        if($payment_setting['paypal_mode']  == 'live')
        {
            config(
                [
                    'paypal.live.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                    'paypal.live.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                    'paypal.mode' =>isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',

                ]
            );
        }
        else{
            config(
                [
                    'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                    'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                    'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',

                ]
            );
        }


    }

    public function planPayWithPaypal(Request $request)
    {
        $authuser = Auth::user();
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan   = Plan::find($planID);
        $this->paymentconfig();
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $get_amount = $plan->price;
        if($plan){
            try
            {
                $coupon_id = null;
                $price     = $plan->price;
                if(!empty($request->coupon))
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
                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('plan.get.payment.status',[$plan->id,$get_amount]),
                        "cancel_url" =>  route('plan.get.payment.status',[$plan->id,$get_amount]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                                "value" => $price,
                            ]
                        ]
                    ]
                ]);

                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                    ->route('plan.index')
                    ->with('error', 'Something went wrong.');
                } else {
                    return redirect()
                    ->route('plan.index')
                    ->with('error', $response['message'] ?? 'Payment Canceled.');
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plans.index')->with('error', __($e->getMessage()));
            }

        }else{
            return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetPaymentStatus(Request $request, $plan_id)
    {
        $user = Auth::user();
        $plan = Plan::find($plan_id);

        if($plan)
        {
            $this->paymentconfig();

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($request['token']);
            $payment_id = Session::get('paypal_payment_id');
            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
            if (isset($response['status']) && $response['status'] == 'COMPLETED')
            {
                if($response['status'] == 'COMPLETED'){
                   $statuses = 'success';
                }
                    $order                 = new Order();
                    $order->order_id       = $order_id;
                    $order->name           = $user->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = $plan->price;
                    $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
                    $order->txn_id         = '';
                    $order->payment_type   = 'PAYPAL';
                    $order->payment_status = $statuses;
                    $order->receipt        = '';
                    $order->user_id        = $user->id;
                    $order->save();

                $assignPlan = $user->assignPlan($plan->id);
                if($assignPlan['is_success'])
                {
                    Utility::referraltransaction($plan);
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }

                return redirect()
                    ->route('plan.index')
                    ->with('success', 'Transaction complete.');
            } else {
                return redirect()
                    ->route('plan.index')
                    ->with('error', $response['message'] ?? 'Transaction Cancel.');
            }
        }
        else
        {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }
}
