<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait UserTrait
{
    public function getProfilelinkAttribute()
    {
        return route('users.edit', ['user' => $this->id]);
    }

    public function getAvatarlinkAttribute()
    {
        if($this->avatar != '' && Storage::exists("/public/" . $this->avatar))
        {
            $image_path = "/public/" . $this->avatar;
        } else {
            $image_path = "avatar.png";
        }

        $image_id = (substr($this->id, -1, 1)) % 5;
        $image_id = $image_id == 0 ? 5 : $image_id;

        return asset(Storage::url($image_path));
    }

    public function getIsmeAttribute()
    {
        return Auth::check() && (Auth::id() == $this->id);
    }
}
