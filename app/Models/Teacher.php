<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    const EMPLOYMENT_FULL = 'full';
    const EMPLOYMENT_PART = 'part';

    protected $fillable = [
        'user_id', 'name', 'mobile', 'assigned_classes', 'employment_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function absenceSms()
    {
        return $this->hasMany(TeacherAbsenceSms::class);
    }

    /**
     * Mirrors Django's Teacher.get_class_list(): a clean, de-duplicated
     * list built from the comma-separated `assigned_classes` string.
     */
    public function getClassListAttribute(): array
    {
        if (!$this->assigned_classes) {
            return [];
        }
        $seen = [];
        foreach (explode(',', $this->assigned_classes) as $part) {
            $cleaned = trim($part);
            if ($cleaned !== '' && !in_array($cleaned, $seen, true)) {
                $seen[] = $cleaned;
            }
        }
        return $seen;
    }
}
