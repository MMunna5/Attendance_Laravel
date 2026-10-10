@extends('layouts.app')
@section('title', ($mode ?? 'Add') . ' Teacher - Attendance Register')
@section('header_title', ($mode ?? 'Add') . " {$typeLabel} Teacher")

@section('content')
<form method="post" action="{{ isset($teacher) && $teacher ? route('teachers.update', $teacher) : route('teachers.store') }}" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-lg mx-auto space-y-5">
    @csrf
    @if($error)
    <div class="bg-red-50 text-absent border-l-4 border-absent px-4 py-2.5 rounded text-sm">{{ $error }}</div>
    @endif
    <input type="hidden" name="employment_type" value="{{ $employmentType }}">

    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Full Name</label>
        <input type="text" name="name" value="{{ old('name', $teacher->name ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Mobile Number @if(!isset($teacher) || !$teacher)(will be used as login username)@endif</label>
        <input type="text" name="mobile" value="{{ old('mobile', $teacher->mobile ?? '') }}" placeholder="01XXXXXXXXX (optional)" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    @if(!isset($teacher) || !$teacher)
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Login Username</label>
        <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
        <p class="text-xs text-ink-soft mt-1">Set a separate username; it does not have to be the mobile number.</p>
    </div>
    @endif
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Assigned Classes</label>
        <div class="grid grid-cols-3 gap-2 border border-gray-200 rounded-lg p-3">
            @foreach($classOptions as $c)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="assigned_classes[]" value="{{ $c['value'] }}" @if($c['is_selected']) checked @endif>
                {{ $c['value'] }}
            </label>
            @endforeach
        </div>
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">{{ isset($teacher) && $teacher ? 'Reset Password (optional)' : 'Password' }}</label>
        <input type="password" name="password" @if(!isset($teacher) || !$teacher) required @endif autocomplete="{{ isset($teacher) && $teacher ? 'new-password' : 'new-password' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <div class="flex gap-3">
        <a href="{{ route('teachers.index') }}" class="flex-1 text-center bg-gray-100 text-ink font-bold py-3 rounded-lg hover:bg-gray-200 transition">Cancel</a>
        <button type="submit" class="flex-1 bg-forest text-white font-bold py-3 rounded-lg hover:bg-forest-deep transition">{{ $mode }} Teacher</button>
    </div>
</form>
@endsection