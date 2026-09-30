<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLogin extends Model
{
    protected $fillable = ['user_id', 'provider', 'nick_email', 'social_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
