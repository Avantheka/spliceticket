<?php

namespace App\Http\Controllers;

use App;
use App\Http\Requests\UserAddRequest;
use App\Models\User;
use App\Models\LoginDetails;
use App\Models\Plan;
use App\Models\Order;
use App\Models\Category;
use App\Models\UserCatgory;
use Illuminate\Http\Request;
use App\Models\Utility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Lab404\Impersonate\Impersonate;
use Illuminate\Support\Facades\Schema;



class UserController extends Controller
{
    public function __construct()
    {
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = Auth::user();


        if(\Auth::user()->type == "Admin")
        {

            $users = User::with(['categories'])->where('parent', Auth::user()->createId())->get();


            return view('admin.users.index', compact('users'));
        }
        else{

            $users = User::where('parent', Auth::user()->createId())->get();

            return view('admin.users.index', compact('users'));
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();
        $categories = Category::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');
        $user->categories  = explode(',', $user->categories);
        if($user->can('create-users'))
        {

            return view('admin.users.create',compact('categories'));
        }
        else
        {
            return view('403');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
  

    public function store(Request $request)
    {

        $user = \Auth::user();
        if ($user->can('create-users')) {
            $objUser = Auth::user()->createId();

            if (\Auth::user()->type == "Super Admin") {
                $validator = \Validator::make(
                    $request->all(),
                    [

                        'name'    => 'required|string|max:255',
                        'email'   => 'required|string|email|max:255|unique:users',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $type = "Admin";
                $slug = str_replace(' ', '-', strtolower($request->name));

                $user['is_enable_login']       = 0;
                if (!empty($request->password_switch) && $request->password_switch == 'on') {
                    $user['is_enable_login']   = 1;
                    $validator = \Validator::make(
                        $request->all(),
                        ['password' => 'required|min:6']
                    );

                    if ($validator->fails()) {
                        return redirect()->back()->with('error', $validator->errors()->first());
                    }
                }

                $date = date("Y-m-d H:i:s");
                $userpassword               = $request->input('password');
                do {
                    $code = rand(100000, 999999);
                } while (DB::table('users')->where('referral_code', $code)->exists());

                $post = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'email_verified_at' => $date,
                    'password' => !empty($userpassword) ? \Hash::make($userpassword) : null,
                    'type' => $type,
                    'parent' => \Auth::user()->createId(),
                    'slug' => $slug,
                    'is_enable_login' => $request->password_switch == 'on' ? '1' : '0',
                    'referral_code' => $code,

                ];
                if ($request->avatar) {

                    $filenameWithExt = $request->file('avatar')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('avatar')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $dir        = 'public/';
                    $path = Utility::upload_file($request, 'avatar', $fileNameToStore, $dir, []);

                    $url = '';
                    $user['avatar']     = !empty($request->avatar) ? $fileNameToStore : '';
                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {
                        return redirect()->route('users.index', \Auth::user()->id)->with('error', __($path['msg']));
                    }
                    $post['avatar'] = $fileNameToStore;
                }
                $user['avatar']     = !empty($request->avatar) ? $fileNameToStore : '';

                $user = User::create($post);

                $user->userDefaultData();
                $plan = Plan::where('price', 0)->first();
                $user->assignPlan($plan->id);
                $role = Role::find($user->type);
                if ($role) {
                    $user->assignRole($role);
                }
                if ($type == 'Admin') {
                    Utility::addCustomeField($user->id);
                }
                $user_role = Role::where('name', $type)->pluck('id');
                $role = Role::find($user_role);
                if ($role) {
                    $user->assignRole($role);
                    $user->userDefaultDataRegister($user->id);
                }

                // slack //
                $settings  = Utility::settings(\Auth::user()->createId());
                if (isset($settings['user_notification']) && $settings['user_notification'] == 1) {
                    $uArr = [
                        'email' => $user->email,
                        'password' => $request->password,
                        'user_name'  => \Auth::user()->name,
                    ];
                    Utility::send_slack_msg('new_user', $uArr);
                }
                // telegram //
                $settings  = Utility::settings(\Auth::user()->createId());
                if (isset($settings['telegram_user_notification']) && $settings['telegram_user_notification'] == 1) {
                    $uArr = [
                        'email' => $user->email,
                        'password' => $request->password,
                        'user_name'  => \Auth::user()->name,
                    ];
                    Utility::send_telegram_msg('new_user', $uArr);
                }


                $uArr = [
                    'email' => $user->email,
                    'password' => $request->password,
                ];
                //  Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);
                Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr, \Auth::user());

                $module = 'New User';
                $webhook =  Utility::webhookSetting($module, $user->created_by);

                if ($webhook) {
                    $parameter = json_encode($user);
                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status == true) {

                        return redirect()->back()->with('success', __('user successfully created!'));
                    } else {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }
                return redirect()->route('users.index')->with('success', __('User Add Successfully') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
            } elseif (\Auth::user()->type == "Admin") {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'categories' => 'required',
                        'email'   => 'required|string|email|max:255|unique:users',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $type = "Agent";
                $slug = "";

                $user['is_enable_login']       = 0;
                if (!empty($request->password_switch) && $request->password_switch == 'on') {
                    $user['is_enable_login']   = 1;
                    $validator = \Validator::make(
                        $request->all(),
                        ['password' => 'required|min:6']
                    );

                    if ($validator->fails()) {
                        return redirect()->back()->with('error', $validator->errors()->first());
                    }
                }

                $date = date("Y-m-d H:i:s");
                $userpassword               = $request->input('password');
                do {
                    $code = rand(100000, 999999);
                } while (DB::table('users')->where('referral_code', $code)->exists());

                $objUser = User::find($objUser);

                $user = User::find(\Auth::user()->parent);
                $total_user = $objUser->countUsers();


                $plan = Plan::find($objUser->plan);

                if ($total_user < $plan->max_agent || $plan->max_agent == -1) {
                    $post = [
                        'name' => $request->name,
                        'email' => $request->email,
                        'email_verified_at' => $date,
                        'password' => !empty($userpassword) ? \Hash::make($userpassword) : null,
                        'type' => $type,
                        'parent' => \Auth::user()->createId(),
                        'slug' => $slug,
                        'is_enable_login' => $request->password_switch == 'on' ? '1' : '0',
                        'referral_code' => $code,

                    ];
                    if ($request->avatar) {

                        $image_size = $request->file('avatar')->getSize();
                        $result = Utility::updateStorage(\Auth::user()->createId(), $image_size);
                        if ($result == 1) {
                            $filenameWithExt = $request->file('avatar')->getClientOriginalName();
                            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                            $extension       = $request->file('avatar')->getClientOriginalExtension();
                            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                            $dir        = 'public/';
                            $path = Utility::upload_file($request, 'avatar', $fileNameToStore, $dir, []);
                            $url = '';
                            // dd($path);
                            $user['avatar']     = !empty($request->avatar) ? $fileNameToStore : '';
                            if ($path['flag'] == 1) {
                                $url = $path['url'];
                            } else {
                                return redirect()->route('users.index', \Auth::user()->id)->with('error', __($path['msg']));
                            }
                            $post['avatar'] = $fileNameToStore;
                        }
                    }
                    $user['avatar']     = !empty($request->avatar) ? $fileNameToStore : '';

                    $user = User::create($post);
                    foreach($request->categories as $value)
                    {
                        $category = UserCatgory::create([
                        'user_id' => $user->id,
                        'category_id' => $value
                        ]);
                    }
                    $user->userDefaultData();
                    $plan = Plan::where('price', 0)->first();
                    $user->assignPlan($plan->id);
                    $role = Role::find($user->type);
                    if ($role) {
                        $user->assignRole($role);
                    }
                    // if ($type == 'Admin') {
                    //     Utility::addCustomeField($user->id);
                    // }
                    $user_role = Role::where('name', $type)->pluck('id');
                    $role = Role::find($user_role);
                    if ($role) {
                        $user->assignRole($role);
                        $user->userDefaultDataRegister($user->id);
                    }
                } else {
                    return redirect()->route('users.index')->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
                // slack //
                $settings  = Utility::settings(\Auth::user()->createId());
                if (isset($settings['user_notification']) && $settings['user_notification'] == 1) {
                    $uArr = [
                        'email' => $user->email,
                        'password' => $request->password,
                        'user_name'  => \Auth::user()->name,
                    ];
                    Utility::send_slack_msg('new_user', $uArr);
                }
                // telegram //
                $settings  = Utility::settings(\Auth::user()->createId());
                if (isset($settings['telegram_user_notification']) && $settings['telegram_user_notification'] == 1) {
                    $uArr = [
                        'email' => $user->email,
                        'password' => $request->password,
                        'user_name'  => \Auth::user()->name,
                    ];
                    Utility::send_telegram_msg('new_user', $uArr);
                }


                $uArr = [
                    'email' => $user->email,
                    'password' => $request->password,
                ];
                //  Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr);
                Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $uArr, \Auth::user());

                $module = 'New User';
                $webhook =  Utility::webhookSetting($module, $user->created_by);

                if ($webhook) {
                    $parameter = json_encode($user);
                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status == true) {

                        return redirect()->back()->with('success', __('user successfully created!'));
                    } else {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }
                return redirect()->route('users.index')->with('success', __('User Add Successfully') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
            }
            else {
                return view('403');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $userObj = \Auth::user();
        $categories = Category::where('created_by',\Auth::user()->createId())->get()->pluck('name','id');
        $userCatgory = UserCatgory::where('user_id',$user->id)->get()->pluck('category_id');
        $categories->prepend(__('Select Category'), '');
        $userObj->categories  = explode(',', $userObj->categories);
        if($userObj->can('edit-users') || $user->id == $userObj->id)
        {
            $roles = Role::get();

            return view('admin.users.edit', compact('user', 'userObj', 'categories','roles','userCatgory'));
        }
        else
        {
            return view('403');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user , Category $category)
    {
        $userObj = \Auth::user();
        if($userObj->can('edit-users') || $user->id == $userObj->id)
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => ['required',
                                Rule::unique('users')->where(function ($query)  use ($user) {
                                return $query->whereNotIn('id',[$user->id])->where('parent',  \Auth::user()->createId());
                            })
                ],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user->name  = $request->name;
            $user->email = $request->email;

            if($request->categories)
            {
                UserCatgory::where('user_id',$user->id)->delete();
                foreach($request->categories as $value)
                {
                    $category = UserCatgory::create([
                    'user_id' => $user->id,
                    'category_id' => $value
                    ]);
                }
            }


            // if($request->password)
            // {
            //     $user->update(['password' => Hash::make($request->password)]);
            // }


            if($request->avatar)
            {
                if(\Auth::user()->type == "Super Admin")
                {
                    $request->validate(['avatar' => 'required|image']);


                    $filenameWithExt = $request->file('avatar')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('avatar')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $url = '';
                    $dir        = 'public/';

                    $path = Utility::upload_file($request,'avatar',$fileNameToStore,$dir,[]);
                    if($path['flag'] == 1){
                        $url = $path['url'];
                    }else{
                        return redirect()->route('users.index', \Auth::user()->id)->with('error', __($path['msg']));
                    }
                    $user->update(['avatar' => $fileNameToStore]);
                }
                else{
                    $request->validate(['avatar' => 'required|image']);
                    $image_size = $request->file('avatar')->getSize();
                    $result = Utility::updateStorage(\Auth::user()->createId(), $image_size);
                    if($result == 1) {

                        $file_path = 'public/' . $user->avatar;
                        Utility::changeStorageLimit(\Auth::user()->createId(), $file_path);
                        $imageName = time() . '.' . $request->avatar->extension();
                        $dir        = 'public/';
                        $path = Utility::upload_file($request,'avatar',$imageName,$dir,[]);
                        $user->avatar = 'public/' . $imageName;

                        if($path['flag'] == 1){
                                $url = $path['url'];
                            }else{
                                return redirect()->route('users.index', \Auth::user()->id)->with('error', __($path['msg']));
                            }
                            $user->update(['avatar' => $imageName]);
                    }
                }
            }
            $user->save();

            return redirect()->route('users.index')->with('success', __('User updated Successfully'). ((isset($result) && $result!=1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
        }
        else
        {
            return view('403');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $objUser = \Auth::user();
        if($objUser->can('delete-users'))
        {
            $user = User::find($id);

            if ($user) {
                if(\Auth::user()->type == "Super Admin")
                {
                    $company_employe = User::where('parent', $user->id)->get();
                    foreach ($company_employe as $key => $agent) {
                        $agent->delete();
                    }
                    $user->delete();
                    return redirect()->route('users.index')->with('success', __('User deleted successfully'));

                }
                else {
                    $user->delete();

                    $file_path = 'public/' . $user->avatar;
                    $result = Utility::changeStorageLimit(\Auth::user()->createId(), $file_path);

                    return redirect()->route('users.index')->with('success', __('User deleted successfully'));

                }

            }
        }
        else
        {
            return view('403');
        }
    }



    public function roles()
    {
        return response()->json(Role::get());
    }


    public function userPassword($id)
    {
        $eId  = \Crypt::decrypt($id);
        $user = User::find($eId);

        $employee = User::where('id', $eId)->first();

        return view('admin.users.reset', compact('user', 'employee'));
    }

    public function userPasswordReset(Request $request, $id)
    {

        $validator = \Validator::make(
            $request->all(), [
                               'password' => 'required|confirmed|same:password_confirmation',
                           ]
        );
        if($validator->fails())
        {
            // dd('fails');
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $user      = User::where('id', $id)->first();

        $user->forceFill([
            'password' => Hash::make($request->password),
            ])->save();

        return redirect()->back()->with('success', 'User Password successfully updated.');
    }

    public function upgradePlan($user_id)
    {
        $user = User::find($user_id);

        $plans = Plan::get();

        return view('admin.users.plan', compact('user', 'plans'));
    }

    public function activePlan($user_id, $plan_id)
    {

        $user       = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id);
        $plan       = Plan::find($plan_id);
        if($assignPlan['is_success'] == true && !empty($plan))
        {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => Utility::getValByName('site_currency'),
                    'txn_id' => '',
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'payment_type' => __('Manually'),
                    'user_id' => $user->id,
                ]
            );

            return redirect()->back()->with('success', 'Plan successfully upgraded.');
        }
        else
        {
            return redirect()->back()->with('error', 'Plan fail to upgrade.');
        }

    }


    public function userlog(Request $request)
    {

       $objUser = \Auth::user();
       $time = date_create($request->month);
       $firstDayofMOnth = (date_format($time, 'Y-m-d'));
       $lastDayofMonth =    \Carbon\Carbon::parse($request->month)->endOfMonth()->toDateString();

       $usersList = User::where('parent', '=', $objUser->createId())->whereNotIn('type', ['Super Admin', 'Admin'])->get()->pluck('name', 'id');
       $usersList->prepend('All User', '');

       if ($request->month == null) {
           $users = DB::table('login_details')
               ->join('users', 'login_details.user_id', '=', 'users.id')
               ->select(DB::raw('login_details.*, users.name as user_name , users.email as user_email'))
               ->where(['login_details.created_by' => $objUser->id]);

       } else {
           $users = DB::table('login_details')
               ->join('users', 'login_details.user_id', '=', 'users.id')
               ->select(DB::raw('login_details.*, users.name as user_name , users.email as user_email'))
               ->where(['login_details.created_by' => $objUser->id]);
       }

       if (!empty($request->month)) {
           $users->where('date', '>=', $firstDayofMOnth);
           $users->where('date', '<=', $lastDayofMonth);
       }
       if (!empty($request->user)) {
           $users->where(['user_id'  => $request->user]);
       }
    //    $users = $users->get();
    $users = $users->orderBy('id', 'desc')->get();
        return view('admin.users.userLog',compact('users' , 'usersList'));
    }


    public function userlogview($id){
        $userlog = LoginDetails::find($id);
        return view('admin.users.viewUserLog', compact('userlog'));
    }

    public function userlogDestroy($id){
        $userlog = LoginDetails::find($id);
        $userlog->delete();
        return redirect()->back()->with('success', 'User Log Deleted Successfully.');
    }

    public function ExitCompany(Request $request)
    {
        \Auth::user()->leaveImpersonation($request->user());
        return redirect('/home');
    }
    public function UserInfo($id)
    {
        $userData = User::where('parent',$id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')->first();

        return view('admin.users.admin_info',compact('userData'  ,'id'));
    }

    public function UserUnable(Request $request)
    {
        User::where('id', $request->id)->update(['is_disable' => $request->is_disable]);
        $userData = User::where('parent',$request->company_id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users, id')->first();
        if($request->is_disable == 1){


            return response()->json(['success' => __('Successfully Unable.'),'userData' => $userData]);

        }else
        {
            return response()->json(['success' => __('Successfull Disable.'),'userData' => $userData]);
        }
    }

    public function LoginWithCompany(Request $request, User $user,  $id)
    {
        $user = User::find($id);
        if ($user && auth()->check()) {
            Impersonate::take($request->user(), $user);
            return redirect('/home');
        }
    }


    public function LoginManage($id)
    {

            $eId        = \Crypt::decrypt($id);
            $user = User::find($eId);
            if($user->is_enable_login == 1)
            {
                $user->is_enable_login = 0;
                $user->save();
                return redirect()->route('users.index')->with('success', 'User login disable successfully.');
            }
            else
            {
                $user->is_enable_login = 1;
                $user->save();
                return redirect()->route('users.index')->with('success', 'User login enable successfully.');
            }


    }


    public function profile()
    {
        $user = \Auth::user();

        return view('admin.users.profile', compact('user'));
    }


    public function editprofile(Request $request,  $id)
    {


            $userObj = \Auth::user();
            if($userObj->can('edit-users') || $id == $userObj->id)
            {
                $user = User::find($id);
                $user->name  = $request->name;
                $user->email = $request->email;

                if($request->password)
                {
                    $user->update(['password' => Hash::make($request->password)]);
                }

                if($request->avatar)
                {

                    $request->validate(['avatar' => 'required|image']);


                    $filenameWithExt = $request->file('avatar')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('avatar')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $url = '';
                    $dir        = 'public/';

                    $path = Utility::upload_file($request,'avatar',$fileNameToStore,$dir,[]);
                    if($path['flag'] == 1){
                        $url = $path['url'];
                    }else{
                        return redirect()->route('users.index', \Auth::user()->id)->with('error', __($path['msg']));
                    }
                    $user->update(['avatar' => $fileNameToStore]);
                }
                $user->update();

                return redirect()->back()->with('success', __('User updated successfully'));
            }
            else
            {
                return view('403');
            }
    }


}

