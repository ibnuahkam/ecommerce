<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Roles;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $profile = Profile::firstOrCreate([
            'user_id' => $user->id
        ]);

        // hapus lama
        if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $profile->avatar = $path;
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated',
            'avatar_url' => asset('storage/'.$path)
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $profile = Profile::firstOrCreate([
            'user_id' => $user->id
        ]);

        $avatarUrl = null;

        if ($profile->avatar) {
            $avatarUrl = asset('storage/' . $profile->avatar);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'profile' => [
                    'address' => $profile->address,
                    'city' => $profile->city,
                    'province' => $profile->province,
                    'postal_code' => $profile->postal_code,
                    'country' => $profile->country,
                    'phone' => $profile->phone,
                    'gender' => $profile->gender,
                    'birth_date' => $profile->birth_date,
                    'avatar_path' => $profile->avatar,
                    'avatar_url' => $avatarUrl
                ]
            ]
        ]);
    }
}
