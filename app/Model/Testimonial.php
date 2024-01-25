<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\CPU\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Testimonial extends Model
{
    protected $table = 'testimonials';

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function translations()
    {
        return $this->morphMany('App\Model\Translation', 'translationable');
    }

    public function getTitleAttribute($title)
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/seller')) {
            return $title;
        }
        return $this->translations[0]->value ?? $title;
    }

    public function getPositionAttribute($position)
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/seller')) {
            return $position;
        }
        return $this->translations[1]->value ?? $position;
    }

    public function getDescriptionAttribute($description)
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/seller')) {
            return $description;
        }
        return $this->translations[2]->value ?? $description;
    }


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                } else {
                    return $query->where('locale', Helpers::default_lang());
                }
            }]);
        });
    }

}
