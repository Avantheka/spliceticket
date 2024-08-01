<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Category extends Model
{
    protected $fillable = [
            'name', 'color','created_by'
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_categories', 'category_id','user_id');
    }
}

