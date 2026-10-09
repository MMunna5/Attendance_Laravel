@extends('layouts.app')
@section('title', 'SMS Status - Attendance Register')
@section('header_title', 'SMS Queue Status')

@section('content')
<form method="get" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Month</label>
        <input type="month" name="month" value="{{ $monthStr }}" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
    </div>
    <button type="submit" class="bg-forest text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-forest-deep transition">View Month</button>
</form>

<div class="grid grid-cols-2 gap-4 mb-6">
    @foreach([['Student', $counts['student']], ['Teacher', $counts['teacher']]] as [$label, $c])
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
        <div class="font-display text-[11px] tracking-widest uppercase text-ink-soft mb-2">{{ $label }} SMS · {{ $monthStr }}</div>
        <div class="grid grid-cols-3 gap-2 text-center">
            <div><div class="text-lg font-bold text-present">{{ $c['sent'] }}</div><div class="text-[10px] text-ink-soft">Sent</div></div>
            <div><div class="text-lg font-bold text-absent">{{ $c['failed'] }}</div><div class="text-[10px] text-ink-soft">Failed</div></div>
            <div><div class="text-lg font-bold text-amber-600">{{ $c['pending'] }}</div><div class="text-[10px] text-ink-soft">Pending</div></div>
        </div>
    </div>
    @endforeach
</div>
@foreach([['Student — Failed', $studentFailed, 'student'], ['Student — Pending', $studentPending, 'student'], ['Teacher — Failed', $teacherFailed, 'teacher'], ['Teacher — Pending', $teacherPending, 'teacher']] as [$label, $list, $kind])
<div class="font-display text-[11px] tracking-widest uppercase text-ink-soft mb-3 mt-6">{{ $label }} ({{ $list->count() }})</div>
<div class="space-y-2 mb-4">
    @forelse($list as $msg)
    <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="font-semibold text-forest-deep text-sm">{{ $kind === 'student' ? $msg->student->name : $msg->teacher->name }}</div>
            <div class="text-xs text-ink-soft">{{ $msg->date->format('d M Y') }} · Attempts: {{ $msg->attempts }}</div>
            @if($msg->last_error)
            <div class="text-xs text-absent mt-1 break-words">{{ $msg->last_error }}</div>
            @endif
        </div>
        @if(str_contains($label, 'Failed'))
        <form method="post" action="{{ route('sms-status.index') }}">
            @csrf
            <input type="hidden" name="action" value="retry">
            <input type="hidden" name="kind" value="{{ $kind }}">
            <input type="hidden" name="id" value="{{ $msg->id }}">
            <button type="submit" class="text-xs font-bold text-forest hover:underline flex-shrink-0">Retry</button>
        </form>
        @endif
    </div>
    @empty
    <div class="text-sm text-ink-soft py-3">None.</div>
    @endforelse
</div>
@endforeach
@endsection
