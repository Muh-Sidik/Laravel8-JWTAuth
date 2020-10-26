<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number_phone' => 'required|numeric|min:11',
            'password'     => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        try {
            if (! $token = JWTAuth::attempt($validator->validated())) {

                return response()->json(['error' => "Unauthorized"], 401);

            }
        } catch (JWTException $e) {

            return response()->json(['error' => 'Could Not Create Token!'], 500);

        }

        return $this->responseWithToken($token);

    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|between:2,100',
            'number_phone' => 'required|numeric|min:11|unique:users',
            'password'     => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $user = User::create(array_merge($validator->validated(), [
            'password' => bcrypt($request->password)
        ]));

        if ($user) {
            return response()->json([
                'message' => 'User Successfully Registered',
                'data'    => $user,
            ]);
        } else {
            return response()->json([
                'message' => 'User Failed Registered',
                'data'    => '',
            ]);
        }
    }

    public function bioProfile()
    {
        return response()->json(['profile' =>auth()->user()]);
    }

    public function refresh()
    {
        return $this->responseWithToken(JWTAuth::refresh());
    }

    public function logout()
    {
        JWTAuth::logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(['user' => $user]);
    }
}
