<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchRecord extends Model
{
    protected $table = 'matches';
    protected $fillable = ['user_a_id','user_b_id'];

    public function messages(): HasMany { return $this->hasMany(Message::class, 'match_id'); }
}
