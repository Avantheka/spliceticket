<?php

namespace App\Models\Api;

use Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'name', 'value', 'created_by'
    ];

    public function settings($key)
    {
        static $settings;

        if(is_null($settings))
        {
            $settings = Cache::remember(
                'settings', 24 * 60, function (){
                return Settings::all()->pluck('value', 'key');
            }
            );
        }

        return (is_array($key)) ? array_only($settings, $key) : $settings[$key];
    }

    public static function colorset()
    {

        if(\Auth::user())
        {
            $user = \Auth::user()->getCreatedBy();
            $setting = DB::table('settings')->where('created_by',$user)->pluck('value','name')->toArray();
        }
        else{
            $setting = DB::table('settings')->pluck('value','name')->toArray();
        }
        return $setting;

        $is_dark_mode = $setting['cust_darklayout'];
        if($is_dark_mode == 'on'){
            return 'logo-light.png';
        }else{
            return 'logo-dark.png';
        }

    }

    public static function getSettings($data)
    {

        $data = Settings::where('created_by',$data['id'])->get();

        $settings = [
            "favicon" => "off",
            "logo" => "off",
            "white_logo" => "",
            "app_name" => "",
            "default_language" => "",
            "site_rtl" => "",
            "gdpr_cookie" => "logo-dark.png",
            "cookie_text" => "logo-light.png",
            "footer_text" => "theme-3",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }
}
