<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use App\Models\Api\Ticket;

class Conversion extends Model
{
    protected $fillable = [
        'ticket_id','description', 'attachments', 'sender'
    ];


    public function getAttachmentsAttribute()
    {

        $data = json_decode($this->attributes['attachments'], true);
        $ticket = Ticket::find($this->ticket_id);

        $avatar=[];
        foreach($data as $attachment){
            $avatar[]= url('/storage/tickets/'.$ticket->ticket_id.'/'.$attachment);
        }

        return $avatar;
    }

    public function getSenderAttribute(){
        $data = User::find($this->attributes['sender']);
        return $data->name;
    }


    public function ticket(){
        return $this->hasOne('App\Models\Ticket','id','ticket_id');
    }

}
