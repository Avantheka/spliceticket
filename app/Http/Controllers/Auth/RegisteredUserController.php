<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Utility;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function create()
    {
        return view('auth.register');
    }


    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        do {
            $code = rand(100000, 999999);
        } while (DB::table('users')->where('referral_code', $code)->exists());

        if (Utility::getValByName('verification_btn') == 'off') {

            $date = date("Y-m-d H:i:s");
        } else {
            $date = null;
        }


        if (Utility::getValByName('RECAPTCHA_MODULE') == 'yes') {
            $validation['g-recaptcha-response'] = 'required';
        } else {
            $validation = [];
        }
        $this->validate($request, $validation);
        $slug = str_replace(' ', '-', strtolower($request->name));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'email_verified_at' => $date,
            'password' => Hash::make($request->password),
            'slug' => $slug,
            'type' => 'Admin',
            'lang' => Utility::getValByName('default_language'),
            'parent' => 1,
            'referral_code'=> $code,
            'referral_used'=> !empty($request->ref_code) ? $request->ref_code : '0',
        ]);


        Utility::getSMTPDetails(1);
        $adminRole = Role::findByName('Admin');
        $user->assignRole($adminRole);
        $user->userDefaultData();
        $user->assignPlan(1);
        $userDefaultData = Utility::addCustomeField($user->id);
        $userDefaultData = Utility::userDefaultData();
        $user->$userDefaultData;
        $user->userDefaultDataRegister($user->id);



        if (Utility::getValByName('verification_btn') == 'on') {
            try {

                event(new Registered($user));

                Auth::login($user);
            } catch (\Exception $e) {
                $user->delete();
                return redirect('/register')->with('status', __('Email SMTP settings does not configure so please contact to your site admin.'));
            }

        } else {
            $uArr = [
                'email' => $user->email,
                'password' => $request->password,
            ];

            Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr, $user->id);
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }

        \App::setLocale($lang);

        return view('auth.verify-email', compact('lang'));

    }

    public function showRegistrationForm($ref = '' , $lang = '')
    {


        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }
        \App::setLocale($lang);
        if($ref == '')
        {
            $ref = 0;
        }

        $refCode = User::where('referral_code' , '=', $ref)->first();

        if($refCode->referral_code != $ref)
        {
            return redirect()->route('register');
        }
        return view('auth.register', compact('lang','ref'));
    }

}
