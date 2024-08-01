<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendCloseTicket;
use App\Mail\SendTicket;
use Illuminate\Support\Facades\Mail;
use App\Models\Api\User;
use App\Models\Api\Category;
use App\Models\Api\Ticket;
use App\Models\Api\Settings;
use App\Models\CustomField;
use App\Mail\EmailTest;


use Illuminate\Support\Facades\DB;
use Artisan;


class SettingController extends Controller
{
    use ApiResponser;

    public function site_setting_page(Request $request)
    {
		$user = User::find($request->id);
   
        $data = Settings::where('created_by',$user->parent)->get();

         if(Settings::where('created_by',$user->parent)->exists() != null){
            foreach($data as $row)
            {
                $settings[$row->name] = $row->value;
            }

            $data = [
                'site' =>$settings
            ];

            return $this->success($data);


        }
        else{
            return $this->error(['message' => 'Site setting not found']);
        }
    }


    public function sitesetting(Request $request)
    {
        $post = [];
            if($request->favicon)
            {
                $request->validate(['favicon' => 'required|image|mimes:jpeg,jpg,png|max:204800']);
                $request->favicon->storeAs('logo', 'favicon.png');
            }
            if(!empty($request->logo))
            {
                $request->validate(['logo' => 'required|image|mimes:jpeg,jpg,png|max:204800']);
                $request->logo->storeAs('logo', 'logo-dark.png');
            }

            if($request->white_logo)
            {
                $request->validate(['white_logo' => 'required|image|mimes:jpeg,jpg,png|max:204800']);
                $request->white_logo->storeAs('logo', 'logo-light.png');
            }

            $rules = [
                'app_name' => 'required|string|max:50',
                'default_language' => 'required|string|max:50',
                'footer_text' => 'required|string|max:50',
            ];

            $validator = \Validator::make(
                $request->all(), $rules
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $arrEnv = [
                'APP_NAME' => $request->app_name,
            ];

            $default_language = $request->has('default_language') ? $request-> default_language : 'en';
            $post['DEFAULT_LANG'] = $default_language;

            $site_rtl = $request->has('site_rtl') ? $request-> site_rtl : 'off';
            $post['SITE_RTL'] = $site_rtl;

            $footer_text = $request->has('footer_text') ? $request-> footer_text : '';
            $post['FOOTER_TEXT'] = $footer_text;

            $gdpr_cookie = $request->has('gdpr_cookie') ? $request-> gdpr_cookie : 'off';

            $post['gdpr_cookie'] = $request-> gdpr_cookie;
            $post['cookie_text'] = $request->gdpr_cookie;


            if(isset($post) && !empty($post) && count($post) > 0)
            {
                $created_at = $updated_at = date('Y-m-d H:i:s');
                foreach($post as $key => $data)
                {
                    DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ', [$data, $key, $request->id, $created_at, $updated_at, ]
                    );
                }
            }
            Artisan::call('config:cache');
	        Artisan::call('config:clear');



        $data = [
            'site' =>[]
        ];

        return $this->success($data);
    }

