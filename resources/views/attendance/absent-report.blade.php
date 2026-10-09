@extends('layouts.app')
@section('title', 'Absent Report - Attendance Register')
@section('header_title', 'Absent Report')

@section('content')

@if(!empty($noAccess))
<div class="text-center text-ink-soft py-16 bg-white border border-gray-200 rounded-2xl">No class is assigned to you yet. Please contact the admin.</div>
@else

@if(count($allowedClasses) > 1)
<form method="get" class="flex gap-2 mb-4">
    <input type="hidden" name="date" value="{{ $targetDate->toDateString() }}">
    <select name="class" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-forest">
        @foreach($allowedClasses as $c)
        <option value="{{ $c }}" @if($c === $classFilter) selected @endif>Class {{ $c }}</option>
        @endforeach
    </select>
</form>
@endif

<div class="flex items-center justify-between bg-white border border-gray-200 rounded-xl p-3 mb-5 shadow-sm">
    <a href="?class={{ $classFilter }}&date={{ $prevDate }}" class="text-forest font-bold text-sm px-3 py-1.5 hover:bg-forest/5 rounded-lg">← Prev Day</a>
    <div class="text-center">
        <div class="font-display text-sm font-bold text-forest-deep">{{ $targetDate->format('l, d M Y') }}</div>
        <div class="text-[11px] text-ink-soft">Class {{ $classFilter }}@if($isToday) · Today @endif</div>
    </div>
    <a href="?class={{ $classFilter }}&date={{ $nextDate }}" class="text-forest font-bold text-sm px-3 py-1.5 hover:bg-forest/5 rounded-lg">Next Day →</a>
</div>

@if(!$wasTaken)
<div class="text-center text-ink-soft py-16 bg-white border border-gray-200 rounded-2xl">
    Attendance was not taken for Class {{ $classFilter }} on this date.
</div>
@else

<div class="grid grid-cols-2 gap-3 mb-5">
    <div class="bg-white border border-gray-200 rounded-2xl px-4 py-3 text-center shadow-sm">
        <div class="text-[11px] uppercase tracking-widest text-ink-soft">Absent</div>
        <div class="text-2xl font-bold text-absent">{{ $absentCount }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-2xl px-4 py-3 text-center shadow-sm">
        <div class="text-[11px] uppercase tracking-widest text-ink-soft">Total Marked</div>
        <div class="text-2xl font-bold text-forest-deep">{{ $markedCount }}/{{ $totalStudents }}</div>
    </div>
</div>

@if(!$isComplete)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-3 mb-5 text-xs">
    Only {{ $markedCount }} of {{ $totalStudents }} students were marked this day — the list below may be incomplete.
</div>
@endif

@if($absentees->count())
<div class="space-y-2.5">
    @foreach($absentees as $a)
    <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-3 shadow-sm">
        <div class="w-10 h-10 rounded-full border-2 border-absent flex items-center justify-center text-sm font-bold text-absent flex-shrink-0 bg-red-50">
            {{ $a->student->roll_no }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-forest-deep break-words">{{ $a->student->name }}</div>
            <div class="text-xs text-ink-soft">
                Class {{ $a->student->class_name }}{{ $a->student->section }}
                @if($a->student->parent_mobile) · {{ $a->student->parent_mobile }} @endif
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-absent">Absent</span>
    </div>
    @endforeach
</div>
@else
<div class="text-center text-present py-16 bg-white border border-gray-200 rounded-2xl font-semibold">Nobody was absent that day. 🎉</div>
@endif

@endif
@endif
@endsection