<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'duration',
        'max_agent',
        'trial',
        'trial_days',
        'enable_custdomain',
        'enable_custsubdomain',
        'enable_chatgpt',
        'description',
        'storage_limit',
        'image',
    ];

    public static $arrDuration = [
        'Lifetime' => 'Lifetime',
        'month' => 'Per Month',
        'year' => 'Per Year',
    ];

     public function status()
    {
        return [
            __('Lifetime'),
            __('Per Month'),
            __('Per Year'),
        ];
    }

    public static function total_plan()
    {
        return Plan::count();
    }

    public static function most_purchese_plan()
    {
        $free_plan = Plan::where('price', '<=', 0)->first()->id;

        return User:: select('plans.name', 'plans.id', \DB::raw('count(*) as total'))->join('plans', 'plans.id', '=', 'users.plan')->where('type', '=', 'owner')->where('plan', '!=', $free_plan)->orderBy('total', 'Desc')->groupBy('plans.name', 'plans.id')->first();
    }
}
