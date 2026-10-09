<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'roll_no', 'name', 'class_name', 'section', 'parent_mobile',
    ];

    protected static function booted()
    {
        // Same default ordering as Django's Meta.ordering.
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('class_name')->orderBy('section')->orderBy('roll_no');
        });
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function absenceSms()
    {
        return $this->hasMany(AbsenceSms::class);
    }
}