    public function emailsettingpage(Request $request){
        $user = User::find($request->user_id);

        $arrEnv = [
            'mail_driver' => env('MAIL_DRIVER'),
            'mail_host' => env('MAIL_HOST'),
            'mail_port' => env('MAIL_PORT'),
            'mail_username' => env('MAIL_USERNAME'),
            'mail_password' => env('MAIL_PASSWORD'),
            'mail_encryption' => env('MAIL_ENCRYPTION'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS'),
            'mail_from_name' => env('MAIL_FROM_NAME'),

        ];

        $data = [
            'site' =>$arrEnv
        ];

        return $this->success($data);

    }


    public function emailsetting(Request $request)
    {

            $rules = [
                'mail_driver' => 'required|string|max:50',
                'mail_host' => 'required|string|max:50',
                'mail_port' => 'required|string|max:50',
                'mail_username' => 'required|string|max:50',
                'mail_password' => 'required|string|max:255',
                'mail_encryption' => 'required|string|max:50',
                'mail_from_address' => 'required|string|max:50',
                'mail_from_name' => 'required|string|max:50',
            ];

            $validator = \Validator::make(
                $request->all(), $rules
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $arrEnv = [
                'MAIL_DRIVER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => $request->mail_password,
                'MAIL_ENCRYPTION' => $request->mail_encryption,
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
                'MAIL_FROM_NAME' => $request->mail_from_name,
            ];

            Artisan::call('config:cache');
	        Artisan::call('config:clear');

            if($this->setEnvironmentValue($arrEnv))
            {
                $data = [
                    'email' =>$arrEnv
                ];
                return $this->success($data);
            }
            else
            {
                $data = [
                    'email' =>''
                ];
                return $this->error($data);
            }
    }

    public function recaptchasetting(Request $request)
    {

        $user = \Auth::user();

            $rules = [];

            if($request->recaptcha_module == 'yes')
            {
                $rules['google_recaptcha_key'] = 'required|string|max:50';
                $rules['google_recaptcha_secret'] = 'required|string|max:50';
            }

            $validator = \Validator::make(
                $request->all(), $rules
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $arrEnv = [
                'RECAPTCHA_MODULE' => $request->recaptcha_module ?? 'no',
                'NOCAPTCHA_SITEKEY' => $request->google_recaptcha_key,
                'NOCAPTCHA_SECRET' => $request->google_recaptcha_secret,
            ];

            Artisan::call('config:cache');
	        Artisan::call('config:clear');

            if($this->setEnvironmentValue($arrEnv))
            {
                $data = [
                    'recaptcha' =>$arrEnv
                ];
                return $this->success($data);
            }
            else
            {
                $data = [
                    'recaptcha' => ''
                ];
                return $this->error($data);
            }

    }



    public function CustomFields(Request $request)
    {

        $customfield = CustomField::where('created_by',$request->user_id)->get();

        $data = [
            'custom_field' =>$customfield
        ];

        return $this->success($data);
    }

    public function storeCustomFields(Request $request)
    {

        if($request->id == null){

            $validation = [
                'name' => ['required', 'string', 'max:255'],
                'placeholder' => ['required'],
                'user_id' => ['required'],

            ];
            $request->validate($validation);

            $post = [
                'name' => $request->name,
                'placeholder' => $request->placeholder,
                'created_by' => $request->user_id,

            ];

            $customfield = CustomField::create($post);

            $data = [
                'custom_field' =>$customfield
            ];

            return $this->success($data);

        }else{

            $customfield = CustomField::find($request->id);
            $post = [
                'name' => $request->name,
                'placeholder' => $request->placeholder,
                'created_by' => $request->user_id,
            ];

            $customfield->update($post);

             $data = [
                'custom_field' =>$customfield
            ];

            return $this->success($data);
        }
    }


    public function destroyCustomFields(Request $request)
    {

        $CustomField = CustomField::find($request->id);
        $CustomField->delete();

        $data = [
            'custom_field'=>[],
        ];

        return $this->success($data);

    }

    public function testEmailSend(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'user_mail' => 'required|email',
                               'mail_driver' => 'required',
                               'mail_host' => 'required',
                               'mail_port' => 'required',
                               'mail_username' => 'required',
                               'mail_password' => 'required',
                               'mail_from_address' => 'required',
                               'mail_from_name' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        try
        {
            config(
                [
                    'mail.driver' => $request->mail_driver,
                    'mail.host' => $request->mail_host,
                    'mail.port' => $request->mail_port,
                    'mail.encryption' => $request->mail_encryption,
                    'mail.username' => $request->mail_username,
                    'mail.password' => $request->mail_password,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name,
                ]
            );
            Mail::to($request->user_mail)->send(new EmailTest());
        }
        catch(\Exception $e)
        {
            $data = [
                'is_success' => false,
                'message' => $e->getMessage(),
            ];
            return $this->success($data);
        }

            $data = [
                'is_success' => true,
                'message' => __('Email send Successfully'),
            ];
            return $this->success($data);
    }




    public function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);
        if(count($values) > 0)
        {
            foreach($values as $envKey => $envValue)
            {
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if(!$keyPosition || !$endOfLinePosition || !$oldLine)
                {
                    $str .= "{$envKey}='{$envValue}'\n";
                }
                else
                {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";

        return file_put_contents($envFile, $str) ? true : false;
    }


    public function langList(){


        $lang = User::languages();



        $data = [
            'language' =>$lang
        ];

        return $this->success($data);
    }



}
