<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

use App\Models\Category;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_id',
        'name',
        'email',
        'category',
        'subject',
        'status',
        'description',
		'created_by',
        'attachments',
        'note',
    ];

    protected $appends = ["color",'time'];

    public function getColorAttribute()
    {

        $category = Category::find($this->attributes['category']);
        $category = (!empty($category->color)) ? $category->color : '';

        return $category;
    }
	
	    public function getTimeAttribute(){
        $timestamp = strtotime($this->created_at);

        $strTime = array("second", "minute", "hour", "day", "month", "year");
        $length = array("60","60","24","30","12","10");

        $currentTime = time();
        if($currentTime >= $timestamp) {
            $diff     = time()- $timestamp;
                for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
                    $diff = $diff / $length[$i];
                }
            $diff = round($diff);
            return $diff . " " . $strTime[$i] . " ago";
        }
    }

    public function getCategoryAttribute()
    {
        $category = Category::find($this->attributes['category']);
        $category = (!empty($category->name)) ? $category->name : '';

        return $category;
    }

    public function getAttachmentsAttribute()
    {
        $attachments = $this->attributes['attachments'];
        $attachment = json_decode($attachments, true);
            $attachments_arr=[];
            foreach ($attachment as $key => $value) {
                    $attachments_arr[]=$value;
            }
        return $attachments_arr;
    }



    public static function Ticket($data)
    {
        return $tickets;
    }



    public function conversions()
    {
        return $this->hasMany('App\Models\Api\Conversion', 'ticket_id', 'id')->orderBy('id');
    }

    public function tcategory()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category');
    }

    public static function category($category)
    {
        $categoryArr  = explode(',', $category);
        $unitRate = 0;
        foreach($categoryArr as $username)
        {
            $category     = Category::find($category);
            $unitRate     = $category->name;
        }
        return $unitRate;
    }


    public static function getIncExpLineChartDate()
    {

        $m             = date("m");
        $de            = date("d");
        $y             = date("Y");
        $format        = 'Y-m-d';
        $arrDate       = [];
        $arrDateFormat = [];

        for($i = 7; $i >= 0; $i--)
        {
            $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));

            $arrDay[]        = date('D', mktime(0, 0, 0, $m, ($de - $i), $y));
            $arrDate[]       = $date;
            $arrDateFormat[] = date("d", strtotime($date)) .'-'.__(date("M", strtotime($date)));
        }
        $data['day'] = $arrDateFormat;

        $open_ticket = array();
        $close_ticket = array();

        for($i = 0; $i < count($arrDate); $i++)
        {

            $aopen_ticket = Ticket::whereIn('status', ['On Hold','In Progress'])->whereDate('created_at', $arrDate[$i])->get();
            $open_ticket[] =  count($aopen_ticket);

            $aclose_ticket = Ticket::where('status', '=', 'Closed')->whereDate('created_at', $arrDate[$i])->get();
            $close_ticket[] = count($aclose_ticket);

        }

        $data['open_ticket']    = $open_ticket;
        $data['close_ticket']      = $close_ticket;

        return $data;
    }
}
