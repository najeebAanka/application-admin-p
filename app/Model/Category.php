<?php

namespace App\Model;

use App\CPU\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    protected $casts = [
        'parent_id' => 'integer',
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'home_section' => 'integer',
        'home_status' => 'integer',
        'is_mad_test' => 'integer',
        'products_style	' => 'integer',
        'home_top' => 'integer',
    ];

    public function translations()
    {
        return $this->morphMany('App\Model\Translation', 'translationable');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function childes()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getNameAttribute($name)
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/seller')) {
            return $name;
        }

        return $this->translations[0]->value ?? $name;
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
