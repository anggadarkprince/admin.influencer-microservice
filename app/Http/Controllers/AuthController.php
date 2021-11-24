<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = Auth::user();

            $token = $user->createToken('admin')->accessToken;
            $cookie = $this->getCookie($token);

            $user->permissions = $user->permissions();
            return response()->json([
                'token' => $token,
                'user' => $user,
            ])->withCookie($cookie);
        }

        return response([
            'error' => 'Invalid credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Set cookie details and return cookie
     *
     * @param string $token JWT
     *
     * @return CookieJar|Cookie
     */
    private function getCookie(string $token)
    {
        return cookie(
            env('AUTH_COOKIE_NAME'),
            $token,
            60,
            null,
            null,
            env('APP_DEBUG') ? false : true,
            true,
            false,
            'Strict'
        );
    }

    public function logout()
    {
        $cookie = cookie(env('AUTH_COOKIE_NAME'), null, 0);

        return response(null, Response::HTTP_NO_CONTENT)->withCookie($cookie);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create(
            $request->only('first_name', 'last_name', 'email')
            + [
                'password' => Hash::make($request->input('password')),
                'role_id' => 1,
            ]
        );

        $token = $user->createToken('admin')->accessToken;

        return response(['user' => $user, 'token' => $token], Response::HTTP_CREATED);
    }
}
