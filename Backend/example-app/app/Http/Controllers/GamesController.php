<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GamesController extends Controller
{
    private static array $games = [
        'Valorant', 'Counter-Strike 2', 'League of Legends', 'Dota 2',
        'Fortnite', 'Apex Legends', 'Call of Duty: Warzone', 'Overwatch 2',
        'Minecraft', 'Rocket League', 'Rainbow Six Siege', 'PUBG',
        'World of Warcraft', 'Final Fantasy XIV', 'Destiny 2', 'Elden Ring',
        'Grand Theft Auto V', 'Diablo IV', 'Hearthstone', 'Teamfight Tactics',
        'Genshin Impact', 'Battlefield 2042', 'Halo Infinite', 'Sea of Thieves',
        'Phasmophobia', 'Among Us', 'Fall Guys', 'Dead by Daylight', 'Lost Ark',
        'Path of Exile', 'Rust', 'Escape from Tarkov', 'Hunt: Showdown',
        'Valheim', 'Terraria', 'Deep Rock Galactic', 'Helldivers 2',
        'Palworld', 'The Finals', 'XDefiant', 'Marvel Rivals',
        'EA FC 25', 'NBA 2K25', 'Cyberpunk 2077', 'Red Dead Online',
        'New World: Aeternum', 'Once Human', 'Throne and Liberty', 'Dark and Darker',
        'Star Wars: The Old Republic', 'Guild Wars 2', 'Black Desert Online',
    ];

    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $list  = $query
            ? array_values(array_filter(self::$games, fn($g) => stripos($g, $query) !== false))
            : self::$games;

        return response()->json(array_slice($list, 0, 30));
    }
}
