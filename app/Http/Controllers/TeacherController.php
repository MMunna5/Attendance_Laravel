<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Services\SmsService;
use App\Support\ClassChoices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    private array $teacherHeaders = ['Name', 'Number', 'Class'];

    public function index()
    {
        $fullTime = Teacher::with('user')->where('employment_type', Teacher::EMPLOYMENT_FULL)->orderBy('id')->get();
        $partTime = Teacher::with('user')->where('employment_type', Teacher::EMPLOYMENT_PART)->orderBy('id')->get();

        return view('teachers.index', [
            'fullTimeTeachers' => $fullTime,
            'partTimeTeachers' => $partTime,
            'fullTimeCount' => $fullTime->count(),
        ]);
    }

    public function create(Request $request)
    {
        $employmentType = $request->query('type', Teacher::EMPLOYMENT_FULL);
        if (!in_array($employmentType, [Teacher::EMPLOYMENT_FULL, Teacher::EMPLOYMENT_PART], true)) {
            $employmentType = Teacher::EMPLOYMENT_FULL;
        }

        return view('teachers.form', [
            'mode' => 'Add',
            'error' => null,
            'teacher' => null,
            'employmentType' => $employmentType,
            'typeLabel' => $employmentType === Teacher::EMPLOYMENT_FULL ? 'Full-time' : 'Part-time',
            'classOptions' => $this->classOptionsMulti([]),
        ]);
    }

        public function store(Request $request)
    {
        $username = trim($request->input('username', ''));
        $password = trim($request->input('password', ''));
        $name = trim($request->input('name', ''));
        $mobile = SmsService::cleanPhoneForStorage($request->input('mobile', ''));
        $employmentType = in_array($request->input('employment_type'), [Teacher::EMPLOYMENT_FULL, Teacher::EMPLOYMENT_PART], true)
            ? $request->input('employment_type') : Teacher::EMPLOYMENT_FULL;
        $assignedClasses = implode(',', array_filter(array_map('trim', (array) $request->input('assigned_classes', []))));

        if ($username === '' || $password === '' || $name === '') {
            return back()->withInput()->with('error', 'Name, login username, and password are required.');
        }
        if (preg_match('/^[\\pL\\pN@.+_-]+$/u', $username) !== 1) {
            return back()->withInput()->with('error', 'Username may contain letters, numbers, and @ . + - _ only; spaces are not allowed.');
        }
        if (User::where('username', $username)->exists()) {
            return back()->withInput()->with('error', "Username '{$username}' is already taken.");
        }

        DB::transaction(function () use ($username, $password, $mobile, $name, $assignedClasses, $employmentType) {
            $user = User::create([
                'username' => $username,
                'name' => $name,
                'email' => $username . '@placeholder.local',
                'password' => Hash::make($password),
            ]);
            Teacher::create([
                'user_id' => $user->id,
                'name' => $name,
                'mobile' => $mobile,
                'assigned_classes' => $assignedClasses,
                'employment_type' => $employmentType,
            ]);
        });

        return redirect()->route('teachers.index');
    }
        
          

    public function edit(Teacher $teacher)
    {
        return view('teachers.form', [
            'mode' => 'Edit',
            'teacher' => $teacher,
            'error' => null,
            'employmentType' => $teacher->employment_type,
            'typeLabel' => $teacher->employment_type === Teacher::EMPLOYMENT_FULL ? 'Full-time' : 'Part-time',
            'classOptions' => $this->classOptionsMulti($teacher->class_list),
        ]);
    }

        public function update(Request $request, Teacher $teacher)
    {
        $employmentType = in_array($request->input('employment_type'), [Teacher::EMPLOYMENT_FULL, Teacher::EMPLOYMENT_PART], true)
            ? $request->input('employment_type') : Teacher::EMPLOYMENT_FULL;
        $mobile = SmsService::cleanPhoneForStorage($request->input('mobile', ''));

        $teacher->update([
            'name' => trim($request->input('name', '')),
            'mobile' => $mobile,
            'employment_type' => $employmentType,
            'assigned_classes' => implode(',', array_filter(array_map('trim', (array) $request->input('assigned_classes', [])))),
        ]);

        $newPassword = trim($request->input('password', ''));
        if ($newPassword && $teacher->user) {
            $teacher->user->update(['password' => Hash::make($newPassword)]);
        }

        return redirect()->route('teachers.index');
    }
    public function confirmDelete(Teacher $teacher)
    {
        return view('teachers.confirm-delete', compact('teacher'));
    }

    public function destroy(Teacher $teacher)
    {
        $linkedUser = $teacher->user;
        $teacher->delete();
        $linkedUser?->delete();
        return redirect()->route('teachers.index');
    }

    /**
     * Bulk teacher import. Mirrors teacher_upload() in views.py, including
     * the DEFAULT_TEACHER_PASSWORD guard and mobile-number-as-username
     * convention (config('school.default_teacher_password')).
     */
    public function upload(Request $request)
    {
        $fileResults = [];
        $employmentType = $request->input('employment_type', $request->query('type', Teacher::EMPLOYMENT_FULL));
        if (!in_array($employmentType, [Teacher::EMPLOYMENT_FULL, Teacher::EMPLOYMENT_PART], true)) {
            $employmentType = Teacher::EMPLOYMENT_FULL;
        }
        $typeLabel = $employmentType === Teacher::EMPLOYMENT_FULL ? 'Full-time' : 'Part-time';

        if ($request->isMethod('post') && $request->hasFile('excel_file')) {
            foreach ((array) $request->file('excel_file') as $file) {
                $filename = $file->getClientOriginalName();
                if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
                    $fileResults[] = ['filename' => $filename, 'error' => 'Only .xlsx files are allowed.'];
                    continue;
                }

                try {
                    $rows = \App\Support\ExcelReader::rows($file);
                } catch (\Throwable $e) {
                    $fileResults[] = ['filename' => $filename, 'error' => 'Could not open this file: ' . $e->getMessage()];
                    continue;
                }

                if (empty($rows)) {
                    $fileResults[] = ['filename' => $filename, 'error' => 'File is empty.'];
                    continue;
                }

                $headerError = $this->checkHeader($rows[0], $this->teacherHeaders);
                if ($headerError) {
                    $fileResults[] = ['filename' => $filename, 'error' => $headerError];
                    continue;
                }

                $parsedTeachers = [];
                $skipped = 0;
                foreach (array_slice($rows, 1) as $row) {
                    if (empty(array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== ''))) {
                        continue;
                    }
                    $name = trim((string) ($row[0] ?? ''));
                    $mobile = SmsService::cleanPhoneForStorage($row[1] ?? null);
                    $class = trim((string) ($row[2] ?? ''));
                    if (str_ends_with($class, '.0')) $class = substr($class, 0, -2);

                    if ($name === '' || $mobile === '') {
                        $skipped++;
                        continue;
                    }
                    $parsedTeachers[$mobile] = compact('name', 'mobile', 'class');
                }

                if (empty($parsedTeachers)) {
                    $fileResults[] = ['filename' => $filename, 'error' => "No valid rows found. All {$skipped} row(s) were missing a Name or Number value."];
                    continue;
                }

                $defaultPw = config('school.default_teacher_password');
                if (!$defaultPw) {
                    $fileResults[] = ['filename' => $filename, 'error' => 'DEFAULT_TEACHER_PASSWORD is not configured. Set it in the deployment environment before importing teachers.'];
                    continue;
                }

                $created = 0;
                $updated = 0;
                DB::transaction(function () use ($parsedTeachers, $employmentType, $defaultPw, &$created, &$updated) {
                    foreach ($parsedTeachers as $mobile => $data) {
                        $user = User::where('username', $mobile)->first();
                        if (!$user) {
                            $user = User::create([
                                'username' => $mobile,
                                'name' => $data['name'],
                                'email' => $mobile . '@placeholder.local',
                                'password' => Hash::make($defaultPw),
                            ]);
                        }

                        $teacher = Teacher::where('mobile', $mobile)->first();
                        if ($teacher) {
                            $teacher->update([
                                'name' => $data['name'],
                                'assigned_classes' => $data['class'],
                                'employment_type' => $employmentType,
                                'user_id' => $user->id,
                            ]);
                            $updated++;
                        } else {
                            Teacher::create([
                                'name' => $data['name'],
                                'mobile' => $mobile,
                                'assigned_classes' => $data['class'],
                                'user_id' => $user->id,
                                'employment_type' => $employmentType,
                            ]);
                            $created++;
                        }
                    }
                });

                $fileResults[] = ['filename' => $filename, 'created' => $created, 'updated' => $updated, 'skipped' => $skipped];
            }
        }

        return view('teachers.upload', compact('fileResults', 'employmentType', 'typeLabel'));
    }

    private function checkHeader(array $headerRow, array $expected): ?string
    {
        $actual = [];
        foreach (array_keys($expected) as $i) {
            $actual[] = isset($headerRow[$i]) ? strtolower(trim((string) $headerRow[$i])) : '';
        }
        $expectedLower = array_map(fn ($h) => strtolower(trim($h)), $expected);
        if ($actual !== $expectedLower) {
            return 'Header row is not in the correct format. The first row must have these exact columns, in this order: ' . implode(', ', $expected) . '. Please fix the header and upload again.';
        }
        return null;
    }

    private function classOptionsMulti(array $selected): array
    {
        $selectedSet = array_flip($selected);
        return collect(ClassChoices::all())->map(fn ($c) => ['value' => $c, 'is_selected' => isset($selectedSet[$c])])->all();
    }
}
