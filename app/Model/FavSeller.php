<?php

namespace App\Model;

use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class FavSeller extends Model
{
    protected $table = 'fav_sellers';

    public function seller(){
        return $this->belongsTo(Seller::class,'seller_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

}
