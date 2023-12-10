<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
            $user = \auth()->user();
            $token = auth()->user()->createToken('Token')->accessToken;
            return response()->json(['token'=>$token, 'user'=>$user],200);
        }else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getUser()
    {
        $user = \auth()->user();
        return response()->json(['user' => $user], 401);
    }

    public function forgetReset(Request $request)
    {
        try {
            $user = User::where('email',$request->email)->get();

            if (count($user)>0) {
                $token =Str::random();
                $domain = URL::to('/');
                $url = $domain."/password-reset=".$token;

                $data['url'] = $url ;
                $data['email'] = $request->email ;
                $data['title'] = "Password Reset" ;
                $data['body'] = "Please Click On Below Link To Reset Your Password" ;

                Mail::send('forgetPasswordMail',['data'=>$data],function ($massage) use ($data){
                   $massage->to($data['email'])->subject($data['title']);
                });

                $dateTime = Carbon::now()->format('Y-m-d H:i:s');

                PasswordReset::updateOrCreate([
                    ['email'=>$request->email],
                    [
                        'email'=>$request->email,
                        'token'=>$token,
                        'create_at'=>$dateTime,
                    ],
                ]);

                return response()->json(['success'=> true, 'massage'=>"Please Check Your Email..."],200);

            }else{
                return response()->json(['success'=> false, 'massage'=>"User Not Found"],401);
            }

        }catch (Exception $e)
        {
            return response()->json(['success'=>false,'massage'=>$e->getMessage()],500);
        }
    }
}
