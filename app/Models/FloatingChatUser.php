<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloatingChatUser extends Model
{
    protected $fillable = [
        'email',
        'is_end',
        'company_id',
    ];

    public function unread()
    {
        return FloatingChatMessage::where('from', '=', $this->id)->where('is_read', '=', 0)->count();
    }
}
