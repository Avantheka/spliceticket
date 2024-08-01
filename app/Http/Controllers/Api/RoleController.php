<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendCloseTicket;
use App\Mail\SendTicket;
use Illuminate\Support\Facades\Mail;
use App\Models\Api\User;
use App\Models\Api\Category;
use App\Models\Api\Ticket;
use Spatie\Permission\Models\Role;


class RoleController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $roles = Role::get();

        $data = [
            'role' =>$roles
        ];

        return $this->success($data);
    }

}