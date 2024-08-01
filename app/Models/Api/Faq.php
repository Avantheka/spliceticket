<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'title', 'description','created_by'
    ];


    public static function getFaq($data)
    {
        $page = $data['page'];
        $per_page = $data['per_page'];

        $page_no  = $page == '' ? -1 : $page;
        $per_page = empty($per_page) ? 1 : $per_page;
        
        if($page_no != -1) {
            $faqs = Faq::orderBy('id', 'desc')->skip($page_no*$per_page)->take($per_page)->get();
        }else{
            $faqs = Faq::orderBy('id', 'desc')->get();
        }

        $faq_data=[];
        foreach($faqs as $faq){

            $faq_data[]=[
                'id'            => $faq->id != null ? $faq->id :'',
                'title'         => $faq->title,
                'description'   => $faq->description
            ];
        }

        return $faq_data;
    }


}
