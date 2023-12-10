<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
   public function register(Request $request)
   {
       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),

       ]);
       try {
           $token = $user->createToken('Token')->accessToken;
       } catch (Exception $e){
           return response()->json(['error' => 'Could not create token'], 500);
       }
       return response()->json(['token'=>$token , 'user' => $user],200);
   }
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (auth()->attempt($data))
        {
            $token = auth()->user()->createToken('Token')->accessToken;
            return response()->json(['token'=>$token,],200);
        }else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getUser()
    {
        $user = \auth()->user();
        return response()->json(['user' => $user], 401);
    }
}
