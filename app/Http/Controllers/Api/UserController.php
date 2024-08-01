<?php

namespace App\Http\Controllers\Api;

use App;
use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAddRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Api\User;

class UserController extends Controller
{

    use ApiResponser;

    public function index(Request $request)
    {

        $users = User::getUser($request->all());

        $data = [
            'users'=>$users,
        ];

        return $this->success($data);
    }

    public function getuser(Request $request)
    {
        $users = User::find($request->id);

        if($users){
                 $users['role'] ='Agent';

        $data = [
            'users'=>$users,
        ];

         $message = "successfull";

        }else{
            $message = "User does not exist";
             $data = [];
        }

        return $this->success($data, $message);
    }




    public function store(Request $request)
    {


        if($request->id == null){

                if(User::where('email',$request->email)->exists() == true){

                           $data = [
                                'massage'=> 'Email already exist.',
                            ];
                        return $this->error($data);
                }



                $post = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'parent' => Auth::user()->getCreatedBy(),
					'type' => 'Agent',
                ];

                if($request->avatar)
                {
                    $avatarName = 'avatar-' . time() . '.' . $request->avatar->getClientOriginalExtension();
                    $request->avatar->storeAs('public', $avatarName);
                    $post['avatar'] = $avatarName;
                }

                $user = User::create($post);


                $data = [
                    'user'=> $user,
                ];

                return $this->success($data);

        }else{


                   $user = User::find($request->id);
                if(User::where('email',$request->email)->exists() == true){

                        $user->name = $request->name;
                        $user->password =  Hash::make($request->password);
                }else{


                        $user->name = $request->name;
                        $user->email = $request->email;
                        $user->password =  Hash::make($request->password);
                }



            if($request->avatar)
            {

                $avatarName = 'avatar-' . time() . '.' . $request->avatar->getClientOriginalExtension();
                $request->avatar->storeAs('public', $avatarName);

                 $user->avatar = $avatarName;
            }

             $user->save();

       

                $user = User::find($request->id);
                $data = [
                    'user'=> $user,
                ];

                return $this->success($data);
        }

    }


    public function update(Request $request, User $user)
    {

    }


    public function destroy(Request $request)
    {

        $user = User::find($request->id);
        $user->delete();

        $data = [
            'user'=>[],
        ];

        return $this->success($data);

    }


}

