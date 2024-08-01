<?php

namespace App\Models;

use App\Models\Knowledge;
use App\Models\Knowledgebasecategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    protected $fillable = [
        'title', 'description', 'category','created_by',
    ];

    public function getCategoryInfo()
    {
       $th = $this->hasOne('App\Models\Knowledgebasecategory', 'id', 'category');
        return $th;
    }

    public static function knowlege_details($id)
    {
        $knowledge = Knowledgebasecategory::where('id',$id)->first();
        if($knowledge)
        {
            return $knowledge->title;
        }
    }

    public static function category_count($id)
    {
        $knowledge = Knowledge::where('category',$id)->count();
        if($knowledge)
        {
            return $knowledge;
        }
    }

}




