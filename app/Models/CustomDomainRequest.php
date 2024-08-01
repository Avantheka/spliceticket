<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomDomainRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'custom_domain',
        'status',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public static $statues = [
        'Pending',
        'Approved',
        'Rejected'
    ];
}
