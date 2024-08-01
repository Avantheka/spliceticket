<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
            'name', 'color','created_by'
    ];

    public static function getCategory($data)
    {

        $categories = Category::where('created_by',\Auth::user()->id)->orderBy('id', 'desc')->get();

        $category_data=[];
        foreach($categories as $Category){

            $category_data[]=[
                'id'            => $Category->id != null ? $Category->id :'',
                'name'          => $Category->name,
                'color'         => $Category->color
            ];

        }

        return $category_data;
    }

}
