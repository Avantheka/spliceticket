<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LivePixel\MercadoPago\MP;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Validator;

class MercadoPaymentController extends Controller
{
     public $token;
    public $is_enabled;
    public $currancy;
    public $mode;

    public function planPayWithMercado(Request $request){

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
                            'payment_type' => 'Mercado Pago',
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $assignPlan = $authuser->assignPlan($plan->id, $request->mercado_payment_frequency);
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return Utility::error_res( __('Plan fail to upgrade.'));
                }
            }

            \MercadoPago\SDK::setAccessToken($this->token);
            try {

                // Create a preference object
                $preference = new \MercadoPago\Preference();
                // Create an item in the preference
                $item = new \MercadoPago\Item();
                $item->title = "Plan : " . $plan->name;
                $item->quantity = 1;
                $item->unit_price = (float)$price;
                $preference->items = array($item);

                $success_url = route('plan.mercado',[$request->plan_id,'payment_frequency='.$request->mercado_payment_frequency,'coupon_id='.$coupons_id,'flag'=>'success']);
                $failure_url = route('plan.mercado',[$request->plan_id,'flag'=>'failure']);
                $pending_url = route('plan.mercado',[$request->plan_id,'flag'=>'pending']);

                $preference->back_urls = array(
                    "success" => $success_url,
                    "failure" => $failure_url,
                    "pending" => $pending_url
                );

                $preference->auto_return = "approved";
                $preference->save();

                // Create a customer object
                $payer = new \MercadoPago\Payer();
                // Create payer information
                $payer->name = \Auth::user()->name;
                $payer->email = \Auth::user()->email;
                $payer->address = array(
                    "street_name" => ''
                );
                if($this->mode =='live'){
                    $redirectUrl = $preference->init_point;
                }else{
                    $redirectUrl = $preference->sandbox_init_point;
                }
                return redirect($redirectUrl);
            } catch (Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }

        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }
    }

    public function getPaymentStatus(Request $request,$plan){

        $this->planpaymentSetting();
        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan           = Plan::find($planID);

       $user = Auth::user();
       $orderID = time();

       if($plan)
       {

            if($plan && $request->has('status'))
            {

                if($request->status == 'approved' && $request->flag =='success')
                {
                        if(!empty($user->payment_subscription_id) && $user->payment_subscription_id != '')
                        {
                           try
                           {
                               $user->cancel_subscription($user->id);
                           }
                           catch(\Exception $exception)
                           {
                               \Log::debug($exception->getMessage());
                           }
                        }

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
                       $order->price          = $plan->price?$plan->price:0;
                       $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
                       $order->txn_id         = $request->has('preference_id')?$request->preference_id:'';
                       $order->payment_type   = __('Mercado Pago');
                       $order->payment_status = 'succeeded';
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
                   }else{
                       return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                   }
               }
               else
               {
                   return redirect()->route('plans.index')->with('error', __('Transaction has been failed! '));
               }

    }
}


    public function invoicePayWithMercado(Request $request){

        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
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

        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user=User::where('id',$invoice->created_by)->first();
        }

        $this->paymentSetting($invoice->created_by);

        $amount = number_format((float)$request->amount, 2, '.', '');

        $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');

        if($invoice_getdue < $amount){
            return Utility::error_res('not correct amount');
        }

        $preference_data       = array(
            "items" => array(
                array(
                    "title" => "Invoice Payment",
                    "quantity" => 1,
                    "currency_id" => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                    "unit_price" => (float)$request->amount,
                ),
            ),
        );

         \MercadoPago\SDK::setAccessToken($this->token);
        try {

            // Create a preference object
            $preference = new \MercadoPago\Preference();
            // Create an item in the preference
            $item = new \MercadoPago\Item();
            $item->title = "Invoice : " . $request->invoice_id;
            $item->quantity = 1;
            $item->unit_price = (float)$request->amount;
            $preference->items = array($item);

            $success_url = route('invoice.mercado',[encrypt($invoice->id),'amount'=>(float)$request->amount,'flag'=>'success']);
            $failure_url = route('invoice.mercado',[encrypt($invoice->id),'flag'=>'failure']);
            $pending_url = route('invoice.mercado',[encrypt($invoice->id),'flag'=>'pending']);
            $preference->back_urls = array(
                "success" => $success_url,
                "failure" => $failure_url,
                "pending" => $pending_url
            );
            $preference->auto_return = "approved";
            $preference->save();

            // Create a customer object
            $payer = new \MercadoPago\Payer();
            // Create payer information
            $payer->name = $user->name;
            $payer->email = $user->email;
            $payer->address = array(
                "street_name" => ''
            );

            if($this->mode =='live'){
                $redirectUrl = $preference->init_point;
            }else{
                $redirectUrl = $preference->sandbox_init_point;
            }
            return redirect($redirectUrl);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getInvociePaymentStatus(Request $request,$invoice_id){

         if(!empty($invoice_id))
        {

            $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::find($invoice_id);
             if(\Auth::check())
                {
                     $user = Auth::user();
                }
                else
                {
                   $user=User::where('id',$invoice->created_by)->first();
                }
            if($invoice && $request->has('status'))
            {
                try
                {

                    if($request->status == 'approved' && $request->flag =='success')
                    {




                    $invoice_payment                 = new InvoicePayment();
                    $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($invoice->created_by);
                    $invoice_payment->invoice_id     = $invoice_id;
                    $invoice_payment->amount         = isset($request->amount)?$request->amount:0;
                    $invoice_payment->date           = date('Y-m-d');
                    $invoice_payment->payment_id     = 0;
                    $invoice_payment->payment_type   = __('Mercado Pago');
                    $invoice_payment->client_id      = 0;
                    $invoice_payment->notes          = '';
                    $invoice_payment->save();

                    if(($invoice->getDue() - $invoice_payment->amount) == 0)
                    {
                        $invoice->status = 'paid';
                        $invoice->save();
                    }

                        if(\Auth::check())
                        {
                              return redirect()->route('invoice.show', $invoice_id)->with('success', __('Invoice paid Successfully!'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        }

                    }else{
                        if(\Auth::check())
                        {
                               return redirect()->route('invoice.show',$invoice_id)->with('error', __('Transaction fail'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction fail'));
                        }

                    }
                }
                catch(\Exception $e)
                {
                    return redirect()->route('invoice.index')->with('error', __('Plan not found!'));
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
        $this->token = isset($admin_payment_setting['mercado_access_token'])?$admin_payment_setting['mercado_access_token']:'';
        $this->mode = isset($admin_payment_setting['mercado_mode'])?$admin_payment_setting['mercado_mode']:'';
        $this->is_enabled = isset($admin_payment_setting['is_mercado_enabled'])?$admin_payment_setting['is_mercado_enabled']:'off';
        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        return;
    }


    public function planpaymentSetting()
    {

        $admin_payment_setting = Utility::payment_settings();
        $this->token = isset($admin_payment_setting['mercado_access_token'])?$admin_payment_setting['mercado_access_token']:'';
        $this->mode = isset($admin_payment_setting['mercado_mode'])?$admin_payment_setting['mercado_mode']:'';
        $this->is_enabled = isset($admin_payment_setting['is_mercado_enabled'])?$admin_payment_setting['is_mercado_enabled']:'off';
        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        return;
    }
}
