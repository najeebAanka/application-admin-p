<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HelpTopic extends Model
{
    protected $table = 'help_topics';
    protected $casts = [

        'ranking'    => 'integer',
        'status'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    public function scopeStatus($query)
    {
        return $query->where('status', 1);
    }
}
