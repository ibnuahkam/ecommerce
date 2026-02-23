<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\Roles;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCodeMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $codeOtp = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => $codeOtp,
            'email_verification_expires_at' => now()->addMinutes(10),
        ]);

        $role = Roles::create([
            'user_id' => $user->id,
            'code' => 'buyer'
        ]);

        $update = User::where('id', $user->id)->update([
            'role_id' => $role->id
        ]);

        Mail::to($user->email)->send(
            new SendVerificationCodeMail($codeOtp)
        );

        return response()->json([
            'success' => true,
            'message' => 'Register berhasil.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $role->code
            ]
        ], 200);
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
        $role = $user->role;

        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email belum diverifikasi'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'role' => [
                'id' => $role->id,
                'code' => $role->code
            ]
        ]);

    }

    public function update(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20'
        ]);

        $user = $request->user(); // ambil user dari token

        $profile = Profile::firstOrCreate([
            'user_id' => $user->id
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
