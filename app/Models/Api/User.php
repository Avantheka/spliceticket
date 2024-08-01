<?php

namespace App\Models\Api;

use App\Traits\UserTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use UserTrait;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parent',
        'avatar',
    ];

    protected $appends = [
        'avatarlink',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function getAllpermissionsAttribute()
    {
        $res            = [];
        $allPermissions = $this->getAllPermissions();
        foreach($allPermissions as $p)
        {
            $res[] = $p->name;
        }

        return $res;
    }



    public static function getUser($data)
    {

        $user_query = User::query()->select('id','name','email')->where('parent',\Auth::user()->id);

        if($data['search']){
            $search = $data['search'];

            $user_query->where('name', 'like', "%$search%")->orwhere('email', 'like', "%$search%");
        }

        $users = $user_query->paginate(10);

        return $users;
    }



    public function getCreatedBy()
    {
        $roles = $this->getRoleNames();
        return $roles == '["Admin"]' ? $this->id : $this->parent;
    }

    public function parentUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'parent')->first();
    }

    public function unread()
    {
        return Message::where('from', '=', $this->id)->where('is_read', '=', 0)->count();
    }


      public static function languages()
    {

        $dir     = base_path() . '/resources/lang/';
        $glob    = glob($dir . "*", GLOB_ONLYDIR);
        $arrLang = array_map(
            function ($value) use ($dir){
                return str_replace($dir, '', $value);
            }, $glob
        );
        $arrLang = array_map(
            function ($value) use ($dir){
                return preg_replace('/[0-9]+/', '', $value);
            }, $arrLang
        );
        $arrLang = array_filter($arrLang);

        return $arrLang;
    }

}
