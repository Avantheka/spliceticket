<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloatingChatMessage extends Model
{
    protected $fillable = [
        'from',
        'to',
        'message',
        'is_read',
        'company_id',
    ];

    public function from_user()
    {
        return $this->hasOne('App\Models\FloatingChatUser', 'id', 'from');
    }
}
