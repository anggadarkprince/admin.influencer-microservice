<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateInfoRequest;
use App\Http\Requests\UpdatePasswordRequest;
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
            $scope = $request->input('scope');

            if ($user->isInfluencer() && $scope != 'influencer') {
                return response([
                    'error' => 'Access denied: cannot access admin scope with influencer user'
                ], Response::HTTP_FORBIDDEN);
            }

            $token = $user->createToken($scope, [$scope])->accessToken;
            $cookie = $this->getCookie($token);

            if ($user->isAdmin()) {
                $user->permissions = $user->permissions();
            } else {
                $user->setHidden(['role_id', 'email_verified_at', 'password', 'remember_token']);
            }

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
            60 * 24,
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
        //$cookie = cookie(env('AUTH_COOKIE_NAME'), null, 0);

        $cookie = \Illuminate\Support\Facades\Cookie::forget(env('AUTH_COOKIE_NAME'));

        return response(null, Response::HTTP_NO_CONTENT)->withCookie($cookie);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create(
            $request->only('first_name', 'last_name', 'email')
            + [
                'password' => Hash::make($request->input('password')),
                'is_influencer' => 1,
            ]
        );

        return response($user, Response::HTTP_CREATED);
    }

    public function user()
    {
        $user = Auth::user();

        $resource = new UserResource($user);

        if ($user->is_influencer) {
            return $resource;
        }

        return $resource->additional([
            'data' => [
                'permissions' => $user->permissions(),
            ],
        ]);
    }

    public function updateInfo(UpdateInfoRequest $request)
    {
        $user = Auth::user();

        $user->update($request->only('first_name', 'last_name', 'email'));

        return response(new UserResource($user), Response::HTTP_ACCEPTED);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return response(new UserResource($user), Response::HTTP_ACCEPTED);
    }
}
