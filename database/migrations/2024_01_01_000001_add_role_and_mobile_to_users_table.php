<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Django used User.is_staff to mark admins. Laravel's default `users`
 * table has no such flag, so we add `is_admin`. Teacher logins in the
 * source app use their mobile number as the username (see
 * TeacherUploadController), so we also add a `username` column here.
 * If you'd rather keep Laravel's default `email` as the login field,
 * skip the username block and use `email` in the Teacher controllers
 * instead -- just be consistent everywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
