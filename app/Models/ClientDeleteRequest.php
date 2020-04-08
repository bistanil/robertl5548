<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ClientDeleteRequest extends Model
{
   use Notifiable;

    protected $fillable = [
        'client_id' ,'email','name','phone','content','status', 'account_action', 'download_info','second_status'
    ];
}
