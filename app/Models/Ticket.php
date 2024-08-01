<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Conversion;
use Carbon\Carbon;
use DateTime;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_id',
        'name',
        'email',
        'category',
        'priority',
        'subject',
        'status',
        'description',
        'created_by',
        'attachments',
        'note',
    ];

    public static function Managepriority($priority)
    {
        $priorityArr  = explode(',', $priority);
        $unitRate = 0;

        foreach ($priorityArr as $username) {

            $priority     = Priority::find($username);

            $unitRate     = $priority->name ?? '';
        }
        return $unitRate;
    }

    protected $appends = ['responsetime', 'responseTimeconvertinhours'];

    public function getResponsetimeAttribute()
    {
        $ticketCreateTime = strtotime($this->created_at);
        $datetime_1 = date('Y-m-d H:i:s', $ticketCreateTime);
        $coTime = Conversion::where('ticket_id', $this->id)->latest()->first();
        if (empty($coTime)) {
            return "-";
        }
        $coTime = $coTime->created_at;
        $ConversionTime = strtotime($coTime);
        $datetime_2 = date('Y-m-d H:i:s', $ConversionTime);
        $diff = Carbon::createFromFormat('Y-m-d H:i:s', $datetime_2)->diffForHumans($datetime_1);
        return $diff;
    }
    private static $conversionHour = null;

    public function getResponseTimeConvertInHoursAttribute()
    {
        if (self::$conversionHour === null) {
            self::$conversionHour = self::fetchgetResponseTimeConvertInHoursAttribute();
        }
        return self::$conversionHour;
    }
    
    public function fetchgetResponseTimeConvertInHoursAttribute()
    {
        $maxTotalResTime = !empty($this->priorities->policies->response_within) ? $this->priorities->policies->response_within : 0;
        $coTime = Conversion::where('ticket_id', $this->id)->latest()->first();
        if (!$coTime) {
            return 'off';
        }
        $coTime = $coTime->created_at;
        $maxTime = null;
        if ($this->priorities->policies->response_time == 'Minute') {
            $maxTime = Carbon::parse($this->created_at)->addMinutes($maxTotalResTime);
        }
        if ($this->priorities->policies->response_time == 'Hour') {
            $maxTime = Carbon::parse($this->created_at)->addHour($maxTotalResTime);
        }
        if ($this->priorities->policies->response_time == 'Day') {
            $maxTime = Carbon::parse($this->created_at)->addDays($maxTotalResTime);
        }
        if ($this->priorities->policies->response_time == 'Month') {
            $maxTime = Carbon::parse($this->created_at)->addMonths($maxTotalResTime);
        }
        if ($maxTime < $coTime) {
            return true;
        } else {
            return false;
        }
    }
    public function getResponsetimeIntoMonthAttribute()
    {
        $coTime = Conversion::where('ticket_id', $this->id)->latest()->first();

        $diff = round((strtotime($this->created_at) - strtotime($coTime)) / 86400);

        return $diff;
    }


    // public function getGroupTimeAttribute()
    // {
    //     $date = Carbon::now()->format('l');
    //     $ticket = \DB::table('groups')
    //     ->join('operating_hours','operating_hours.id','=','groups.operating_hours')
    //     ->select(['operating_hours.id','operating_hours.content','groups.name'])
    //     ->pluck('operating_hours.content','groups.name');

    //     foreach($ticket as $key => $value)
    //     {
    //         $value = json_decode($value, true);
    //         $resultKey = array_keys($value);
    //         dd($resultKey);
    //         $s = array_search($date,$resultKey);

    //         if ($s !== false) {

    //             $dates = Carbon::now()->format('H:i');
    //             if($dates)
    //             {
    //                 dd($resultKey);
    //             }
    //             else
    //             {
    //                 dd($s,$dates);
    //             }
    //         } else {
    //             dd('abvvbvb');
    //         }
    //     }
    // }


    protected $append = ['resolvetime', 'resolveTimeconvertinhours'];

    public function getResolvetimeAttribute()
    {

        $ticketResolveTime = strtotime($this->reslove_at);
        $datetime_1 = date('Y-m-d H:i:s', $ticketResolveTime);

        $ticketCreateTime = strtotime($this->created_at);
        $datetime_2 = date('Y-m-d H:i:s', $ticketCreateTime);


        if ($this->reslove_at == '0000-00-00 00:00:00') {
            return '-';
        }
        $diff = Carbon::createFromFormat('Y-m-d H:i:s', $datetime_1)->diffForHumans($datetime_2);
        return $diff;
    }


    public function getResolveTimeConvertInHoursAttribute()
    {

        // $maxTotalResolveTime = $this->priorities->policies->resolve_within;
        $maxTotalResolveTime = !empty($this->priorities->policies->resolve_within) ? $this->priorities->policies->resolve_within : 0;
        $ticketResolveTime = strtotime($this->reslove_at);
        $datetime_1 = date('Y-m-d H:i:s', $ticketResolveTime);
        $maxReTime = null;

        if ($this->priorities->policies->resolve_time == 'Hour') {
            $maxReTime = Carbon::parse($this->created_at)->addHour($maxTotalResolveTime);
            // dd($maxReTime);
        }
        if ($this->priorities->policies->resolve_time == 'Minute') {
            $maxReTime = Carbon::parse($this->created_at)->addMinutes($maxTotalResolveTime);
        }
        if ($this->priorities->policies->resolve_time == 'Day') {
            $maxReTime = Carbon::parse($this->created_at)->addDays($maxTotalResolveTime);
        }

        if ($this->priorities->policies->resolve_time == 'Month') {
            $maxReTime = Carbon::parse($this->created_at)->addMonths($maxTotalResolveTime);
        }


        if ($maxReTime < $datetime_1) {
            return true;
        } else {
            return false;
        }
    }






    public function conversions()
    {
        return $this->hasMany('App\Models\Conversion', 'ticket_id', 'id')->orderBy('id');
    }

    public function tcategory()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category');
    }

    public static function category($category)
    {
        $categoryArr  = explode(',', $category);
        $unitRate = 0;
        foreach ($categoryArr as $username) {
            $category     = Category::find($username);
            $unitRate     = $category->name ?? '';
        }
        return $unitRate;
    }

    public function priorities()
    {

        return $this->hasOne('App\Models\Priority', 'id', 'priority');
    }

    public static $statues = [
        'New Ticket',
        'In Progress',
        'On Hold',
        'Closed',
        'Resolved',
    ];
}
