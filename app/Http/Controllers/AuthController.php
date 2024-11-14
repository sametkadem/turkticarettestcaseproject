<?php
namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

// GuzzleHttp\Psr7\Request yerine Illuminate\Http\Request kullanılmalı
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $validatedData = $request->validated();
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password'])
        ]);
        $token = auth('api')->login($user);
        return $this->respondWithToken($token, ['user']);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        if (!$user)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Kullanıcı bulunamadı.'
            ], 401);
        }
        if ($user->is_active == 0)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Kullanıcı aktif değil.'
            ], 401);
        }
        $roles = $user->is_admin ? ['admin'] : ['user'];
        $guard = $user->is_admin ? 'admin' : 'api';
        // JWT ile oturum açma
        if (!$token = auth($guard)->attempt($credentials))
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Lütfen giriş bilgilerinizi kontrol edin.'
            ], 401);
        }
        $user = auth($guard)->user();
        return $this->respondWithToken($token, $roles, $guard);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['status' => 'success', 'message' => 'Çıkış işlemi başarılı']);
    }

    public function refresh()
    {
        $user = auth('api')->user();
        return $this->respondWithToken(auth('api')->refresh(), $user->is_admin ? ['admin'] : ['user']);
    }

    protected function respondWithToken($token, $roles, $guard = 'api')
    {
        return response()->json([
            'user' => auth($guard)->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'roles' => $roles,
        ]);
    }
}
