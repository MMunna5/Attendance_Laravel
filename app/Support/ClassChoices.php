<?php

namespace App\Support;

use App\Models\Student;

/**
 * Shared helper used by AttendanceController, StudentController and the
 * history controllers -- port of get_class_choices()/get_section_choices()
 * in views.py. Centralized here instead of duplicated per-controller.
 */
class ClassChoices
{
    public static function all(): array
    {
        $names = Student::withoutGlobalScopes()
            ->distinct()
            ->pluck('class_name')
            ->filter()
            ->map(fn ($c) => trim($c))
            ->filter()
            ->unique()
            ->values()
            ->all();

        usort($names, function ($a, $b) {
            $group = fn ($s) => preg_match('/^(play|nursery|kg|pre)/i', $s) ? 0 : (preg_match('/\d/', $s) ? 1 : 2);
            $num = fn ($s) => preg_match('/(\d+)/', $s, $m) ? (int) $m[1] : 0;
            return [$group($a), $num($a), strtolower($a)] <=> [$group($b), $num($b), strtolower($b)];
        });

        return $names;
    }

    public static function sections(): array
    {
        return Student::withoutGlobalScopes()
            ->where('section', '!=', '')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->all();
    }
}
