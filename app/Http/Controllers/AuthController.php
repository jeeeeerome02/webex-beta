<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    private const COOKIE_NAME = 'access_token';

    public function user(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        return response()->json([
            'user' => $user ? $this->serializeUser($user) : null,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'confirmPassword' => ['required', 'same:password'],
            'role' => ['required', Rule::in(['teacher', 'student'])],
            'course' => ['required', 'string', 'max:255'],
            'addressLine' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'cityMunicipality' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'postalCode' => ['required', 'digits:4'],
            'agreeToTerms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'course' => $validated['course'],
            'address_line' => $validated['addressLine'],
            'barangay' => $validated['barangay'],
            'city_municipality' => $validated['cityMunicipality'],
            'province' => $validated['province'],
            'region' => $validated['region'],
            'postal_code' => $validated['postalCode'],
        ]);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Account created successfully.',
            'user' => $this->serializeUser($user),
        ], 201)->withCookie($this->tokenCookie($token));
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $token = Auth::guard('api')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if (! $token) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return response()->json([
            'message' => 'Signed in successfully.',
            'user' => $this->serializeUser(Auth::guard('api')->user()),
        ])->withCookie($this->tokenCookie($token));
    }

    public function logout(Request $request): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            Auth::guard('api')->logout();
        }

        return response()->json([
            'message' => 'Signed out successfully.',
            'user' => null,
        ])->withCookie($this->forgetCookie());
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'course' => $user->course,
            'avatar_url' => $user->avatar(),
        ];
    }

    private function tokenCookie(string $token): Cookie
    {
        $minutes = (int) config('jwt.ttl', 60);

        return cookie(
            name: self::COOKIE_NAME,
            value: $token,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: ! app()->environment('local'),
            httpOnly: true,
            sameSite: 'lax',
        );
    }

    private function forgetCookie(): Cookie
    {
        return cookie(
            name: self::COOKIE_NAME,
            value: '',
            minutes: -1,
            path: '/',
            domain: null,
            secure: ! app()->environment('local'),
            httpOnly: true,
            sameSite: 'lax',
        );
    }
}
