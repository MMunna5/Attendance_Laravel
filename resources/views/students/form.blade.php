@extends('layouts.app')
@section('title', ($mode ?? 'Add') . ' Student - Attendance Register')
@section('header_title', ($mode ?? 'Add') . ' Student')

@section('content')
<form method="post" action="{{ isset($student) && $student ? route('students.update', $student) : route('students.store') }}" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-lg mx-auto space-y-5">
    @csrf
    @if($error)
    <div class="bg-red-50 text-absent border-l-4 border-absent px-4 py-2.5 rounded text-sm">{{ $error }}</div>
    @endif

    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Roll Number</label>
        <input type="text" name="roll_no" value="{{ old('roll_no', $student->roll_no ?? '') }}" required placeholder="e.g. 12" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Full Name</label>
        <input type="text" name="name" value="{{ old('name', $student->name ?? '') }}" required placeholder="Student's full name" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-ink-soft mb-1.5">Class</label>
            <select name="class_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
                <option value="">Select class</option>
                @foreach($classOptions as $c)
                <option value="{{ $c['value'] }}" @if($c['is_selected']) selected @endif>{{ $c['value'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-ink-soft mb-1.5">Section</label>
            <select name="section" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
                <option value="">No section</option>
                @foreach($sectionOptions as $s)
                <option value="{{ $s['value'] }}" @if($s['is_selected']) selected @endif>{{ $s['value'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Parent's Mobile Number</label>
        <input type="text" name="parent_mobile" value="{{ old('parent_mobile', $student->parent_mobile ?? '') }}" placeholder="01XXXXXXXXX" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
        <p class="text-xs text-ink-soft mt-1.5">SMS will be sent to this number when the student is absent</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('students.index') }}" class="flex-1 text-center bg-gray-100 text-ink font-bold py-3 rounded-lg hover:bg-gray-200 transition">Cancel</a>
        <button type="submit" class="flex-1 bg-forest text-white font-bold py-3 rounded-lg hover:bg-forest-deep transition">{{ $mode }} Student</button>
    </div>
</form>
@endsection
