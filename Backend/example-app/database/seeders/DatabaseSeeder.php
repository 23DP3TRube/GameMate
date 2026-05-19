<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['Alex','alex@demo.com','ShadowStrike','PC','EU','Competitive',['Valorant','CS2','Apex'],'#ef4444'],
            ['Mia','mia@demo.com','PixelMia','PS','NA','Casual',['Fortnite','Minecraft'],'#22d3ee'],
            ['Jonas','jonas@demo.com','TacticJonas','PC','EU','Competitive',['Dota 2','Deadlock'],'#f59e0b'],
            ['Eva','eva@demo.com','EvaQueen','Xbox','NA','Casual',['Halo','Forza'],'#a855f7'],
            ['Leo','leo@demo.com','LeoTheLion','PC','EU','Competitive',['Rust','Tarkov'],'#10b981'],
        ];
        foreach ($samples as [$name,$email,$tag,$plat,$region,$style,$games,$color]) {
            $u = User::updateOrCreate(['email'=>$email], [
                'name'=>$name,'password'=>Hash::make('password'),'api_token'=>Str::random(64),
                'is_admin'=>$email === 'alex@demo.com',
            ]);
            Profile::updateOrCreate(['user_id'=>$u->id], [
                'gamertag'=>$tag,'platform'=>$plat,'region'=>$region,'playstyle'=>$style,
                'games'=>$games,'avatar_color'=>$color,
                'bio'=>"Hey, es esmu $name. Meklēju komandas biedrus!",
            ]);
        }

        $admin = User::where('name', 'duckgun2')
            ->orWhereHas('profile', fn($q) => $q->where('gamertag', 'duckgun2'))
            ->first();
        if ($admin) {
            $admin->is_admin = true;
            $admin->save();
        }
    }
}
