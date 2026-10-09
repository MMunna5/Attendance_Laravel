@extends('layouts.app')
@section('title', 'Delete Teacher - Attendance Register')
@section('header_title', 'Confirm Delete')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-md mx-auto text-center space-y-4">
    <p class="text-ink">Delete <strong>{{ $teacher->name }}</strong> and their login?</p>
    <p class="text-sm text-ink-soft">This cannot be undone.</p>
    <form method="post" action="{{ route('teachers.destroy', $teacher) }}" class="flex gap-3 justify-center">
        @csrf
        <a href="{{ route('teachers.index') }}" class="bg-gray-100 text-ink font-bold px-5 py-2.5 rounded-lg hover:bg-gray-200 transition">Cancel</a>
        <button type="submit" class="bg-absent text-white font-bold px-5 py-2.5 rounded-lg hover:opacity-90 transition">Delete</button>
    </form>
</div>
@endsection
