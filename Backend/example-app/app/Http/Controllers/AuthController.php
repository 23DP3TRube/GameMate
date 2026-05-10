<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmail;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'gamertag' => 'required|string|max:60|unique:profiles,gamertag',
        ]);

        $verificationToken = Str::random(64);

        $user = User::create([
            'name'                       => $data['name'],
            'email'                      => $data['email'],
            'password'                   => Hash::make($data['password']),
            'api_token'                  => Str::random(64),
            'email_verification_token'   => $verificationToken,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'gamertag' => $data['gamertag'],
        ]);

        $verifyUrl = config('app.url') . '/api/verify-email/' . $verificationToken;
        try {
            Mail::to($user->email)->send(new VerifyEmail($user->name, $verifyUrl));
        } catch (\Throwable) {
            // Don't block registration if mail fails
        }

        return response()->json([
            'token'          => $user->api_token,
            'user'           => $user->load('profile'),
            'email_verified' => false,
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }
        if (!$user->api_token) {
            $user->api_token = Str::random(64);
            $user->save();
        }
        return response()->json([
            'token' => $user->api_token,
            'user' => $user->load('profile'),
        ]);
    }

    public function verifyEmail(string $token)
    {
        $user = User::where('email_verification_token', $token)->first();
        if (!$user) {
            return response('<h2 style="font-family:sans-serif;color:#f87171">Nederīga vai novecojusi saite.</h2>', 400)
                ->header('Content-Type', 'text/html');
        }
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return response(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>E-pasts apstiprināts</title></head><body style="font-family:sans-serif;background:#0f0f0f;color:#e5e7eb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;">
            <div style="text-align:center;background:#1a1a1a;border:1px solid #2a2a2a;border-radius:16px;padding:48px 40px;">
                <div style="font-size:48px;margin-bottom:16px;">✅</div>
                <h2 style="color:#4ade80;margin:0 0 12px;">E-pasts apstiprināts!</h2>
                <p style="color:#9ca3af;margin:0 0 24px;">Tavs GameMate konts ir aktivizēts.</p>
                <a href="http://localhost:5173/login" style="display:inline-block;background:#7c3aed;color:#fff;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:700;">Pieslēgties</a>
            </div></body></html>',
            200
        )->header('Content-Type', 'text/html');
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();
        if ($user->email_verified_at) {
            return response()->json(['message' => 'E-pasts jau apstiprināts.']);
        }
        $token = Str::random(64);
        $user->email_verification_token = $token;
        $user->save();

        $verifyUrl = config('app.url') . '/api/verify-email/' . $token;
        Mail::to($user->email)->send(new VerifyEmail($user->name, $verifyUrl));

        return response()->json(['message' => 'Verifikācijas e-pasts nosūtīts.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('profile'));
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->api_token = Str::random(64);
        $user->save();
        return response()->json(['ok' => true]);
    }
}
