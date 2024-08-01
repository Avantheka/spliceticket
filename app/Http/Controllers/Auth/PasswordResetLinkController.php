<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\Utility;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create($lang = '')
    {
        $setting      = Utility::settings();
        if ($lang == '') {
            $lang = Utility::getSettingValByName('DEFAULT_LANG') ?? 'en';
        }
        \App::setLocale($lang);
        return view('auth.passwords.email',compact('setting'));
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $settings = Utility::settingsById(1);
        if($settings['mail_driver'] && $settings['mail_host'] && $settings['mail_port'] && $settings['mail_encryption'] && $settings['mail_username'] && $settings['mail_password'] && $settings['mail_from_address'] && $settings['mail_from_name']){

            Utility::getSMTPDetails(1);
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
        }else{
            return redirect()->back()->with('Error','Email SMTP settings does not configured so please contact to your site admin.');
        }

    }
}
