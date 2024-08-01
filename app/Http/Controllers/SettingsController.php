<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Mail\EmailTest;
use App\Models\CustomDomainRequest;
use App\Models\Setting;
use App\Models\Settings;
use App\Models\Plan;
use App\Models\Webhook;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Artisan;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();

        if ($user->can('manage-setting')) {

            // $lang         = $user->languages();
            $customFields = CustomField::where('created_by', \Auth::user()->id)->orderBy('order')->get();
            $settings      = Utility::settings();
            $timezones               = config('timezones');



            $webhooks = Webhook::where('created_by', \Auth::user()->id)->get();
            $store_settings = Settings::where('id', $user->created_by)->first();

            $serverName = str_replace(
                [
                    'http://',
                    'https://',
                ],
                '',
                env('APP_URL')
            );

            $serverIp = gethostbyname($serverName);
            if ($serverIp == $_SERVER['SERVER_ADDR']) {
                $serverIp;
            } else {
                $serverIp = request()->server('SERVER_ADDR');
            }

            $plan = Plan::where('id', $user->plan)->first();
            $app_url = trim(env('APP_URL'), '/');
            $store_settings['store_url'] = $app_url . '/' . \Auth::user()->slug . '/tickets';
            // $store_settings['store_url'] = $app_url .'/'.'admin'.'/tickets';

            if (!empty($plan->enable_subdomain) && $plan->enable_subdomain == 'on') {
                // Remove the http://, www., and slash(/) from the URL
                $input = env('APP_URL');

                // If URI is like, eg. www.way2tutorial.com/
                $input = trim($input, '/');
                // If not have http:// or https:// then prepend it
                if (!preg_match('#^http(s)?://#', $input)) {
                    $input = 'http://' . $input;
                }

                $urlParts = parse_url($input);

                // Remove www.
                $subdomain_name = preg_replace('/^www\./', '', $urlParts['host']);
                // Output way2tutorial.com

            } else {
                $subdomain_name = str_replace(
                    [
                        'http://',
                        'https://',
                    ],
                    '',
                    env('APP_URL') . '/' .\Auth::user()->slug . '/tickets',

                );
            }
            return view('admin.users.setting', compact('customFields', 'timezones', 'settings', 'plan', 'subdomain_name', 'webhooks', 'store_settings', 'serverIp'));
        } else {
            return view('403');
        }
    }


    public function store(Request $request)
    {

        $user = \Auth::user();
        $post = [];
        if ($user->can('manage-setting')) {
            if ($request->favicon) {
                $request->validate(['favicon' => 'required|image|mimes:jpeg,jpg,png|max:204800']);
                $request->favicon->storeAs('logo', 'favicon.png');
            }

            if (!empty($request->logo)) {
                $request->validate(['logo' => 'required|image|mimes:jpeg,jpg,png|max:204800']);
                $request->logo->storeAs('logo', 'logo-dark.png');
            }

            if ($request->white_logo) {
                $request->validate(['white_logo' => 'required|image|mimes:jpeg,jpg,png|max:204800']);
                $request->white_logo->storeAs('logo', 'logo-light.png');
            }

            $rules = [
                'app_name' => 'required|string|max:50',
                'default_language' => 'required|string|max:50',
                'footer_text' => 'required|string|max:50',
            ];

            $validator = \Validator::make(
                $request->all(),
                $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            $default_language = $request->has('default_language') ? $request->default_language : 'en';
            $post['DEFAULT_LANG'] = $default_language;

            $site_rtl = $request->has('site_rtl') ? $request->site_rtl : 'off';
            $post['SITE_RTL'] = $site_rtl;

            $footer_text = $request->has('footer_text') ? $request->footer_text : '';
            $post['FOOTER_TEXT'] = $footer_text;

            $faq = $request->faq ? $request->faq : 'off';
            $post['FAQ'] = $faq;

            $knowledge_base = $request->has('knowledge') ? $request->knowledge : 'off';
            $post['Knowlwdge_Base'] = $knowledge_base;

            $cust_theme_bg = (!empty($request->cust_theme_bg)) ? 'on' : 'off';
            $post['cust_theme_bg'] = $cust_theme_bg;

            $SITE_RTL = (!empty($request->SITE_RTL)) ? 'on' : 'off';
            $post['SITE_RTL'] = $SITE_RTL;

            $cust_darklayout = !empty($request->cust_darklayout) ? 'on' : 'off';
            $post['cust_darklayout'] = $cust_darklayout;

            if (isset($post) && !empty($post) && count($post) > 0) {
                $created_at = $updated_at = date('Y-m-d H:i:s');
                foreach ($post as $key => $data) {
                    DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                        [$data, $key, Auth::user()->id, $created_at, $updated_at,]
                    );
                }
            }

            return redirect()->back()->with('success', __('Setting updated successfully'));

            Artisan::call('config:cache');
            Artisan::call('config:clear');
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function saveBusinessSettings(Request $request)
    {
        $user = \Auth::user();

        if ($user->type == 'Super Admin') {


            if ($request->logo) {
                $request->validate(
                    [
                        'logo' => 'image',
                    ]
                );
                $logoName = 'logo-dark.png';
                $dir = 'uploads/logo/';
                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'logo', $logoName, $dir, $validation);

                if ($path['flag'] == 1) {
                    $logo = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $logoName,
                        'logo',
                        $user->createId(),
                    ]
                );
            }
            if ($request->white_logo) {
                $request->validate(
                    [
                        'white_logo' => 'image',
                    ]
                );
                $lightlogoName = 'logo-light.png';
                $dir = 'uploads/logo/';
                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'white_logo', $lightlogoName, $dir, $validation);
                if ($path['flag'] == 1) {
                    $white_logo = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $lightlogoName,
                        'white_logo',
                        $user->createId(),
                    ]
                );
            }
            if ($request->favicon) {
                $request->validate(
                    [
                        'favicon' => 'image',
                    ]
                );

                $favicon = 'favicon.png';
                $dir = 'uploads/logo/';
                $validation = [
                    'mimes:' . 'png',
                    'max:' . '20480',
                ];
                $path = Utility::upload_file($request, 'favicon', $favicon, $dir, $validation);
                if ($path['flag'] == 1) {
                    $favicon = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $favicon,
                        'favicon',
                        $user->createId(),
                    ]
                );
            }
            if (!empty($request->title_text) || !empty($request->footer_text) || !empty($request->default_language) || !empty($request->color) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout) || !empty($request->SITE_RTL || !empty($request->display_landing))) {

                $post = $request->all();
                // if($request-> color ) {
                //     $post['color'] = $request-> color;
                // }
                if (isset($request->color) && $request->color_flag == 'false') {
                    $post['color'] = $request->color;
                } else {
                    $post['color'] = $request->custom_color;
                }

                $post['color_flag'] = $request->color_flag;

                unset($post['_token'], $post['white_logo'], $post['logo'], $post['favicon']);

                $SITE_RTL = $request->has('SITE_RTL') ? $request->SITE_RTL : 'off';
                $post['SITE_RTL'] = $SITE_RTL;

                $SIGNUP = $request->has('SIGNUP') ? $request->SIGNUP : 'off';
                $post['SIGNUP'] = $SIGNUP;

                $arrEnv = [
                    'APP_NAME' => $request->app_name,
                    'FOOTER_TEXT' => $request->footer_text,
                ];

                //  Utility::setEnvironmentValue($arrEnv);
                $this->setEnvironmentValue($arrEnv);


                $default_language = $request->has('default_language') ? $request->default_language : 'en';
                $post['DEFAULT_LANG'] = $default_language;

                $verification_btn = $request->has('verification_btn') ? $request->verification_btn : 'off';
                $post['verification_btn'] = $verification_btn;

                $display_landing = $request->has('display_landing') ? $request->display_landing : 'off';
                $post['display_landing'] = $display_landing;

                // $footer_text = $request->has('footer_text') ? $request-> footer_text : '';
                // $post['FOOTER_TEXT'] = $footer_text;

                $post['cust_darklayout'] = isset($request->cust_darklayout) ? $request->cust_darklayout : 'off';
                $post['cust_theme_bg'] = isset($request->cust_theme_bg) ? $request->cust_theme_bg : 'off';


                $created_at = date('Y-m-d H:i:s');
                $updated_at = date('Y-m-d H:i:s');
                foreach ($post as $key => $data) {

                    \DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                        [
                            $data,
                            $key,
                            \Auth::user()->createId(),
                            $created_at,
                            $updated_at,
                        ]
                    );
                }
            }
        } elseif ($user->type == 'Admin') {


            if ($request->company_logo) {
                $request->validate(
                    [
                        'company_logo' => 'image',
                    ]
                );
                $logoNames = 'logo_dark.png';
                $logoName = $user->id . 'logo_dark.png';

                $image_size = $request->file('company_logo')->getSize();
                $result = Utility::updateStorage(\Auth::user()->createId(), $image_size);
                if ($result == 1) {

                    $dir = 'uploads/logo/';
                    $validation = [
                        'mimes:' . 'png',
                        'max:' . '20480',
                    ];
                    $path = Utility::upload_file($request, 'company_logo', $logoName, $dir, $validation);
                    if ($path['flag'] == 1) {
                        $company_logo = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $logoName,
                        'company_logo',
                        \Auth::user()->createId(),
                    ]
                );
            }
            if ($request->company_logo_light) {
                $request->validate(
                    [
                        'company_logo_light' => 'image',
                    ]
                );
                $logoNames = 'logo-light.png';
                $logoName_light = $user->id . 'logo-light.png';

                $image_size = $request->file('company_logo_light')->getSize();
                $result = Utility::updateStorage(\Auth::user()->createId(), $image_size);
                if ($result == 1) {

                    $dir = 'uploads/logo/';
                    $validation = [
                        'mimes:' . 'png',
                        'max:' . '20480',
                    ];
                    $path = Utility::upload_file($request, 'company_logo_light', $logoName_light, $dir, $validation);
                    if ($path['flag'] == 1) {
                        $company_logo_light = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $logoName_light,
                        'company_logo_light',
                        \Auth::user()->createId(),
                    ]
                );
            }
            if ($request->company_favicon) {
                $request->validate(
                    [
                        'company_favicon' => 'image',
                    ]
                );
                $favicon  = $user->id . '_favicon.png';


                $image_size = $request->file('company_favicon')->getSize();
                $result = Utility::updateStorage(\Auth::user()->createId(), $image_size);
                if ($result == 1) {

                    $dir = 'uploads/logo/';
                    $validation = [
                        'mimes:' . 'png',
                        'max:' . '20480',
                    ];
                    $path = Utility::upload_file($request, 'company_favicon', $favicon, $dir, $validation);

                    if ($path['flag'] == 1) {
                        $company_favicon = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }



                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $favicon,
                        'company_favicon',
                        \Auth::user()->createId(),
                    ]
                );
            }

            $faq = $request->faq ? $request->faq : 'off';
            $post['FAQ'] = $faq;

            $knowledge_base = $request->has('knowledge') ? $request->knowledge : 'off';
            $post['Knowlwdge_Base'] = $knowledge_base;



            if (!empty($request->title_text) || !empty($request->footer_text) || !empty($request->default_language) || !empty($request->color) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout)  || !empty($request->SITE_RTL || !empty($request->color_flag))) {


                $footer_text = $request->has('footer_text') ? $request->footer_text : '';
                $post['FOOTER_TEXT'] = $footer_text;

                $app_name = $request->has('app_name') ? $request->app_name : '';
                $post['App_Name'] = $app_name;

                $default_language = $request->has('default_language') ? $request->default_language : 'en';
                $post['DEFAULT_LANG'] = $default_language;

                // if($request-> color ) {
                //     $post['color'] = $request-> color;
                // }


                if (isset($request->color_flag) && $request->color_flag == 'false') {
                    $post['color'] = $request->color;
                } else {
                    $post['color'] = $request->custom_color;
                }
                $post['color_flag'] = $request->color_flag;

                $post['cust_darklayout'] = isset($request->cust_darklayout) ? $request->cust_darklayout : 'off';
                $post['cust_theme_bg'] = isset($request->cust_theme_bg) ? $request->cust_theme_bg : 'off';

                $SITE_RTL = $request->has('SITE_RTL') ? $request->SITE_RTL : 'off';
                $post['SITE_RTL'] = $SITE_RTL;



                unset($post['_token'], $post['company_logo'], $post['company_logo_light'], $post['company_favicon']);

                foreach ($post as $key => $data) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                        [
                            $data,
                            $key,
                            $user->createId(),
                        ]
                    );
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return redirect()->back()->with('success', __('Brand setting succefully saved.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
    }


    public function emailSettingStore(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'Super Admin') {
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
                $request->all(),
                $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post = $request->all();

            unset($post['_token']);

            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {

                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->createId(),
                        $created_at,
                        $updated_at,
                    ]
                );
            }
            return redirect()->back()->with('success', __('Email Settings updated successfully'));

            Artisan::call('config:cache');
            Artisan::call('config:clear');
        } elseif ($user->type == 'Admin') {


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
                $request->all(),
                $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post = $request->all();

            unset($post['_token']);

            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {

                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->createId(),
                        $created_at,
                        $updated_at,
                    ]
                );
            }
            return redirect()->back()->with('success', __('Email Settings updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function recaptchaSettingStore(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('manage-setting')) {
            $rules = [];

            if ($request->recaptcha_module == 'yes') {
                $rules['google_recaptcha_key'] = 'required|string|max:50';
                $rules['google_recaptcha_secret'] = 'required|string|max:50';
            }

            $validator = \Validator::make(
                $request->all(),
                $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $post = [];

            $enable_recaptcha = (!empty($request->recaptcha_module)) ? 'yes' : 'no';
            $post['RECAPTCHA_MODULE'] = $enable_recaptcha;

            $google_recaptcha_key = $request->has('google_recaptcha_key') ? $request->google_recaptcha_key : '';
            $post['NOCAPTCHA_SITEKEY'] = $google_recaptcha_key;

            $google_recaptcha_secret = $request->has('google_recaptcha_secret') ? $request->google_recaptcha_secret : '';
            $post['NOCAPTCHA_SECRET'] = $google_recaptcha_secret;



            if (isset($post) && !empty($post) && count($post) > 0) {
                $created_at = $updated_at = date('Y-m-d H:i:s');

                foreach ($post as $key => $data) {

                    \DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                        [$data, $key, Auth::user()->id, $created_at, $updated_at,]
                    );
                }
            }
            return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function pusherSettingStore(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('manage-setting')) {
            $rules = [];

            if ($request->enable_chat == 'yes') {
                $rules['pusher_app_id']      = 'required|string|max:50';
                $rules['pusher_app_key']     = 'required|string|max:50';
                $rules['pusher_app_secret']  = 'required|string|max:50';
                $rules['pusher_app_cluster'] = 'required|string|max:50';
            }

            $validator = \Validator::make(
                $request->all(),
                $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $post = [];

            $enable_chat = (!empty($request->enable_chat)) ? 'yes' : 'no';
            $post['CHAT_MODULE'] = $enable_chat;

            $pusher_app_id = $request->has('pusher_app_id') ? $request->pusher_app_id : '';
            $post['PUSHER_APP_ID'] = $pusher_app_id;

            $pusher_app_key = $request->has('pusher_app_key') ? $request->pusher_app_key : '';
            $post['PUSHER_APP_KEY'] = $pusher_app_key;

            $pusher_app_secret = $request->has('pusher_app_secret') ? $request->pusher_app_secret : '';
            $post['PUSHER_APP_SECRET'] = $pusher_app_secret;

            $pusher_app_cluster = $request->has('pusher_app_cluster') ? $request->pusher_app_cluster : '';
            $post['PUSHER_APP_CLUSTER'] = $pusher_app_cluster;


            if (isset($post) && !empty($post) && count($post) > 0) {
                $created_at = $updated_at = date('Y-m-d H:i:s');

                foreach ($post as $key => $data) {

                    \DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                        [$data, $key, Auth::user()->id, $created_at, $updated_at,]
                    );
                }
            }
            return redirect()->back()->with('success', __('Pusher Settings updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public static function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}='{$envValue}'\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";

        return file_put_contents($envFile, $str) ? true : false;
    }

    public function testEmail(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('manage-setting')) {
            $data                      = [];
            $data['mail_driver']       = $request->mail_driver;
            $data['mail_host']         = $request->mail_host;
            $data['mail_port']         = $request->mail_port;
            $data['mail_username']     = $request->mail_username;
            $data['mail_password']     = $request->mail_password;
            $data['mail_encryption']   = $request->mail_encryption;
            $data['mail_from_address'] = $request->mail_from_address;
            $data['mail_from_name']    = $request->mail_from_name;

            return view('admin.users.test_email', compact('data'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function testEmailSend(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'mail_driver' => 'required',
                'mail_host' => 'required',
                'mail_port' => 'required',
                'mail_username' => 'required',
                'mail_password' => 'required',
                'mail_from_address' => 'required',
                'mail_from_name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        try {
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
            Mail::to($request->email)->send(new EmailTest());
        } catch (\Exception $e) {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => $e->getMessage(),
                ]
            );
        }

        return response()->json(
            [
                'is_success' => true,
                'message' => __('Email send Successfully'),
            ]
        );
    }



    public function storeCustomFields(Request $request)
    {
        $rules      = [
            'fields' => 'required|present|array',
        ];
        $attributes = [];

        if ($request->fields) {
            foreach ($request->fields as $key => $val) {
                $rules['fields.' . $key . '.name']      = 'required|max:255';
                $attributes['fields.' . $key . '.name'] = __('Field Name');
            }
        }

        $validator = \Validator::make($request->all(), $rules, [], $attributes);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $field_ids = CustomField::orderBy('order')->pluck('id')->toArray();

        $order = 0;
        foreach ($request->fields as $key => $field) {
            $fieldObj = new CustomField();
            if (isset($field['id']) && !empty($field['id'])) {
                $fieldObj = CustomField::find($field['id']);
                if (($key = array_search($fieldObj->id, $field_ids)) !== false) {
                    unset($field_ids[$key]);
                }
            }
            $fieldObj->name        = $field['name'];
            $fieldObj->placeholder = $field['placeholder'];
            if (isset($field['type']) && !empty($field['type'])) {
                if (isset($fieldObj->id) && $fieldObj->id > 7) {
                    $fieldObj->type = $field['type'];
                } elseif (!isset($fieldObj->id)) {
                    $fieldObj->type = $field['type'];
                }
            }
            $fieldObj->width  = (isset($field['width'])) ? $field['width'] : '12';
            $fieldObj->status = 1;
            if (isset($field['is_required'])) {
                if (isset($fieldObj->id) && $fieldObj->id > 7) {
                    $fieldObj->is_required = $field['is_required'];
                } elseif (!isset($fieldObj->id)) {
                    $fieldObj->is_required = $field['is_required'];
                }
            }
            $fieldObj->created_by = Auth::id();
            $fieldObj->order      = $order++;
            $fieldObj->save();
            if ($fieldObj->custom_id == 0) {

                $fieldObj->custom_id      = $fieldObj->id;
                $fieldObj->save();
            }
        }

        if (!empty($field_ids) && count($field_ids) > 0) {
            CustomField::whereIn('id', $field_ids)->where('status', 1)->delete();
        }

        return redirect()->back()->with('success', __('Fields Saves Successfully.!'));
    }

    public function saveCompanySettings(Request $request)
    {
        if (\Auth::user()->can('manage-company-settings')) {
            $user = \Auth::user();
            $request->validate(
                [
                    'company_name' => 'required|string|max:50',
                    'company_email' => 'required',
                    'company_email_from_name' => 'required|string',

                ]
            );
            $post = $request->all();
            unset($post['_token']);

            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->createId(),
                        $created_at,
                        $updated_at,
                    ]
                );
            }

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function savedomainSettings(Request $request)
    {


        $user = \Auth::user();
        if (\Auth::user()->type == 'Admin') {

              // custom domain code
            if ($request->domain_switch == 'on') {
                $validator = \Validator::make(
                    $request->all(), [
                        'domain' => 'required',
                    ]
                );
            }
            if ($request->custom_setting == 'enable_subdomain') {
                $validator = \Validator::make(
                    $request->all(), [
                        'subdomain' => 'required',
                    ]
                );
            }
            if ($request->domain_switch == 'on' || $request->custom_setting == 'enable_subdomain') {
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
            }


            $post = $request->all();
            unset($post['_token']);
            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {

                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->createId(),
                        $created_at,
                        $updated_at,
                    ]
                );
            }


            if ($request->domain_switch == 'on') {
                $custom_domain_request = CustomDomainRequest::where('user_id', \Auth::user()->createId())->first();
                if ($custom_domain_request) {
                    $custom_domain_request->custom_domain = $request->domain;
                    $custom_domain_request->save();
                } else {
                    $custom_domain_requests = new CustomDomainRequest();
                    $custom_domain_requests->user_id = \Auth::user()->createId();
                    $custom_domain_requests->custom_domain = $request->domain;
                    $custom_domain_requests->status = 0;
                    $custom_domain_requests->save();
                }
            }else
            {
                $custom_domain_request = CustomDomainRequest::where('user_id', \Auth::user()->createId())->first();
                if($custom_domain_request)
                {
                    $custom_domain_request->delete();
                }
            }

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        } else {

            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function savePaymentSettings(Request $request)
    {
        $user = \Auth::user();

        $validator = \Validator::make(
            $request->all(),
            [
                'currency' => 'required|string|max:255',
                'currency_symbol' => 'required|string|max:255',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        } else {

            $post['currency_symbol'] = $request->currency_symbol;
            $post['currency'] = $request->currency;
        }

        if (isset($request->is_manually_enabled) && $request->is_manually_enabled == 'on') {
            $post['is_manually_enabled']     = $request->is_manually_enabled;
        } else {
            $post['is_manually_enabled'] = 'off';
        }


        if (isset($request->is_banktransfer_enabled) && $request->is_banktransfer_enabled == 'on') {
            $validator = \validator::make(
                $request->all(),
                [
                    'bank_details' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['is_banktransfer_enabled']     = $request->is_banktransfer_enabled;
            $post['bank_details'] = $request->bank_details;
        } else {
            $post['is_banktransfer_enabled'] = 'off';
        }

        if (isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'stripe_key' => 'required|string',
                    'stripe_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_stripe_enabled']     = $request->is_stripe_enabled;
            $post['stripe_secret']         = $request->stripe_secret;
            $post['stripe_key']            = $request->stripe_key;
        } else {
            $post['is_stripe_enabled'] = 'off';
        }

        if (isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paypal_mode' => 'required|string',
                    'paypal_client_id' => 'required|string',
                    'paypal_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode']       = $request->paypal_mode;
            $post['paypal_client_id']  = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        } else {
            $post['is_paypal_enabled'] = 'off';
        }

        if (isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        } else {
            $post['is_paystack_enabled'] = 'off';
            $post['paystack_public_key'] = '';
            $post['paystack_secret_key'] = '';
        }


        if (isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_private_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_private_key'] = $request->paymentwall_private_key;
        } else {
            $post['is_paymentwall_enabled'] = 'off';
        }


        if (isset($request->is_toyyibpay_enabled) && $request->is_toyyibpay_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'toyyibpay_secret_key' => 'required|string',
                    'toyyibpay_category_code' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
            $post['toyyibpay_secret_key'] = $request->toyyibpay_secret_key;
            $post['toyyibpay_category_code'] = $request->toyyibpay_category_code;
        } else {
            $post['is_toyyibpay_enabled'] = 'off';
        }

        if (isset($request->is_payfast_enabled) && $request->is_payfast_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'payfast_mode' => 'required',
                    'payfast_merchant_id' => 'required|string',
                    'payfast_merchant_key' => 'required|string',
                    'payfast_signature' => 'required|string',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
            $post['payfast_merchant_id'] = $request->payfast_merchant_id;
            $post['payfast_merchant_key']       = $request->payfast_merchant_key;
            $post['payfast_signature'] = $request->payfast_signature;
            $post['payfast_mode'] = $request->payfast_mode;
        } else {
            $post['is_payfast_enabled'] = 'off';
        }


        if (isset($request->is_iyzipay_enabled) && $request->is_iyzipay_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'iyzipay_key' => 'required|string',
                    'iyzipay_mode' => 'required',
                    'iyzipay_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_iyzipay_enabled']     = $request->is_iyzipay_enabled;
            $post['iyzipay_mode']           = $request->iyzipay_mode;
            $post['iyzipay_key']            = $request->iyzipay_key;
            $post['iyzipay_secret']         = $request->iyzipay_secret;
        } else {
            $post['is_iyzipay_enabled'] = 'off';
        }



        if (isset($request->is_sspay_enabled) && $request->is_sspay_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'sspay_secret_key' => 'required|string',
                    'sspay_category_code' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_sspay_enabled'] = $request->is_sspay_enabled;
            $post['sspay_secret_key'] = $request->sspay_secret_key;
            $post['sspay_category_code'] = $request->sspay_category_code;
        } else {
            $post['is_sspay_enabled'] = 'off';
        }

        if (isset($request->is_paytab_enabled) && $request->is_paytab_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paytab_profile_id' => 'required|string',
                    'paytab_server_key' => 'required|string',
                    'paytab_region' => 'required|string',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytab_enabled'] = $request->is_paytab_enabled;
            $post['paytab_profile_id'] = $request->paytab_profile_id;
            $post['paytab_server_key'] = $request->paytab_server_key;
            $post['paytab_region'] = $request->paytab_region;
        } else {
            $post['is_paytab_enabled'] = 'off';
        }

        if (isset($request->is_benefit_enabled) && $request->is_benefit_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'benefit_api_key' => 'required|string',
                    'benefit_secret_key' => 'required|string',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_benefit_enabled'] = $request->is_benefit_enabled;
            $post['benefit_api_key'] = $request->benefit_api_key;
            $post['benefit_secret_key'] = $request->benefit_secret_key;
        } else {
            $post['is_benefit_enabled'] = 'off';
        }

        if (isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        } else {
            $post['is_flutterwave_enabled'] = 'off';
            $post['flutterwave_public_key'] = '';
            $post['flutterwave_secret_key'] = '';
        }

        if (isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        } else {
            $post['is_razorpay_enabled'] = 'off';
        }

        if (isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on') {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token']     = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        } else {
            $post['is_mercado_enabled'] = 'off';
        }

        if (isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytm_enabled']    = $request->is_paytm_enabled;
            $post['paytm_mode']          = $request->paytm_mode;
            $post['paytm_merchant_id']   = $request->paytm_merchant_id;
            $post['paytm_merchant_key']  = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        } else {
            $post['is_paytm_enabled'] = 'off';
        }

        if (isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key']    = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        } else {
            $post['is_mollie_enabled'] = 'off';
        }

        if (isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on') {



            $validator = \Validator::make(
                $request->all(),
                [
                    'skrill_email' => 'required|email',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email']      = $request->skrill_email;
        } else {
            $post['is_skrill_enabled'] = 'off';
        }

        if (isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode']       = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        } else {
            $post['is_coingate_enabled'] = 'off';
        }

        if (isset($request->is_cashefree_enabled) && $request->is_cashefree_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'cashfree_key' => 'required|string',
                    'cashfree_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_cashefree_enabled'] = $request->is_cashefree_enabled;
            $post['cashfree_key']       = $request->cashfree_key;
            $post['cashfree_secret'] = $request->cashfree_secret;
        } else {
            $post['is_cashefree_enabled'] = 'off';
        }

        if (isset($request->is_aamarpay_enabled) && $request->is_aamarpay_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'aamarpay_store_id' => 'required|string',
                    'aamarpay_signature_key' => 'required|string',
                    'aamarpay_description' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_aamarpay_enabled'] = $request->is_aamarpay_enabled;
            $post['aamarpay_store_id']       = $request->aamarpay_store_id;
            $post['aamarpay_signature_key'] = $request->aamarpay_signature_key;
            $post['aamarpay_description'] = $request->aamarpay_description;
        } else {
            $post['is_aamarpay_enabled'] = 'off';
        }

        if (isset($request->is_paytr_enabled) && $request->is_paytr_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'paytr_merchant_id' => 'required|string',
                    'paytr_merchant_key' => 'required|string',
                    'paytr_merchant_salt' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytr_enabled'] = $request->is_paytr_enabled;
            $post['paytr_merchant_id']       = $request->paytr_merchant_id;
            $post['paytr_merchant_key'] = $request->paytr_merchant_key;
            $post['paytr_merchant_salt'] = $request->paytr_merchant_salt;
        } else {
            $post['is_paytr_enabled'] = 'off';
        }


        if (isset($request->is_yookassa_enabled) && $request->is_yookassa_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'yookassa_shop_id' => 'required|string',
                    'yookassa_secret' => 'required|string',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_yookassa_enabled'] = $request->is_yookassa_enabled;
            $post['yookassa_shop_id'] = $request->yookassa_shop_id;
            $post['yookassa_secret'] = $request->yookassa_secret;
        } else {
            $post['is_yookassa_enabled'] = 'off';
        }

        if (isset($request->is_midtrans_enabled) && $request->is_midtrans_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'midtrans_secret' => 'required|string',


                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['midtrans_mode']           = $request->midtrans_mode;
            $post['is_midtrans_enabled'] = $request->is_midtrans_enabled;
            $post['midtrans_secret'] = $request->midtrans_secret;
        } else {
            $post['is_midtrans_enabled'] = 'off';
        }

        if (isset($request->is_xendit_enabled) && $request->is_xendit_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'is_xendit_enabled' => 'required',
                    'xendit_api' => 'required',
                    'xendit_token' => 'required',

                ]
            );
            // 'midtrans_mode' => 'required',

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_xendit_enabled'] = $request->is_xendit_enabled;
            // $post['midtrans_mode'] = $request->midtrans_mode;
            $post['xendit_token'] = $request->xendit_token;
            $post['xendit_api'] = $request->xendit_api;
        } else {
            $post['is_xendit_enabled'] = 'off';
        }


        if (isset($request->is_paiementpro_enabled) && $request->is_paiementpro_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paiementpro_merchant_id' => 'required|string',


                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['is_paiementpro_enabled'] = $request->is_paiementpro_enabled;
            $post['paiementpro_merchant_id'] = $request->paiementpro_merchant_id;
        } else {
            $post['is_paiementpro_enabled'] = 'off';
        }


        if (isset($request->is_nepalste_enabled) && $request->is_nepalste_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'nepalste_public_key' => 'required|string',
                    'nepalste_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_nepalste_enabled'] = $request->is_nepalste_enabled;
            $post['nepalste_public_key'] = $request->nepalste_public_key;
            $post['nepalste_secret_key'] = $request->nepalste_secret_key;
        } else {
            $post['is_nepalste_enabled'] = 'off';
            $post['nepalste_public_key'] = '';
            $post['nepalste_secret_key'] = '';
        }

        if (isset($request->is_fedapay_enabled) && $request->is_fedapay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'fedapay_mode' => 'required',
                    'fedapay_public_key' => 'required',
                    'fedapay_secret_key' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['is_fedapay_enabled'] = $request->is_fedapay_enabled;
            $post['fedapay_mode']       = $request->fedapay_mode;
            $post['fedapay_secret_key'] = $request->fedapay_secret_key;
            $post['fedapay_public_key']  = $request->fedapay_public_key;

        } else {
            $post['is_fedapay_enabled'] = 'off';
            $post['fedapay_secret_key'] = '';
            $post['fedapay_public_key'] = '';
        }

        if (isset($request->is_cinetpay_enabled) && $request->is_cinetpay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'cinetpay_api_key' => 'required',
                    'cinetpay_secret_key' => 'required',
                    'cinetpay_site_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['is_cinetpay_enabled'] = $request->is_cinetpay_enabled;
            $post['cinetpay_api_key']  = $request->cinetpay_api_key;
            $post['cinetpay_secret_key'] = $request->cinetpay_secret_key;
            $post['cinetpay_site_id'] = $request->cinetpay_site_id;

        } else {
            $post['is_cinetpay_enabled'] = 'off';
        }

        if (isset($request->is_payhere_enabled) && $request->is_payhere_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'payhere_merchant_id' => 'required',
                    'payhere_merchant_secret_key' => 'required',
                    'payhere_app_id' => 'required',
                    'payhere_app_secret_key' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $post['is_payhere_enabled'] = $request->is_payhere_enabled;
            $post['payhere_merchant_id']  = $request->payhere_merchant_id;
            $post['payhere_merchant_secret_key'] = $request->payhere_merchant_secret_key;
            $post['payhere_app_id'] = $request->payhere_app_id;
            $post['payhere_app_secret_key'] = $request->payhere_app_secret_key;


        } else {
            $post['is_payhere_enabled'] = 'off';
        }


        foreach ($post as $key => $data) {
            $arr = [
                $data,
                $key,
                $user->id,
            ];

            $insert_payment_setting = \DB::insert(
                'insert into admin_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }
        return redirect()->back()->with('success', 'Payment setting successfully updated.');
    }

    public function storageSettingStore(Request $request)
    {

        if (isset($request->storage_setting) && $request->storage_setting == 'local') {
            $request->validate(
                [

                    'local_storage_validation' => 'required',
                    'local_storage_max_upload_size' => 'required',
                ]
            );

            $post['storage_setting'] = $request->storage_setting;
            $local_storage_validation = implode(',', $request->local_storage_validation);
            $post['local_storage_validation'] = $local_storage_validation;
            $post['local_storage_max_upload_size'] = $request->local_storage_max_upload_size;
        }

        if (isset($request->storage_setting) && $request->storage_setting == 's3') {
            $request->validate(
                [
                    's3_key'                  => 'required',
                    's3_secret'               => 'required',
                    's3_region'               => 'required',
                    's3_bucket'               => 'required',
                    's3_url'                  => 'required',
                    's3_endpoint'             => 'required',
                    's3_max_upload_size'      => 'required',
                    's3_storage_validation'   => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['s3_key']                     = $request->s3_key;
            $post['s3_secret']                  = $request->s3_secret;
            $post['s3_region']                  = $request->s3_region;
            $post['s3_bucket']                  = $request->s3_bucket;
            $post['s3_url']                     = $request->s3_url;
            $post['s3_endpoint']                = $request->s3_endpoint;
            $post['s3_max_upload_size']         = $request->s3_max_upload_size;
            $s3_storage_validation              = implode(',', $request->s3_storage_validation);
            $post['s3_storage_validation']      = $s3_storage_validation;
        }

        if (isset($request->storage_setting) && $request->storage_setting == 'wasabi') {
            $request->validate(
                [
                    'wasabi_key'                    => 'required',
                    'wasabi_secret'                 => 'required',
                    'wasabi_region'                 => 'required',
                    'wasabi_bucket'                 => 'required',
                    'wasabi_url'                    => 'required',
                    'wasabi_root'                   => 'required',
                    'wasabi_max_upload_size'        => 'required',
                    'wasabi_storage_validation'     => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['wasabi_key']                 = $request->wasabi_key;
            $post['wasabi_secret']              = $request->wasabi_secret;
            $post['wasabi_region']              = $request->wasabi_region;
            $post['wasabi_bucket']              = $request->wasabi_bucket;
            $post['wasabi_url']                 = $request->wasabi_url;
            $post['wasabi_root']                = $request->wasabi_root;
            $post['wasabi_max_upload_size']     = $request->wasabi_max_upload_size;
            $wasabi_storage_validation          = implode(',', $request->wasabi_storage_validation);
            $post['wasabi_storage_validation']  = $wasabi_storage_validation;
        }

        foreach ($post as $key => $data) {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }

        return redirect()->back()->with('success', 'Storage setting successfully updated.');
    }

    public function saveSEOSettings(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'meta_keywords' => 'required',
                'meta_description' => 'required',
                'meta_image' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        if ($request->meta_image) {
            $img_name = time() . '_' . 'meta_image.png';
            $dir = 'uploads/metaevent/';
            $validation = [
                'max:' . '20480',
            ];
            $path = Utility::upload_file($request, 'meta_image', $img_name, $dir, $validation);
            if ($path['flag'] == 1) {
                $logo_dark = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['meta_image']  = $img_name;
        }
        $post['meta_keywords']            = $request->meta_keywords;
        $post['meta_description']            = $request->meta_description;
        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->id,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                ]
            );
        }
        return redirect()->back()->with('success', 'SEO successfully updated.');
    }


    public function slack(Request $request)
    {
        $post = [];
        $post['slack_webhook'] = $request->input('slack_webhook');
        $post['user_notification'] = $request->has('user_notification') ? $request->input('user_notification') : 0;
        $post['ticket_notification'] = $request->has('ticket_notification') ? $request->input('ticket_notification') : 0;
        $post['reply_notification'] = $request->has('reply_notification') ? $request->input('reply_notification') : 0;

        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');


            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->id,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function telegram(Request $request)
    {
        $post = [];
        $post['telegram_accestoken'] = $request->input('telegram_accestoken');
        $post['telegram_chatid'] = $request->input('telegram_chatid');
        $post['telegram_user_notification'] = $request->has('telegram_user_notification') ? $request->input('telegram_user_notification') : 0;
        $post['telegram_ticket_notification'] = $request->has('telegram_ticket_notification') ? $request->input('telegram_ticket_notification') : 0;
        $post['telegram_reply_notification'] = $request->has('telegram_reply_notification') ? $request->input('telegram_reply_notification') : 0;


        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->id,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }




    public function chatgptkey(Request $request)
    {
        if (\Auth::user()->type == 'Super Admin') {
            $user = \Auth::user();

            $post = $request->all();
            $post['chatgpt_key'] = $request->chatgpt_key;

            unset($post['_token']);

            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->createId(),
                        $created_at,
                        $updated_at,
                    ]
                );
            }

            return redirect()->back()->with('success', __('Chatgpykey successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function saveCookieSettings(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'cookie_title' => 'required',
                'cookie_description' => 'required',
                'strictly_cookie_title' => 'required',
                'strictly_cookie_description' => 'required',
                'more_information_description' => 'required',
                'contactus_url' => 'required',
            ]
        );
        // dd($request->all());
        $post = $request->all();
        // dd($post);
        unset($post['_token']);
        if ($request->enable_cookie) {
            $post['enable_cookie'] = 'on';
        } else {
            $post['enable_cookie'] = 'off';
        }
        if ($request->cookie_logging) {
            $post['cookie_logging'] = 'on';
        } else {
            $post['cookie_logging'] = 'off';
        }
        $post['cookie_title']            = $request->cookie_title;
        $post['cookie_description']            = $request->cookie_description;
        $post['strictly_cookie_title']            = $request->strictly_cookie_title;
        $post['strictly_cookie_description']            = $request->strictly_cookie_description;
        $post['more_information_description']            = $request->more_information_description;
        $post['contactus_url']            = $request->contactus_url;
        $settings = Utility::settings();
        foreach ($post as $key => $data) {
            if (in_array($key, array_keys($settings))) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->id,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Cookie setting successfully saved.');
    }

    public function CookieConsent(Request $request)
    {
        $settings = Utility::settings();
        if ($request['cookie']) {
            if ($settings['enable_cookie'] == "on" && $settings['cookie_logging'] == "on") {
                $allowed_levels = ['necessary', 'analytics', 'targeting'];
                $levels = array_filter($request['cookie'], function ($level) use ($allowed_levels) {
                    return in_array($level, $allowed_levels);
                });
                $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
                // Generate new CSV line
                $browser_name = $whichbrowser->browser->name ?? null;
                $os_name = $whichbrowser->os->name ?? null;
                $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
                $device_type = get_device_type($_SERVER['HTTP_USER_AGENT']);
                // $ip = '49.36.83.154';
                $ip = $_SERVER['REMOTE_ADDR']; // your ip address here
                $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));
                $date = (new \DateTime())->format('Y-m-d');
                $time = (new \DateTime())->format('H:i:s') . ' UTC';
                $new_line = implode(',', [$ip, $date, $time, json_encode($request['cookie']), $device_type, $browser_language, $browser_name, $os_name, isset($query) ? $query['country'] : '', isset($query) ? $query['region'] : '', isset($query) ? $query['regionName'] : '', isset($query) ? $query['city'] : '', isset($query) ? $query['zip'] : '', isset($query) ? $query['lat'] : '', isset($query) ? $query['lon'] : '']);
                if (!file_exists(storage_path() . '/uploads/sample/data.csv')) {
                    $first_line = 'IP,Date,Time,Accepted cookies,Device type,Browser language,Browser name,OS Name';
                    file_put_contents(storage_path() . '/uploads/sample/data.csv', $first_line . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
                file_put_contents(storage_path() . '/uploads/sample/data.csv', $new_line . PHP_EOL, FILE_APPEND | LOCK_EX);
                return response()->json('success');
            }
            return response()->json('error');
        } else {
            return redirect()->back();
        }
    }
}

function get_device_type($user_agent)
{
    $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
    $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
    if (preg_match_all($mobile_regex, $user_agent)) {
        return 'mobile';
    } else {
        if (preg_match_all($tablet_regex, $user_agent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
}
