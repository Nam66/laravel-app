<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use App\Models\User;
use App\Models\UserRefreshToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::guard('api')->user();
        $refreshToken = (string) Str::random(64);

        UserRefreshToken::where('user_id', $user->id)->update(['revoked_at' => now()]);
        UserRefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7),
        ]);

        $response = response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl', 3600),
            'user' => $user,
        ]);

        return $response->withCookie(cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7,
            '/',
            null,
            false,
            true,
            false,
            'lax'
        ));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new UserRegistered($user));

        $refreshToken = (string) Str::random(64);
        $token = Auth::guard('api')->login($user);

        UserRefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7),
        ]);

        $response = response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl', 3600),
            'user' => $user,
        ], 201);

        return $response->withCookie(cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7,
            '/',
            null,
            false,
            true,
            false,
            'lax'
        ));
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        $accessToken = $request->bearerToken();

        if (! $refreshToken) {
            return response()->json(['message' => 'Refresh token missing'], 401);
        }

        if (! $accessToken) {
            return response()->json(['message' => 'Access token missing'], 401);
        }

        $record = UserRefreshToken::where('expires_at', '>', now())
            ->whereNull('revoked_at')
            ->get()
            ->first(function ($item) use ($refreshToken) {
                return hash_equals($item->token_hash, hash('sha256', $refreshToken));
            });

        if (! $record) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        try {
            $newAccessToken = Auth::guard('api')->setToken($accessToken)->refresh();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid or expired access token'], 401);
        }

        $newRefreshToken = (string) Str::random(64);

        $record->update([
            'token_hash' => hash('sha256', $newRefreshToken),
            'expires_at' => now()->addDays(7),
            'revoked_at' => null,
        ]);

        return response()->json([
            'message' => 'Token refreshed',
            'access_token' => $newAccessToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl', 3600),
        ])->withCookie(cookie(
            'refresh_token',
            $newRefreshToken,
            60 * 24 * 7,
            '/',
            null,
            false,
            true,
            false,
            'lax'
        ));
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if ($refreshToken) {
            UserRefreshToken::where('token_hash', hash('sha256', $refreshToken))
                ->update(['revoked_at' => now()]);
        }

        Auth::guard('api')->logout();

        return response()->json(['message' => 'Logged out successfully'])->withCookie(
            cookie()->forget('refresh_token')
        );
    }
}
