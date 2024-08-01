<?php

namespace App\Models\Api;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Storage;
use Date;

class Utility extends Model
{

    public static function status()
    {

        $status[] = ['name'=>'In Progress'];
        $status[] = ['name'=>'On Hold'];
        $status[] = ['name'=>'Closed'];

        return $status;
    }


    public static function getSettingValByName($key)
    {
        $setting = self::settings();

        if(!isset($setting[$key]) || empty($setting[$key]))
        {
            $setting[$key] = '';
        }

        return $setting[$key];
    }
    public static function settings()
    {
        $user = \Auth::user();


        $data = DB::table('settings')->get();

        $settings = [
            "Knowlwdge_Base" => "off",
            "FAQ" => "off",
            "FOOTER_TEXT" => "",
            "SITE_RTL" => "",
            "gdpr_cookie" => "",
            "cookie_text" => "",
            "dark_logo" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "color" => "theme-3",
            'DEFAULT_LANG' => 'en',
            'CHAT_MODULE' => "",
            'FOOTER_TEXT' => "",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function addNewData()
    {
        \Artisan::call('cache:forget spatie.permission.cache');
        \Artisan::call('cache:clear');

        $usr            = \Auth::user();
        $arrPermissions = [

            "manage-knowledge",
            "create-knowledge",
            "edit-knowledge",
            "delete-knowledge",
            "manage-knowledgecategory",
            "create-knowledgecategory",
            "edit-knowledgecategory",
            "delete-knowledgecategory",

        ];
        foreach ($arrPermissions as $ap) {
            // check if permission is not created then create it.
            $permission = Permission::where('name', 'LIKE', $ap)->first();
            if (empty($permission)) {
                Permission::create(['name' => $ap]);
            }
        }
        $adminRole          = Role::where('name', 'LIKE', 'Admin')->first();
        $adminPermissions   = $adminRole->getPermissionNames()->toArray();
        $adminNewPermission = [
            "manage-knowledge",
            "create-knowledge",
            "edit-knowledge",
            "delete-knowledge",
            "manage-knowledgecategory",
            "create-knowledgecategory",
            "edit-knowledgecategory",
            "delete-knowledgecategory",
        ];
        foreach ($adminNewPermission as $op) {
            // check if permission is not assign to owner then assign.
            if (!in_array($op, $adminPermissions)) {
                $permission = Permission::findByName($op);
                $adminRole->givePermissionTo($permission);
            }
        }
        $agentRole          = Role::where('name', 'LIKE', 'Agent')->first();
        $agentPermissions   = $agentRole->getPermissionNames()->toArray();
        $agentNewPermission = [

        ];
        foreach ($agentNewPermission as $op) {
            // check if permission is not assign to owner then assign.
            if (!in_array($op, $agentPermissions)) {
                $permission = Permission::findByName($op);
                $agentRole->givePermissionTo($permission);
            }
        }
    }

    public static function get_superadmin_logo(){
        $is_dark_mode = self::getSettingValByName('cust_darklayout');
        if($is_dark_mode == 'on'){
            return 'logo-light.png';
        }else{
            return 'logo-dark.png';
        }
    }


}
