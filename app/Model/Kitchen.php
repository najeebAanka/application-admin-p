<?php

namespace App\Model;

use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Kitchen extends Authenticatable
{
    protected $table = 'kitchens';


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
