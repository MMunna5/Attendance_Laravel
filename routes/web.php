<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmergencySmsController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SmsStatusController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Health check -- no DB queries, no external calls (same contract as
// Django's /health/ endpoint, used for Render liveness probes).
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('change-password.show');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.update');

    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/absent-report', [AttendanceController::class, 'absentReport'])->name('absent-report');
    Route::get('/attendance-export-month', [HistoryController::class, 'exportAttendanceMonth'])->name('history.export-attendance-month');

    Route::get('/history', [HistoryController::class, 'classHistory'])->name('class-history');
    Route::get('/history/student/{student}', [HistoryController::class, 'studentHistoryDetail'])->name('student-history-detail');

    // --- Admin-only ---
    Route::middleware('admin')->prefix('admin-panel')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/add', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students/add', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::post('/students/{student}/edit', [StudentController::class, 'update'])->name('students.update');
        Route::get('/students/{student}/delete', [StudentController::class, 'confirmDelete'])->name('students.confirm-delete');
        Route::post('/students/{student}/delete', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::match(['get', 'post'], '/students/upload', [StudentController::class, 'upload'])->name('students.upload');
        Route::get('/students/class/{className}/delete', [StudentController::class, 'confirmDeleteClass'])->name('students.confirm-delete-class');
        Route::post('/students/class/{className}/delete', [StudentController::class, 'destroyClass'])->name('students.destroy-class');

        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/add', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers/add', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::post('/teachers/{teacher}/edit', [TeacherController::class, 'update'])->name('teachers.update');
        Route::get('/teachers/{teacher}/delete', [TeacherController::class, 'confirmDelete'])->name('teachers.confirm-delete');
        Route::post('/teachers/{teacher}/delete', [TeacherController::class, 'destroy'])->name('teachers.destroy');
        Route::match(['get', 'post'], '/teachers/upload', [TeacherController::class, 'upload'])->name('teachers.upload');

        Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'show'])->name('teacher-attendance.show');
        Route::post('/teacher-attendance', [TeacherAttendanceController::class, 'store'])->name('teacher-attendance.store');
        Route::get('/teacher-attendance-history', [HistoryController::class, 'teacherAttendanceHistory'])->name('teacher-attendance-history');
        Route::get('/teacher-attendance-history/{teacher}', [HistoryController::class, 'teacherHistoryDetail'])->name('teacher-history-detail');
        Route::get('/teacher-attendance-export', [HistoryController::class, 'exportTeacherAttendance'])->name('history.export-teacher-attendance');

        Route::get('/attendance-history', [HistoryController::class, 'attendanceHistory'])->name('history.attendance');
        Route::get('/attendance-export', [HistoryController::class, 'exportAttendance'])->name('history.export-attendance');
        Route::post('/attendance/{student}/correct', [HistoryController::class, 'correctAttendance'])->name('history.correct-attendance');

        Route::match(['get', 'post'], '/sms-status', [SmsStatusController::class, 'index'])->name('sms-status.index');
        Route::match(['get', 'post'], '/emergency-sms', [EmergencySmsController::class, 'index'])->name('emergency-sms.index');
    });
});
