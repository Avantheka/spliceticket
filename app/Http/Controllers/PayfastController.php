<?php

namespace App\Http\Controllers;


use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use Exception;
use App\Models\Utility;
use Illuminate\Http\Request;

class PayfastController extends Controller
{
    public $payfast_access_token;
    public $payfast_mode;
    public $is_enabled;
    public $token;
    public $mode;
    public $currancy;
    public $MerchantId;
    public $merchantKey;

    public function __construct()
    {

        $payment_setting = Utility::payment_settings();


        $this->MerchantId = isset($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '';
        $this->merchantKey = isset($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '';
        $this->is_enabled = isset($payment_setting['is_payfast_enabled']) ? $payment_setting['is_payfast_enabled'] : 'off';
    }

    public function index(Request $request)
    {
        if (\Auth::check())
        {
            $payment_setting = Utility::payment_settings();
            $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

            $plan = Plan::find($planID);
            if ($plan)
            {
                $plan_amount = $plan->price;
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
                $user = \Auth::user();

                if ($request->coupon_amount >= 0 && $request->coupon_code != null)
                {
                    $coupons = Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', '1')->first();

                    if(!empty($coupons))
                    {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user = $user->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order = $order_id;
                        $userCoupon->save();
                        $usedCoupun             = $coupons->used_coupon();
                        $discount_value         = ($plan_amount / 100) * $coupons->discount;
                        $plan->discounted_price = $plan_amount - $discount_value;

                        if ($usedCoupun >= $coupons->limit)
                        {
                            return redirect()->back()->with('error',__('This coupon code has expired.'));
                        }
                        $plan_amount = $plan_amount - $discount_value;
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }
                $success = \Illuminate\Support\Facades\Crypt::encrypt([
                    'plan' => $plan->toArray(),
                    'order_id' => $order_id,
                    'plan_amount' => $plan_amount
                ]);

                $data = array(
                    // Merchant details
                    'merchant_id' => !empty($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '',
                    'merchant_key' => !empty($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '',
                    'return_url' => route('payfast.payment.success',$success),
                    'cancel_url' => route('plan.index'),
                    'notify_url' => route('plan.index'),
                    // Buyer details
                    'name_first' => $user->name,
                    'name_last' => '',
                    'email_address' => $user->email,
                    // Transaction details
                    'm_payment_id' => $order_id, //Unique payment ID to pass through to notify_url
                    'amount' => $plan_amount,
                    'item_name' => $plan->name,
                );

                $passphrase = !empty($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
                $signature = $this->generateSignature($data, $passphrase);
                $data['signature'] = $signature;

                $htmlForm = '';

                foreach ($data as $name => $value) {
                    $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
                }

                return response()->json([
                    'success' => true,
                    'inputs' => $htmlForm,
                ]);
            }
        }
    }


    public function generateSignature($data, $passPhrase = null)
    {
        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }
        $getString = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }
        return md5($getString);
    }

    public function success($success)
    {
        try {
            $user = \Auth::user();
            $data = \Illuminate\Support\Facades\Crypt::decrypt($success);
            $order = new Order();
            $order->order_id = $data['order_id'];
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $data['plan']['name'];
            $order->plan_id = $data['plan']['id'];
            $order->price = $data['plan_amount'];
            $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
            $order->txn_id = $data['order_id'];
            $order->payment_type = __('PayFast');
            $order->payment_status = 'succeeded';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $user->id;
            // dd( $order);
            $order->save();
            $plan       = Plan::find($order->plan_id);
            $assignPlan = $user->assignPlan($data['plan']['id']);
            if ($assignPlan['is_success']) {
                Utility::referraltransaction($plan);
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
            } else {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
        } catch (Exception $e) {
            return redirect()->route('plan.index')->with('error', __($e));
        }
    }
}
