<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $players = [
            [
                'name' => 'Alex Johnson', 'email' => 'alex@demo.com', 'gamertag' => 'AlexShot',
                'bio' => 'Competitive FPS player. Diamond in Valorant, looking for serious teammates for ranked grind.',
                'platform' => 'PC', 'region' => 'EU', 'playstyle' => 'Competitive',
                'games' => ['Valorant', 'Counter-Strike 2', 'Apex Legends'],
                'avatar_color' => '#7c3aed',
                'gaming_accounts' => [
                    ['platform' => 'valorant', 'username' => 'AlexShot#EU1', 'rank' => 'Diamond', 'server' => ''],
                    ['platform' => 'steam', 'username' => 'alexshot_gaming', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Maya Torres', 'email' => 'maya@demo.com', 'gamertag' => 'MayaFrag',
                'bio' => 'Hardcore gamer, mostly RPG and FPS. Love trying new games. Always up for a party!',
                'platform' => 'PC', 'region' => 'NA', 'playstyle' => 'Hardcore',
                'games' => ['Elden Ring', 'Destiny 2', 'Apex Legends', 'Diablo IV'],
                'avatar_color' => '#dc2626',
                'gaming_accounts' => [
                    ['platform' => 'epic', 'username' => 'MayaFrag99', 'rank' => '', 'server' => ''],
                    ['platform' => 'battlenet', 'username' => 'MayaFrag#1234', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Carlos Reyes', 'email' => 'carlos@demo.com', 'gamertag' => 'CarlosKing',
                'bio' => 'Gold in LoL, trying to climb. Chill player, don\'t flame. Mid/Support main.',
                'platform' => 'PC', 'region' => 'EU', 'playstyle' => 'Casual',
                'games' => ['League of Legends', 'Teamfight Tactics', 'Hearthstone'],
                'avatar_color' => '#059669',
                'gaming_accounts' => [
                    ['platform' => 'lol', 'username' => 'CarlosKing', 'rank' => 'Gold', 'server' => 'EUW'],
                    ['platform' => 'riotid', 'username' => 'CarlosKing#EUW', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Sophie Zhang', 'email' => 'sophie@demo.com', 'gamertag' => 'SophieGG',
                'bio' => 'JRPG and survival game fan. Recently got into competitive games. Friendly vibes only!',
                'platform' => 'PS5', 'region' => 'Asia', 'playstyle' => 'Casual',
                'games' => ['Final Fantasy XIV', 'Genshin Impact', 'Palworld', 'Minecraft'],
                'avatar_color' => '#db2777',
                'gaming_accounts' => [
                    ['platform' => 'psn', 'username' => 'SophieGG_PS', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'James Walker', 'email' => 'james@demo.com', 'gamertag' => 'JWalkerFPS',
                'bio' => 'Global Elite in CS2. Semi-pro player. Looking for 5-stack for faceit.',
                'platform' => 'PC', 'region' => 'EU', 'playstyle' => 'Competitive',
                'games' => ['Counter-Strike 2', 'Valorant', 'Rainbow Six Siege'],
                'avatar_color' => '#2563eb',
                'gaming_accounts' => [
                    ['platform' => 'cs2', 'username' => 'JWalkerFPS', 'rank' => 'Global Elite', 'server' => 'EU'],
                    ['platform' => 'steam', 'username' => 'jwalker_fps', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Priya Sharma', 'email' => 'priya@demo.com', 'gamertag' => 'PriyaPlays',
                'bio' => 'Casual player who loves cozy and survival games. Will also run dungeons in MMOs!',
                'platform' => 'PC', 'region' => 'Asia', 'playstyle' => 'Casual',
                'games' => ['Stardew Valley', 'Valheim', 'World of Warcraft', 'Terraria'],
                'avatar_color' => '#d97706',
                'gaming_accounts' => [
                    ['platform' => 'battlenet', 'username' => 'PriyaPlays#2345', 'rank' => '', 'server' => ''],
                    ['platform' => 'steam', 'username' => 'priya_plays', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Luca Bianchi', 'email' => 'luca@demo.com', 'gamertag' => 'LucaSniper',
                'bio' => 'Apex Predator player. Main Wraith. Looking for consistent ranked partners in EU.',
                'platform' => 'PC', 'region' => 'EU', 'playstyle' => 'Competitive',
                'games' => ['Apex Legends', 'Fortnite', 'Call of Duty: Warzone'],
                'avatar_color' => '#7c3aed',
                'gaming_accounts' => [
                    ['platform' => 'apex', 'username' => 'LucaSniper_EU', 'rank' => 'Predator', 'server' => ''],
                    ['platform' => 'epic', 'username' => 'LucaSniper', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Emma Davis', 'email' => 'emma@demo.com', 'gamertag' => 'EmmaGames',
                'bio' => 'Xbox player, love all genres. Looking for friends to play Sea of Thieves and Halo!',
                'platform' => 'Xbox Series X/S', 'region' => 'NA', 'playstyle' => 'Casual',
                'games' => ['Sea of Thieves', 'Halo Infinite', 'Minecraft', 'Fall Guys'],
                'avatar_color' => '#16a34a',
                'gaming_accounts' => [
                    ['platform' => 'xbox', 'username' => 'EmmaGames2024', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Noah Kim', 'email' => 'noah@demo.com', 'gamertag' => 'NoahKRacer',
                'bio' => 'Rocket League Grand Champ. Also play competitive Overwatch. DM for ranked sessions.',
                'platform' => 'PC', 'region' => 'NA', 'playstyle' => 'Competitive',
                'games' => ['Rocket League', 'Overwatch 2', 'Fortnite'],
                'avatar_color' => '#0891b2',
                'gaming_accounts' => [
                    ['platform' => 'overwatch2', 'username' => 'NoahKR#1234', 'rank' => 'Master', 'server' => ''],
                    ['platform' => 'epic', 'username' => 'NoahKRacer', 'rank' => '', 'server' => ''],
                ],
            ],
            [
                'name' => 'Aisha Patel', 'email' => 'aisha@demo.com', 'gamertag' => 'AishaMidlane',
                'bio' => 'Diamond LoL mid laner. Also play Valorant. EU West. Let\'s climb together!',
                'platform' => 'PC', 'region' => 'EU', 'playstyle' => 'Semi-competitive',
                'games' => ['League of Legends', 'Valorant', 'Teamfight Tactics'],
                'avatar_color' => '#be185d',
                'gaming_accounts' => [
                    ['platform' => 'lol', 'username' => 'AishaMidlane', 'rank' => 'Diamond', 'server' => 'EUW'],
                    ['platform' => 'valorant', 'username' => 'Aisha#EUW', 'rank' => 'Platinum', 'server' => ''],
                ],
            ],
        ];

        foreach ($players as $p) {
            $existing = User::where('email', $p['email'])->first();
            if ($existing) continue;

            $user = User::create([
                'name'      => $p['name'],
                'email'     => $p['email'],
                'password'  => Hash::make('password'),
                'api_token' => Str::random(64),
            ]);

            Profile::create([
                'user_id'         => $user->id,
                'gamertag'        => $p['gamertag'],
                'bio'             => $p['bio'],
                'platform'        => $p['platform'],
                'region'          => $p['region'],
                'playstyle'       => $p['playstyle'],
                'games'           => $p['games'],
                'avatar_color'    => $p['avatar_color'],
                'gaming_accounts' => $p['gaming_accounts'],
            ]);
        }
    }
}
