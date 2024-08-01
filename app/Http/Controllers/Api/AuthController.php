<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponser;

    public function login(Request $request)
    {
		
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error(['message' => 'Invalid login details']);
        }


        $user = User::where('email', $request['email'])->firstOrFail();
        $user->device_type = $request->device_type;
        $user->token = $request->token;
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'image_url'=> URL('storage/public').'/'.$user->avatar,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];

       

        return $this->success($data);
    }

    public function logout(Request $request)
    {
        $user = User::find($request->id);
        if(!empty($user)){
            $user->tokens()->delete();

            return $this->success(['message' => 'logout successfully']);
        }else{
            return $this->error(['message' => 'Invalid login details']);
        }
    }




}
