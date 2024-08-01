<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'from',
        'slug',
        'created_by',
    ];

    private static $emailtempalte = null;

    public function template()
    {

        return $this->hasOne('App\Models\UserEmailTemplate', 'template_id', 'id')->where('user_id', '=', \Auth::user()->id);
    }

    public static function  getemailtemplate()
    {
        if(self::$emailtempalte === null){
            self::$emailtempalte = EmailTemplate::all();
        }
        return self::$emailtempalte;
    }
}
