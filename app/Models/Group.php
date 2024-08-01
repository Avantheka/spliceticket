<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'operating_hours',
        'users',
        'leader',
        'description',
        'created_by',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'users');
    }




    public static function getUser($user)
    {
        if($user)
        {
            return User::whereIn('id', explode(',', $user))->get();
        }

        return false;
    }

    public function agent()
    {
        return $this->belongsToMany('App\Models\User', 'users', 'id', 'users');
    }

    public function operating()
    {
        return $this->hasOne('App\Models\OperatingHours', 'id', 'operating_hours');
    }


    
}
