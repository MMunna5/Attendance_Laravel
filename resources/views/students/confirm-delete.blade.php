@extends('layouts.app')
@section('title', 'Delete Student - Attendance Register')
@section('header_title', 'Confirm Delete')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-md mx-auto text-center space-y-4">
    <p class="text-ink">Delete <strong>{{ $student->name }}</strong> (Roll {{ $student->roll_no }}, Class {{ $student->class_name }}{{ $student->section }})?</p>
    <p class="text-sm text-ink-soft">This will also delete all of their attendance history. This cannot be undone.</p>
    <form method="post" action="{{ route('students.destroy', $student) }}" class="flex gap-3 justify-center">
        @csrf
        <a href="{{ route('students.index') }}" class="bg-gray-100 text-ink font-bold px-5 py-2.5 rounded-lg hover:bg-gray-200 transition">Cancel</a>
        <button type="submit" class="bg-absent text-white font-bold px-5 py-2.5 rounded-lg hover:opacity-90 transition">Delete</button>
    </form>
</div>
@endsection
