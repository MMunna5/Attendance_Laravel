<?php

namespace App\Support;

use App\Models\User;

class ViewerAccess
{
    public static function allowedClasses(User $user): array
    {
        if ($user->is_admin) {
            return [true, ClassChoices::all()];
        }
        $teacher = $user->teacher;
        if (!$teacher) {
            return [false, []];
        }
        $all = ClassChoices::all();
        return [false, array_values(array_intersect($teacher->class_list, $all))];
    }
}