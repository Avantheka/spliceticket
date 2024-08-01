<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaystackPaymentController extends Controller
{
    //
    public $secret_key;
    public $public_key;
    public $is_enabled;


    public function planPayWithPaystack(Request $request){

        $this->planpaymentSetting();



        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan           = Plan::find($planID);
        $authuser       = Auth::user();
        $coupon_id ='';
        if($plan)
        {
            /* Check for code usage */
            $plan->discounted_price = false;
            $price                  = $plan->price;

            if(isset($request->coupon) && !empty($request->coupon))
            {
                $request->coupon = trim($request->coupon);
                $coupons         = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if(!empty($coupons))
                {
                    $usedCoupun             = $coupons->used_coupon();
                    $discount_value         = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;

                    if($usedCoupun >= $coupons->limit)
                    {
                        return Utility::error_res( __('This coupon code has expired.'));
                    }
                    $price = $price - $discount_value;
                    $coupon_id = $coupons->id;
                }
                else
                {
                    return Utility::error_res( __('This coupon code is invalid or has expired.'));
                }
            }
            if($price <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();

                $assignPlan = $authuser->assignPlan($plan->id);

                if($assignPlan['is_success'] == true && !empty($plan))
                {

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
                            'price' => $price,
                            'price_currency' => !empty($this->currancy) ? $this->currancy : 'usd',
                            'txn_id' => '',
                            'payment_type' => 'Paystack',
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $res['msg'] = __("Plan successfully upgraded.");
                    $res['flag'] = 2;
                    return $res;
                }
                else
                {
                    return Utility::error_res( __('Plan fail to upgrade.'));
                }
            }
            $res_data['email'] = Auth::user()->email;
            $res_data['total_price'] = $price;
            $res_data['currency'] = $this->currancy;
            $res_data['flag'] = 1;
            $res_data['coupon'] = $coupon_id;
            return $res_data;
        }
        else
        {
            return Utility::error_res( __('Plan is deleted.'));
        }
    }

    public function getPaymentStatus(Request $request,$pay_id,$plan){

        $this->planpaymentSetting();

        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan           = Plan::find($planID);
        $user = Auth::user();
        $result = array();

        if($plan)
        {
            //The parameter after verify/ is the transaction reference to be verified
            $url = "https://api.paystack.co/transaction/verify/$pay_id";
            $ch  = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, [
                       'Authorization: Bearer ' . $this->secret_key,
                   ]
            );
            $result = curl_exec($ch);
            curl_close($ch);
            if($result)
            {
                $result = json_decode($result, true);
            }
            $orderID = time();
            if(isset($result['status']) && $result['status'] == true)
            {
                if($request->has('coupon_id') && $request->coupon_id != '')
                {
                    $coupons = Coupon::find($request->coupon_id);

                    if(!empty($coupons))
                    {
                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $user->id;
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
                }
                $objUser                    = \Auth::user();
                $assignPlan = $objUser->assignPlan($plan->id);
                if($assignPlan['is_success'] == true  && !empty($plan))
                {


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
                            'price' => $plan->price,
                            'price_currency' => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                            'txn_id' => '',
                            'payment_type' => __('paystack'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => \Auth::user()->id,
                        ]
                    );
                    Utility::referraltransaction($plan);
                  
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }

            } else
            {
                return redirect()->route('plan.index')->with('error', __('Transaction fail'));
            }

        }else
        {
            return redirect()->route('plan.index')->with('error', __('Plan not found!'));
        }
    }

    public function paymentSetting($id)
    {
        $payment_setting = Utility::invoice_payment_settings($id);

        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';

        $this->secret_key = isset($payment_setting['paystack_secret_key'])?$payment_setting['paystack_secret_key']:'';
        $this->public_key = isset($payment_setting['paystack_public_key'])?$payment_setting['paystack_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_paystack_enabled'])?$payment_setting['is_paystack_enabled']:'off';
        return $this;
    }

    public function planpaymentSetting()
    {
        $payment_setting = Utility::payment_settings();

        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';

        $this->secret_key = isset($payment_setting['paystack_secret_key'])?$payment_setting['paystack_secret_key']:'';
        $this->public_key = isset($payment_setting['paystack_public_key'])?$payment_setting['paystack_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_paystack_enabled'])?$payment_setting['is_paystack_enabled']:'off';
        return $this;
    }

}
