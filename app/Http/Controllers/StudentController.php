<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\SmsService;
use App\Support\ClassChoices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    private array $studentHeaders = ['Roll', 'Name', 'Class', 'Section', 'Session', 'Phone'];

    public function index(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $classFilter = trim((string) $request->query('class', ''));

        $classNames = $this->classChoices();
        $students = Student::withoutGlobalScopes()->orderBy('class_name')->orderBy('section')->orderBy('roll_no');
        if ($classFilter) {
            $students->where('class_name', $classFilter);
        }
        if ($query) {
            $students->where('name', 'like', "%{$query}%");
        }

        $totalCount = (clone $students)->count();
        $paginated = $students->paginate(50)->withQueryString();

        return view('students.index', [
            'students' => $paginated,
            'totalCount' => $totalCount,
            'query' => $query,
            'classFilter' => $classFilter,
            'allClasses' => collect($classNames)->map(fn ($c) => ['name' => $c, 'is_selected' => $c === $classFilter]),
        ]);
    }

    public function create()
    {
        return view('students.form', [
            'mode' => 'Add',
            'error' => null,
            'student' => null,
            'classOptions' => collect($this->classChoices())->map(fn ($c) => ['value' => $c, 'is_selected' => false]),
            'sectionOptions' => collect($this->sectionChoices())->map(fn ($s) => ['value' => $s, 'is_selected' => false]),
        ]);
    }

    public function store(Request $request)
    {
        try {
            Student::create([
                'roll_no' => trim($request->input('roll_no', '')),
                'name' => trim($request->input('name', '')),
                'class_name' => trim($request->input('class_name', '')),
                'section' => trim($request->input('section', '')),
                'parent_mobile' => SmsService::cleanPhoneForStorage($request->input('parent_mobile', '')),
            ]);
            return redirect()->route('students.index');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('error', 'A student with this Roll Number already exists in this Class/Section. Please check and try again.');
        }
    }

    public function edit(Student $student)
    {
        return view('students.form', [
            'mode' => 'Edit',
            'student' => $student,
            'error' => null,
            'classOptions' => collect($this->classChoices())->map(fn ($c) => ['value' => $c, 'is_selected' => $c === $student->class_name]),
            'sectionOptions' => collect($this->sectionChoices())->map(fn ($s) => ['value' => $s, 'is_selected' => $s === $student->section]),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        try {
            $student->update([
                'roll_no' => trim($request->input('roll_no', '')),
                'name' => trim($request->input('name', '')),
                'class_name' => trim($request->input('class_name', '')),
                'section' => trim($request->input('section', '')),
                'parent_mobile' => SmsService::cleanPhoneForStorage($request->input('parent_mobile', '')),
            ]);
            return redirect()->route('students.index');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('error', 'A student with this Roll Number already exists in this Class/Section. Please check and try again.');
        }
    }

    public function confirmDelete(Student $student)
    {
        return view('students.confirm-delete', compact('student'));
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index');
    }

    public function confirmDeleteClass(string $className)
    {
        $count = Student::withoutGlobalScopes()->where('class_name', $className)->count();
        return view('history.class-confirm-delete', compact('className', 'count'));
    }

    public function destroyClass(string $className)
    {
        Student::withoutGlobalScopes()->where('class_name', $className)->delete();
        return redirect()->route('students.index');
    }

    /**
     * Excel bulk import/update. Uses maatwebsite/excel to read rows
     * (composer require maatwebsite/excel), mirroring student_upload()
     * in views.py: header validation, create-vs-update by
     * (class,section,roll), and a per-file result summary.
     */
    public function upload(Request $request)
    {
        $fileResults = [];

        if ($request->isMethod('post') && $request->hasFile('excel_file')) {
            foreach ((array) $request->file('excel_file') as $file) {
                $filename = $file->getClientOriginalName();
                if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
                    $fileResults[] = ['filename' => $filename, 'error' => 'This is not an .xlsx file. Please save it as an Excel (.xlsx) file and try again.'];
                    continue;
                }

                try {
                    $rows = \App\Support\ExcelReader::rows($file);
                } catch (\Throwable $e) {
                    $fileResults[] = ['filename' => $filename, 'error' => 'Could not open this file: ' . $e->getMessage()];
                    continue;
                }

                if (empty($rows)) {
                    $fileResults[] = ['filename' => $filename, 'error' => 'This file appears to be empty (only a header row, or no rows at all).'];
                    continue;
                }

                $headerError = $this->checkHeader($rows[0], $this->studentHeaders);
                if ($headerError) {
                    $fileResults[] = ['filename' => $filename, 'error' => $headerError];
                    continue;
                }

                if (count($rows) < 2) {
                    $fileResults[] = ['filename' => $filename, 'error' => 'This file appears to be empty (only a header row, or no rows at all).'];
                    continue;
                }

                $parsedRows = [];
                $skipped = 0;
                $detectedClasses = [];

                foreach (array_slice($rows, 1) as $row) {
                    if (empty(array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== ''))) {
                        continue;
                    }
                    if (count($row) < 3) {
                        $skipped++;
                        continue;
                    }

                    $roll = $this->cleanCell($row[0] ?? null);
                    $class = $this->cleanCell($row[2] ?? null);
                    $name = trim((string) ($row[1] ?? ''));
                    $section = trim((string) ($row[3] ?? ''));
                    if (str_ends_with($section, '.0')) $section = substr($section, 0, -2);
                    $phone = SmsService::cleanPhoneForStorage($row[5] ?? null);

                    if ($roll === '' || $name === '' || $class === '') {
                        $skipped++;
                        continue;
                    }

                    $key = "{$class}|{$section}|{$roll}";
                    $parsedRows[$key] = compact('roll', 'class', 'section', 'name', 'phone');
                    $detectedClasses["{$class}{$section}"] = true;
                }

                if (empty($parsedRows)) {
                    $fileResults[] = ['filename' => $filename, 'error' => "No valid rows found. All {$skipped} row(s) were missing a Roll, Name, or Class value."];
                    continue;
                }

                $created = 0;
                $updated = 0;
                DB::transaction(function () use ($parsedRows, &$created, &$updated) {
                    foreach ($parsedRows as $data) {
                        $student = Student::withoutGlobalScopes()->firstOrNew([
                            'class_name' => $data['class'],
                            'section' => $data['section'],
                            'roll_no' => $data['roll'],
                        ]);
                        $exists = $student->exists;
                        $student->name = $data['name'];
                        $student->parent_mobile = $data['phone'];
                        $student->save();
                        $exists ? $updated++ : $created++;
                    }
                });

                $fileResults[] = [
                    'filename' => $filename,
                    'class_label' => implode(', ', array_keys($detectedClasses)) ?: 'Unknown',
                    'created' => $created,
                    'updated' => $updated,
                    'skipped' => $skipped,
                ];
            }
        }

        return view('students.upload', compact('fileResults'));
    }

    private function cleanCell($value): string
    {
        $s = trim((string) ($value ?? ''));
        if (str_ends_with($s, '.0')) $s = substr($s, 0, -2);
        return $s;
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

    private function classChoices(): array
    {
        return ClassChoices::all();
    }

    private function sectionChoices(): array
    {
        return ClassChoices::sections();
    }
}
