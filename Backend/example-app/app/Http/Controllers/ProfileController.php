<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'gamertag'        => 'sometimes|string|max:60|unique:profiles,gamertag,' . optional($request->user()->profile)->id,
            'bio'             => 'nullable|string|max:1000',
            'platform'        => 'nullable|string|max:50',
            'region'          => 'nullable|string|max:50',
            'playstyle'       => 'nullable|string|max:50',
            'games'           => 'nullable|array',
            'games.*'         => 'string|max:60',
            'avatar_color'    => 'nullable|string|max:20',
            'gaming_accounts' => 'nullable|array',
            'gaming_accounts.*.platform' => 'required_with:gaming_accounts|string|max:30',
            'gaming_accounts.*.username' => 'required_with:gaming_accounts|string|max:100',
            'gaming_accounts.*.rank'     => 'nullable|string|max:60',
            'gaming_accounts.*.server'   => 'nullable|string|max:30',
        ]);

        $profile = Profile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json($profile);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:4096',
        ]);

        $user    = $request->user();
        $profile = $user->profile;

        if ($profile && $profile->avatar_path) {
            Storage::disk('public')->delete($profile->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            ['avatar_path' => $path]
        );

        return response()->json(['avatar_url' => $profile->avatar_url]);
    }

    public function show(Request $request, $id)
    {
        $profile = Profile::with('user:id,name')->where('user_id', $id)->firstOrFail();
        return response()->json($profile);
    }
}
