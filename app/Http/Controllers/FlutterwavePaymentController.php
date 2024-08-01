<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Validator;

class FlutterwavePaymentController extends Controller
{
    public $secret_key;
    public $public_key;
    public $is_enabled;
    public $currancy;

    public function planPayWithFlutterwave(Request $request){

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
                    if(!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '')
                    {
                        try
                        {
                            $authuser->cancel_subscription($authuser->id);
                        }
                        catch(\Exception $exception)
                        {
                            \Log::debug($exception->getMessage());
                        }
                    }

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
                            'price' => $price==null?0:$price,
                            'price_currency' => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                            'txn_id' => '',
                            'payment_type' => 'Paystack',
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $assignPlan = $authuser->assignPlan($plan->id, $request->flaterwave_payment_frequency);
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return Utility::error_res( __('Plan fail to upgrade.'));
                }
            }

            $res_data['email'] = Auth::user()->email;
            $res_data['total_price'] = $price;
            $res_data['currency'] =!empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
            $res_data['flag'] = 1;
            $res_data['payment_frequency'] = $request->flaterwave_payment_frequency;
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
        $result = array();

        $user = Auth::user();
        if($plan)
        {
            try
            {
                $orderID = time();
                $data    = array(
                    'txref' => $pay_id,
                    'SECKEY' => $this->secret_key,
                    //secret key from pay button generated on rave dashboard
                );
                // make request to endpoint using unirest.
                $headers = array('Content-Type' => 'application/json');
                $body    = \Unirest\Request\Body::json($data);
                $url     = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify"; //please make sure to change this to production url when you go live

                // Make `POST` request and handle response with unirest
                $response = \Unirest\Request::post($url, $headers, $body);
                if(!empty($response))
                {
                    $response = json_decode($response->raw_body, true);
                }
                if(isset($response['status']) && $response['status'] == 'success')
                {
                    $paydata = $response['data'];

                    if($request->has('coupon_id') && $request->coupon_id != '')
                    {
                        $coupons = Coupon::find($request->coupon_id);
                        if(!empty($coupons))
                        {
                            $userCoupon            = new UserCoupon();
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

                    $order                 = new Order();
                    $order->order_id       = $orderID;
                    $order->name           = $user->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = isset($paydata['amount']) ? $paydata['amount'] : 0;
                    $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
                    $order->txn_id         = isset($paydata['txid']) ? $paydata['txid'] : $pay_id;
                    $order->payment_type   = __('flutterwave');
                    $order->payment_status = 'success';
                    $order->receipt        = '';
                    $order->user_id        = $user->id;
                    $order->save();

                    $assignPlan = $user->assignPlan($plan->id, $request->payment_frequency);

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
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Plan not found!'));
            }
        }
    }

    public function invoicePayWithFlutterwave(Request $request){

        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
            'email' => 'required|email',
        ];
        $validator      = Validator::make(
            $request->all(), $validatorArray
        )->setAttributeNames(
            ['invoice_id' => 'Invoice']
        );
        if($validator->fails())
        {
            return Utility::error_res($validator->errors()->first());
        }


        $invoice = Invoice::find($request->invoice_id);

        $this->paymentSetting($invoice->created_by);

        $amount = number_format((float)$request->amount, 2, '.', '');

        $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');

        if($invoice_getdue < $amount){
            return Utility::error_res('not correct amount');
        }

        $res_data['email'] = $request->email;
        $res_data['total_price'] = $request->amount;
        $res_data['currency'] = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
        $res_data['flag'] = 1;
        $res_data['invoice_id'] = $invoice->id;
        $request->session()->put('invoice_data', $res_data);
        $this->pay_amount = $request->amount;
        return $res_data;
    }

    public function getInvociePaymentStatus($pay_id,$invoice_id,Request $request){


        $encrypt_invoiceid = \Illuminate\Support\Facades\Crypt::encrypt(decrypt($invoice_id));

        if(!empty($invoice_id) && !empty($pay_id))
        {
            $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::find($invoice_id);

            $this->paymentSetting($invoice->created_by);


            $invoice_data =  $request->session()->get('invoice_data') ;
            if($invoice && !empty($invoice_data))
            {

                try
                {
                    $orderID = time();
                    $data    = array(
                        'txref' => $pay_id,
                        'SECKEY' => $this->secret_key,
                        //secret key from pay button generated on rave dashboard
                    );
                    // make request to endpoint using unirest.
                    $headers = array('Content-Type' => 'application/json');
                    $body    = \Unirest\Request\Body::json($data);
                    $url     = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify"; //please make sure to change this to production url when you go live

                    // Make `POST` request and handle response with unirest
                    $response = \Unirest\Request::post($url, $headers, $body);
                    if(!empty($response))
                    {
                        $response = json_decode($response->raw_body, true);
                    }

                    if(isset($response['status']) && $response['status'] == 'success')
                    {
                        $paydata = $response['data'];

                        $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($invoice->created_by);
                        $invoice_payment->invoice_id     = $invoice_id;
                        $invoice_payment->amount         = isset($invoice_data['total_price'])?$invoice_data['total_price']:0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   = __('Flutterwave');
                        $invoice_payment->client_id      = 0;
                        $invoice_payment->notes          = '';
                        $invoice_payment->save();

                        $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');

                        if($invoice_getdue <= 0.0)
                        {

                            Invoice::change_status($invoice->id, 3);
                        }
                        else{

                            Invoice::change_status($invoice->id, 2);
                        }

                        return redirect()->route('pay.invoice',$encrypt_invoiceid)->with('success', __('Payment added Successfully'));
                    }else{
                        return redirect()->route('pay.invoice',$encrypt_invoiceid)->with('error', __('Transaction fail'));
                    }
                }
                catch(\Exception $e)
                {
                    return redirect()->route('pay.invoice',$encrypt_invoiceid)->with('error', __('Invoice not found.'));
                }
            }else{
                return redirect()->route('pay.invoice',$encrypt_invoiceid)->with('error', __('Invoice not found.'));
            }
        }else{
            return redirect()->route('pay.invoice',$encrypt_invoiceid)->with('error', __('Invoice not found.'));
        }
    }

    public function paymentSetting($id)
    {

        $payment_setting = Utility::invoice_payment_settings($id);

        $this->currancy =isset($payment_setting['currency'])?$payment_setting['currency']:'';

        $this->secret_key = isset($payment_setting['flutterwave_secret_key'])?$payment_setting['flutterwave_secret_key']:'';
        $this->public_key = isset($payment_setting['flutterwave_public_key'])?$payment_setting['flutterwave_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_flutterwave_enabled'])?$payment_setting['is_flutterwave_enabled']:'off';
    }

    public function planpaymentSetting()
    {

        $payment_setting = Utility::payment_settings();

        $this->currancy =isset($payment_setting['currency'])?$payment_setting['currency']:'';

        $this->secret_key = isset($payment_setting['flutterwave_secret_key'])?$payment_setting['flutterwave_secret_key']:'';
        $this->public_key = isset($payment_setting['flutterwave_public_key'])?$payment_setting['flutterwave_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_flutterwave_enabled'])?$payment_setting['is_flutterwave_enabled']:'off';
    }
}
