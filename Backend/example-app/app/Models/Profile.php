<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'gamertag', 'bio', 'platform', 'region',
        'playstyle', 'games', 'avatar_color', 'avatar_path', 'gaming_accounts',
    ];

    protected $casts = [
        'games' => 'array',
        'gaming_accounts' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : null;
    }

    protected $appends = ['avatar_url'];
}
