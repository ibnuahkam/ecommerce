<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCodeMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // ✅ validasi
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        // ✅ generate kode OTP
        $code = rand(100000, 999999);

        // ✅ create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => $code,
            'email_verification_expires_at' => now()->addMinutes(10)
        ]);

        // ✅ kirim email
        Mail::to($user->email)->send(
            new SendVerificationCodeMail($code)
        );

        // ✅ response API
        return response()->json([
            'success' => true,
            'message' => 'Register berhasil. Kode verifikasi dikirim ke email.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email
            ]
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ],404);
        }

        if ($user->email_verification_code != $request->code) {
            return response()->json([
                'message' => 'Kode salah'
            ],400);
        }

        if (now()->gt($user->email_verification_expires_at)) {
            return response()->json([
                'message' => 'Kode expired'
            ],400);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi'
        ]);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // ✅ sudah verified?
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email sudah diverifikasi'
            ], 400);
        }

        // ✅ cooldown 60 detik (anti spam)
        if ($user->email_verification_expires_at &&
            now()->diffInSeconds($user->updated_at) < 60) {
            return response()->json([
                'message' => 'Tunggu 60 detik sebelum minta kode baru'
            ], 429);
        }

        // ✅ generate kode baru
        $code = rand(100000, 999999);

        $user->email_verification_code = $code;
        $user->email_verification_expires_at = now()->addMinutes(10);
        $user->save();

        // ✅ kirim email
        Mail::to($user->email)->send(
            new SendVerificationCodeMail($code)
        );

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi baru telah dikirim'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // ❌ user tidak ada
        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // ❌ password salah
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // ❌ BELUM VERIFY EMAIL
        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email belum diverifikasi'
            ], 403);
        }

        // ✅ buat token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);

    }

    public function update(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20'
        ]);

        $profile = Profile::firstOrCreate([
            'user_id' => $request->user_id
        ]);

        $profile->update($request->only([
            'address',
            'city',
            'province',
            'postal_code',
            'country',
            'phone',
            'birth_date',
            'gender'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => $profile->fresh()
        ]);
    }

}
