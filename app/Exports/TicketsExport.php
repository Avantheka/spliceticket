<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class TicketsExport implements FromCollection ,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Ticket::where('created_by',\Auth::user()->createId())->get();

        foreach($data as $k => $tickets)
        {
            $category = Ticket::category($tickets->category);
           
            $priority = Ticket::Managepriority($tickets->priority);

            unset($tickets->id,$tickets->attachments,$tickets->resolve_at,$tickets->note,$tickets->created_by,$tickets->created_at,$tickets->updated_at);
            $data[$k]['category'] = $category;
            $data[$k]['priority'] = $priority;

        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Ticket ID",
            "Name",
            "Email",
            "Category",
            "Priority",
            "Subject",
            "Status",
            "Description",
        ];
    }
}



