<?php

namespace App\Models;

use App\Traits\UserTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Lab404\Impersonate\Models\Impersonate;

use App\Models\Priority;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use HasRoles;
    use UserTrait;
    use HasApiTokens;
    use Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'parent',
        'avatar',
        'is_enable_login',
        'type',
        'slug',
        'referral_code',
        'referral_used',
        'commission_amount',
    ];

    protected $appends = [
        'allPermissions',
        'profilelink',
        'avatarlink',
        'isme',
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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

    public function languages()
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

    public function currantLang()
    {
        return $this->can('lang-change') ? $this->lang : $this->parentUser()->lang;
    }

    public function currantLangPath()
    {
        if($this->can('lang-change'))
        {
            $lang = $this->lang;
            $dir  = base_path() . '/resources/lang/' . $lang . "/";
            if(!is_dir($dir) && $this->roles[0]->name != 'Admin')
            {
                $lang = $this->lang;
            }
        }
        else
        {
            $lang = $this->parentUser()->lang;
        }
        $dir = base_path() . '/resources/lang/' . $lang . "/";
        return is_dir($dir) ? $lang : 'en';
    }

    public function getCreatedBy()
    {
        $roles = $this->getRoleNames();
        return $roles == '["Admin"]' ? $this->id : $this->parent;
    }

    public function createId()
    {
        if($this->type == "Super Admin" || $this->type == "Admin")
        {

            return $this->id;
        }
        else
        {
            // dd('bvvbvb');
            return $this->parent;
        }
    }


    public function paymentcreateId()
    {
        if($this->type == "Admin")
        {
            return $this->parent;
        }
        else
        {
            return $this->id;
        }
    }


    public function parentUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'parent')->first();
    }

    public function unread()
    {
        return Message::where('from', '=', $this->id)->where('is_read', '=', 0)->count();
    }

    public static function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);
        if(count($values) > 0)
        {
            foreach($values as $envKey => $envValue)
            {
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if(!$keyPosition || !$endOfLinePosition || !$oldLine)
                {
                    $str .= "{$envKey}='{$envValue}'\n";
                }
                else
                {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1) . "\n";

        return file_put_contents($envFile, $str) ? true : false;
    }

    public static function delete_directory($dir)
    {
        if(!file_exists($dir))
        {
            return true;
        }
        if(!is_dir($dir))
        {
            return unlink($dir);
        }
        foreach(scandir($dir) as $item)
        {
            if($item == '.' || $item == '..')
            {
                continue;
            }
            if(!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item))
            {
                return false;
            }
        }
        return rmdir($dir);
    }

    public static function userDefaultDataRegister($user_id)
    {
        // Make Entry In User_Email_Template
        $allEmail = EmailTemplate::all();
        foreach ($allEmail as $email) {
            UserEmailTemplate::create(
                [
                    'template_id' => $email->id,
                    'user_id' => $user_id,
                    'is_active' => 1,
                ]
            );
        }
    }

    public function assignPlan($planID)
    {
        $plan = Plan::find($planID);
        if($plan)
        {
            $this->plan = $plan->id;
            if($this->trial_expire_date != null);
            {
                $this->trial_expire_date = null;
            }
            if($plan->duration == 'month')
            {
                $this->plan_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            elseif($plan->duration == 'year')
            {
                $this->plan_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            }
            else if($plan->duration == 'Unlimited'){
                $this->plan_expire_date=null;
            }

            $this->save();

            $users    = User::where('parent', '=', \Auth::user())->get();

            if($plan->max_agent == -1)
            {
                foreach($users as $user)
                {
                    $user->is_active = 1;
                    $user->save();
                }
            }
            else
            {
                $userCount = 0;
                foreach($users as $user)
                {
                    $userCount++;
                    if($userCount <= $plan->max_agent)
                    {
                        $user->is_active = 1;
                        $user->save();
                    }
                    else
                    {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }



            return ['is_success' => true];
        }
        else
        {
            return [
                'is_success' => false,
                'error' => 'Plan is deleted.',
            ];
        }
    }
    public static function dateFormat($date)
    {
        $settings = Utility::settings();

        return date($settings['site_date_format'], strtotime($date));
    }

    public function timeFormat($time)
    {
        $settings = Utility::settings();

        return date($settings['site_time_format'], strtotime($time));
    }

    public function countCompany()
    {
        return User::where('type', '=', 'Admin')->where('parent', '=', \Auth::user()->id)->count();
    }

    public function countUsers()
    {
        return User::where('type', '=', 'Agent')->where('parent', '=', \Auth::user()->id)->count();
    }

    public function countPaidCompany()
    {
        return User::where('type', '=', 'Admin')->whereNotIn(
            'plan', [
                      0,
                      1,
                  ]
        )->where('parent', '=', \Auth::user()->id)->count();
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category', 'user_categories', 'user_id', 'category_id');
    }

    public function userDefaultData()
    {
        $id       = $this->id;
        // 'created_by' => $id,
        $priorities = [
            '#252323' => __('Urgent'),
            '#FF0000' => __('High'),
            '#009700' => __('Medium'),
            '#dbc900' => __('Low'),
        ];
        foreach($priorities as $color => $priority)
        {
            $priority = Priority::create([
                    'name' => $priority,
                    'color' => $color,
                    'created_by' => $id,
                ]);

            $policies = new Policies();
            $policies->priority_id = $priority->id;
            $policies->response_time = 'Hour';
            $policies->resolve_time = 'Hour';
            $policies->created_by = $id;
            $policies->save();
        }

    }
}
