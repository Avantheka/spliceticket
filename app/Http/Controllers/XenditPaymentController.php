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
use Xendit\Xendit;
use Illuminate\Support\Str;

class XenditPaymentController extends Controller
{
    public function planPayWithXendit(Request $request)
    {
        $payment_setting = Utility::payment_settings();
        $xendit_api = $payment_setting['xendit_api'];
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
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = Auth::user()->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $orderID;
                    $userCoupon->save();
                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }
            $response = ['orderId' => $orderID, 'user' => $user, 'get_amount' => $get_amount, 'plan' => $plan, 'currency' => $currency];
            Xendit::setApiKey($xendit_api);
            $params = [
                'external_id' => $orderID,
                'payer_email' => Auth::user()->email,
                'description' => 'Payment for order ' . $orderID,
                'amount' => $get_amount,
                'callback_url' => route('plan.xendit.status', [$plan->id, 'order_id' => $orderID, 'price' => $get_amount]),
                'success_redirect_url' => route('plan.xendit.status', $response),
                'failure_redirect_url' => route('plan.index'),
            ];

            $invoice = \Xendit\Invoice::create($params);
            Session::put('invoice',$invoice);

            return redirect($invoice['invoice_url']);
        }
    }

    public function planGetXenditStatus(Request $request)
    {

        $data = request()->all();

        $fixedData = [];
        foreach ($data as $key => $value) {
            $fixedKey = str_replace('amp;', '', $key);
            $fixedData[$fixedKey] = $value;
        }


        $payment_setting = Utility::payment_settings();
        $xendit_api = $payment_setting['xendit_api'];
        Xendit::setApiKey($xendit_api);

        $session = Session::get('invoice');
        $getInvoice = \Xendit\Invoice::retrieve($session['id']);


        $authuser = User::find($fixedData['user']);
        $plan = Plan::find($fixedData['plan']);

        if($getInvoice['status'] == 'PAID'){

                    $order  = new Order();
                    $order->order_id       = $fixedData['orderId'];
                    $order->name           = $authuser->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = $fixedData['get_amount'] == null ? 0 : $fixedData['get_amount'];
                    $order->price_currency =  $fixedData['currency'];
                    $order->txn_id         = '';
                    $order->payment_type   = 'Xendit';
                    $order->payment_status = 'succeeded';
                    $order->receipt        = '';
                    $order->user_id        = $authuser->id;
                    $order->save();

            $assignPlan = $authuser->assignPlan($plan->id);

            if($assignPlan['is_success'])
            {
                Utility::referraltransaction($plan);
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
            }
            else
            {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
        }
    }
}
