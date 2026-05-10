<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TrackerController extends Controller
{
    private static array $platformUrls = [
        'valorant'   => 'https://public-api.tracker.gg/v2/valorant/standard/profile/riot/',
        'riotid'     => 'https://public-api.tracker.gg/v2/valorant/standard/profile/riot/',
        'apex'       => 'https://public-api.tracker.gg/v2/apex/standard/profile/origin/',
        'cs2'        => 'https://public-api.tracker.gg/v2/csgo/standard/profile/steam/',
        'overwatch2' => 'https://public-api.tracker.gg/v2/overwatch-2/standard/profile/battlenet/',
        'lol'        => 'https://public-api.tracker.gg/v2/lol/standard/profile/riot/',
    ];

    public function rank(Request $request)
    {
        $platform = $request->query('platform');
        $username = $request->query('username');

        if (!$platform || !$username) {
            return response()->json(['error' => 'platform and username are required'], 422);
        }

        $key = config('services.tracker_gg.key');
        if (!$key) {
            return response()->json(['error' => 'TRACKER_GG_KEY not configured on server'], 503);
        }

        $base = self::$platformUrls[$platform] ?? null;
        if (!$base) {
            return response()->json(['error' => 'Rank lookup not supported for this platform'], 422);
        }

        $url = $base . rawurlencode($username);

        $response = Http::withHeaders([
            'TRN-Api-Key' => $key,
            'Accept'      => 'application/json',
        ])->timeout(8)->get($url);

        if ($response->status() === 404) {
            return response()->json(['error' => 'Player not found on Tracker.gg'], 404);
        }
        if (!$response->successful()) {
            return response()->json(['error' => 'Tracker.gg returned an error: ' . $response->status()], 502);
        }

        $data     = $response->json();
        $segments = $data['data']['segments'] ?? [];

        $rank = null;
        foreach ($segments as $seg) {
            $stats = $seg['stats'] ?? [];
            foreach (['rank', 'competitiveTier', 'rankScore'] as $key) {
                if (isset($stats[$key]['metadata']['tierName'])) {
                    $rank = $stats[$key]['metadata']['tierName'];
                    break 2;
                }
            }
        }

        return response()->json(['rank' => $rank]);
    }
}
