<?php

namespace App\Http\Controllers;

use App\Models\MatchRecord;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Swipe;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function discover(Request $request)
    {
        $userId   = $request->user()->id;
        $seen     = Swipe::where('user_id', $userId)->pluck('target_id');

        $query = Profile::with('user:id,name')
            ->where('user_id', '!=', $userId)
            ->whereNotIn('user_id', $seen);

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('playstyle')) {
            $query->where('playstyle', $request->playstyle);
        }
        if ($request->filled('game')) {
            $query->where('games', 'like', '%' . $request->game . '%');
        }

        $sortBy  = in_array($request->sort_by, ['gamertag','platform','region']) ? $request->sort_by : 'gamertag';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';
        $profiles = $query->orderBy($sortBy, $sortDir)->limit(50)->get();
        return response()->json($profiles);
    }

    public function swipe(Request $request)
    {
        $data   = $request->validate([
            'target_id' => 'required|integer|exists:users,id',
            'liked'     => 'required|boolean',
        ]);
        $userId = $request->user()->id;

        if ($data['target_id'] == $userId) {
            return response()->json(['message' => 'Cannot swipe yourself'], 422);
        }

        Swipe::updateOrCreate(
            ['user_id' => $userId, 'target_id' => $data['target_id']],
            ['liked' => $data['liked']]
        );

        $matched = false;
        $matchId = null;

        if ($data['liked']) {
            $reciprocal = Swipe::where('user_id', $data['target_id'])
                ->where('target_id', $userId)
                ->where('liked', true)
                ->exists();

            if ($reciprocal) {
                $a     = min($userId, $data['target_id']);
                $b     = max($userId, $data['target_id']);
                $match = MatchRecord::firstOrCreate(['user_a_id' => $a, 'user_b_id' => $b]);
                $matched = true;
                $matchId = $match->id;
            }
        }

        return response()->json(['matched' => $matched, 'match_id' => $matchId]);
    }

    public function matches(Request $request)
    {
        $userId  = $request->user()->id;
        $matches = MatchRecord::where('user_a_id', $userId)
            ->orWhere('user_b_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($m) use ($userId) {
                $otherId = $m->user_a_id === $userId ? $m->user_b_id : $m->user_a_id;
                $profile = Profile::with('user:id,name')->where('user_id', $otherId)->first();
                return [
                    'match_id'   => $m->id,
                    'created_at' => $m->created_at,
                    'profile'    => $profile,
                ];
            });

        return response()->json($matches);
    }

    public function messages(Request $request, $id)
    {
        $userId = $request->user()->id;
        $match  = MatchRecord::findOrFail($id);
        if ($match->user_a_id !== $userId && $match->user_b_id !== $userId) {
            abort(403);
        }
        return response()->json($match->messages()->orderBy('created_at')->get());
    }

    public function sendMessage(Request $request, $id)
    {
        $userId = $request->user()->id;
        $match  = MatchRecord::findOrFail($id);
        if ($match->user_a_id !== $userId && $match->user_b_id !== $userId) {
            abort(403);
        }
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $msg  = Message::create([
            'match_id'  => $match->id,
            'sender_id' => $userId,
            'body'      => $data['body'],
        ]);
        return response()->json($msg);
    }
}
