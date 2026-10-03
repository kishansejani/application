<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['identifier', 'channel', 'purpose', 'code_hash', 'attempts', 'expires_at'];

    protected $hidden = ['code_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
