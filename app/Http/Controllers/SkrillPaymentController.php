<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Obydul\LaraSkrill\SkrillClient;
use Obydul\LaraSkrill\SkrillRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\UserCoupon;
use App\Models\InvoicePayment;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;

class SkrillPaymentController extends Controller
{
    public $email;
    public $is_enabled;
    public $currancy;

    public function planPayWithSkrill(Request $request)
    {
        $this->planpaymentSetting();
        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan           = Plan::find($planID);
        $authuser       = Auth::user();
        $coupons_id ='';
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
                    $coupons_id = $coupons->id;

                    if($usedCoupun >= $coupons->limit)
                    {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $price = $price - $discount_value;
                }
                else
                {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }
            if($price <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();
                $assignPlan = $authuser->assignPlan($plan->id);
                if($assignPlan['is_success'] == true && !empty($plan))
                {
                    $orderID = time();
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
                            'payment_type' => __('SKRILL'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $assignPlan = $authuser->assignPlan($plan->id, $request->skrill_payment_frequency);
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                }
            }
            $tran_id = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
            $skill               = new SkrillRequest();
            $skill->pay_to_email = $this->email;
            $skill->return_url   = route('plan.skrill',[$request->plan_id,'tansaction_id=' . MD5($tran_id),'payment_frequency='.$request->skrill_payment_frequency,'coupon_id='.$coupons_id]);
            $skill->cancel_url   = route('plan.index',[$request->plan_id]);
            // create object instance of SkrillRequest
            $skill->transaction_id  = MD5($tran_id); // generate transaction id
            $skill->amount          = $price;
            $skill->currency        = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
            $skill->language        = 'EN';
            $skill->prepare_only    = '1';
            $skill->merchant_fields = 'site_name, customer_email';
            $skill->site_name       = Auth::user()->name;
            $skill->customer_email  = Auth::user()->email;
            // create object instance of SkrillClient
            $client = new SkrillClient($skill);
            $sid    = $client->generateSID(); //return SESSION ID
            // handle error
            $jsonSID = json_decode($sid);
            if($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
            {
            }
            // do the payment
            $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
            if($tran_id)
            {
                $data = [
                    'amount' => $price,
                    'trans_id' => MD5($request['transaction_id']),
                    'currency' =>!empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                ];
                session()->put('skrill_data', $data);
            }

            try
            {
                return  new RedirectResponse($redirectUrl);
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Transaction has been failed!'));
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }
    }
    public function getPaymentStatus(Request $request,$plan)
    {
        $this->planpaymentSetting();
        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan           = Plan::find($planID);
        $user = Auth::user();
        $orderID = time();
        if($plan)
        {
            try
            {
                if(session()->has('skrill_data'))
                {
                    $get_data = session()->get('skrill_data');
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
                        $order->price          = isset($get_data['amount']) ? $get_data['amount'] : 0;
                        $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
                        $order->txn_id         = isset($request->transaction_id) ? $request->transaction_id : '';
                        $order->payment_type   = __('Skrill');
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
                return redirect()->back()->with('error', __('Plan not found!'));
            }
        }
    }
    public function invoicePayWithSkrill(Request $request){



        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
            'name' => 'required',
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

        $tran_id = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
        try{
            $skill               = new SkrillRequest();
            $skill->pay_to_email = $this->email;
            $skill->return_url   = route('invoice.skrill',encrypt([$request->invoice_id]));
            $skill->cancel_url   = route('invoice.skrill',encrypt([$request->invoice_id]));

            // create object instance of SkrillRequest
            $skill->transaction_id  = MD5($tran_id); // generate transaction id
            $skill->amount          = $request->amount;
            $skill->currency        = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
            $skill->language        = 'EN';
            $skill->prepare_only    = '1';
            $skill->merchant_fields = 'site_name, customer_email';
            $skill->site_name       = $request->name;
            $skill->customer_email  = $request->email;

            // create object instance of SkrillClient
            $client = new SkrillClient($skill);
            $sid    = $client->generateSID(); //return SESSION ID

            // handle error
            $jsonSID = json_decode($sid);

            if($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
            {

            }


            $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
            if($tran_id)
            {
                $data = [
                    'amount' => $request->amount,
                    'trans_id' => MD5($request['transaction_id']),
                    'currency' =>!empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                ];
                session()->put('skrill_data', $data);
            }

            return new RedirectResponse($redirectUrl);

        }catch(\Exception $e)
        {
            return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($request->invoice_id))->with('error', __('Transaction has been failed!'));
        }
    }

    public function getInvociePaymentStatus(Request $request,$invoice_id){

        if(!empty($invoice_id))
        {
            $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::where('id',$invoice_id)->first();

             if(\Auth::check())
            {
                 $user = Auth::user();
            }
            else
            {
               $user=User::where('id',$invoice->created_by)->first();
            }

            if($invoice)
            {
                try
                {

                    if(session()->has('skrill_data'))
                    {
                        $get_data = session()->get('skrill_data');

                        $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                        $invoice_payment->invoice_id     = $invoice->id;
                        $invoice_payment->amount         = isset($get_data['amount']) ? $get_data['amount'] : 0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   = 'skrill';
                        $invoice_payment->client_id      =  $user->id;
                        $invoice_payment->notes          = '';

                        $invoice_payment->save();

                        if(($invoice->getDue() - $invoice_payment->amount) == 0)
                        {
                            $invoice->status = 'paid';
                            $invoice->save();
                        }

                        $settings  = Utility::settings($invoice->created_by);

                        if(isset($settings['payment_notification']) && $settings['payment_notification'] ==1){
                            $msg = ucfirst($user->name) .' paid '.$get_data['amount'].'.';

                            Utility::send_slack_msg($msg);
                        }

                        if(isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] ==1){
                            $resp = ucfirst($user->name) .' paid '.$get_data['amount'].'.';
                            \Utility::send_telegram_msg($resp);
                        }

                        if(\Auth::check())
                        {
                             return redirect()->route('invoice.show', $invoice_id)->with('success', __('Payment added Successfully'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully'));
                        }


                    }else{
                        if(\Auth::check())
                        {
                             return redirect()->route('invoice.show',$invoice_id)->with('error', __('Transaction has been failed!'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
                        }
                    }
                }catch(\Exception $e)
                {
                    if(\Auth::check())
                        {
                             return redirect()->route('invoice.show',$invoice_id)->with('error', __('Invoice not found.'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                        }

                }

            }else{
                if(\Auth::check())
                {
                     return redirect()->route('invoice.show',$invoice_id)->with('error', __('Invoice not found.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }

            }
        }else{
            if(\Auth::check())
                {
                     return redirect()->route('invoice.index')->with('error', __('Invoice not found.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }

        }
    }
    public function paymentSetting($id)
    {

        $admin_payment_setting = Utility::invoice_payment_settings($id);

        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        $this->email = isset($admin_payment_setting['skrill_email'])?$admin_payment_setting['skrill_email']:'';
        $this->is_enabled = isset($admin_payment_setting['is_skrill_enabled'])?$admin_payment_setting['is_skrill_enabled']:'off';
    }

    public function planpaymentSetting()
    {

        $admin_payment_setting = Utility::payment_settings();

        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        $this->email = isset($admin_payment_setting['skrill_email'])?$admin_payment_setting['skrill_email']:'';
        $this->is_enabled = isset($admin_payment_setting['is_skrill_enabled'])?$admin_payment_setting['is_skrill_enabled']:'off';
    }
}
