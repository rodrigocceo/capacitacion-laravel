<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SocialLogin;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean',
        ]);

        $credentials = request(['email', 'password']);
        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at->toDateTimeString(),
        ]);
    }

    public function user(Request $request){
        return response()->json([
            $request->user()
        ]);
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();

        return response()->json([
            'message'=>'Sesion terminada con exito'
        ]);
    }

    public function redirectToProvider($provider){
        if(!config("services.$provider")) abort('404');
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider){
        if(!config("services.$provider")) abort('404');
        
        $userSocialite = Socialite::driver($provider)->user();

        $existingLogin = SocialLogin::where('nick_email', $userSocialite->getEmail())
                                ->orWhere('nick_email', $userSocialite->getNickname())
                                ->first();

        if($existingLogin){
            $user = User::find($existingLogin->user_id);
            return $this->loginAndRedirect($user);
        }else{
            $user = User::create([
                'name' => $userSocialite->getName(),
                'email' => $userSocialite->email ? $userSocialite->email : $userSocialite->nickname,
                'password' => bcrypt(Str::random(10))
            ]);

            SocialLogin::create([
                'user_id'=>$user->id,
                'provider'=>$provider,
                'nick_email' => $userSocialite->email ? $userSocialite->email : $userSocialite->nickname,
                'social_id' => $userSocialite->id
            ]);

            return $this->loginAndRedirect($user);
        }
    }

    public function loginAndRedirect($user){
        Auth::login($user);
        return redirect()->to('user');
    }
}
