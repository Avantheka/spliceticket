<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Utility;
use App\Models\User;
use App\Models\LoginDetails;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function __construct()
    {
        if(!file_exists(storage_path() . "/installed"))
        {
            header('location:install');
            die;
        }


    }


    // config(['captcha.NOCAPTCHA_SECRET' => Utility::getSettingValByName('NOCAPTCHA_SECRET')]);
    // config(['captcha.NOCAPTCHA_SITEKEY' =>Utility::getSettingValByName('NOCAPTCHA_SITEKEY')]);

    public function store(LoginRequest $request)
    {
        if (Utility::getValByName('RECAPTCHA_MODULE') == 'yes') {
            $validation['g-recaptcha-response'] = 'required';
        } else {
            $validation = [];
        }
        $this->validate($request, $validation);

        $request->authenticate();

        $request->session()->regenerate();
        $user = Auth::user();
        if($user->delete_status == 1)
        {
            auth()->logout();
        }

        if($user->is_disable == 0 && \Auth::user()->type != 'Super Admin'){
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your Account is disable, please contact your Administrator.');
        }

        if($user->is_enable_login != 1 && $user->type != 'Super Admin')
        {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account is disabled from admin');
        }

          $ip = $_SERVER['REMOTE_ADDR'];
        //   $ip = '49.36.83.154'; // This is static ip address
          $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));
          $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
          if ($whichbrowser->device->type == 'bot') {
              return;
          }
          $referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
          /* Detect extra details about the user */
          $query['browser_name'] = $whichbrowser->browser->name ?? null;
          $query['os_name'] = $whichbrowser->os->name ?? null;
          $query['browser_language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
          $query['device_type'] = get_device_type($_SERVER['HTTP_USER_AGENT']);
          $query['referrer_host'] = !empty($referrer['host']);
          $query['referrer_path'] = !empty($referrer['path']);

          isset($query['timezone']) ? date_default_timezone_set($query['timezone']) : '';

           $json = json_encode($query);

           $user = \Auth::user();

           if ($user->type != 'Admin' && $user->type != 'Super Admin') {
               $login_detail = LoginDetails::create([
                   'user_id' => $user->id,
                   'ip' => $ip,
                   'date' => date('Y-m-d H:i:s'),
                   'details' => $json,
                   'role' => $user->type,
                   'created_by' => $user->parent,
               ]);
           }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function showLoginForm($lang = '')
    {
        $setting      = Utility::settings();
        if ($lang == '') {
            $lang = $setting['DEFAULT_LANG'] ?? 'en';
        }

        $ref  = 0;
       

        $refCode = User::where('referral_code' , '=', $ref)->first();
        \App::setLocale($lang);

        return view('auth.login', compact('lang','setting','ref'));
    }

    public function showLinkRequestForm($lang = '')
    {
        if ($lang == '') {
            $lang = Utility::getSettingValByName('DEFAULT_LANG') ?? 'en';
        }
        \App::setLocale($lang);

        return view('auth.passwords.email', compact('lang'));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
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
