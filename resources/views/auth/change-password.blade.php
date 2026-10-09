@extends('layouts.app')
@section('title', 'Change Password - Attendance Register')
@section('header_title', 'Change Password')

@section('content')
<form method="post" action="{{ route('change-password.update') }}" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-md mx-auto space-y-5">
    @csrf
    @if($error)
    <div class="bg-red-50 text-absent border-l-4 border-absent px-4 py-2.5 rounded text-sm">{{ $error }}</div>
    @endif
    @if($success)
    <div class="bg-green-50 text-present border-l-4 border-present px-4 py-2.5 rounded text-sm">Password changed successfully.</div>
    @endif

    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Current Password</label>
        <input type="password" name="old_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">New Password</label>
        <input type="password" name="new_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Confirm New Password</label>
        <input type="password" name="confirm_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <button type="submit" class="w-full bg-forest text-white font-bold py-3 rounded-lg hover:bg-forest-deep transition">Update Password</button>
</form>
@endsection
