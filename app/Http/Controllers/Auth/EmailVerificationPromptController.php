<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utility;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function __invoke(Request $request,$lang = '')
    {
        $setting = Utility::settings();

        if ($lang == '') {
            $lang = Utility::getSettingValByName('DEFAULT_LANG') ?? 'en';
        }

        \App::setLocale($lang);

        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(RouteServiceProvider::HOME)
                    : view('auth.verify-email',compact('lang'));
    }
}
