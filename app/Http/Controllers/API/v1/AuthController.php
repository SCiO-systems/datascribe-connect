<?php

namespace App\Http\Controllers\API\v1;

use JWTAuth;
use Exception;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\Auth\AuthCheckRequest;

class AuthController extends Controller
{
    /**
     * Authenticate a user.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => 'The provided credentials do not match our records.',
                    'password' => 'The provided credentials do not match our records.',
                ]
            ], 401);
        }

        $user = User::where('email', $request->email)
            ->where('identity_provider', User::IDENTITY_PROVIDER_LOCAL)
            ->first();

        return response()->json([
            'data' => [
                'access_token' => $token,
                'user' => new UserResource($user),
            ]
        ]);
    }

    /**
     * Logout a user.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(LogoutRequest $request)
    {
        try {
            auth('api')->logout();
        } catch (Exception $ex) {
            return response()->json([], 500);
            // TODO: Maybe do something here in the future.
        }

        return response()->json([], 204);
    }

    /**
     * Return the authenticated user.
     *
     * @param AuthCheckRequest $request
     * @return user
     */
    public function check(AuthCheckRequest $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                'errors' => [
                    'error' => $e->getMessage()
                ]
            ], 401);
        }

        return new UserResource($user);
    }
}
