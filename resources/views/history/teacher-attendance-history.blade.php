@extends('layouts.app')
@section('title', 'Teacher Attendance History - Attendance Register')
@section('header_title', 'Teacher Attendance History')

@section('content')
<form method="get" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm flex flex-wrap gap-3 items-end">
    <input type="month" name="month" value="{{ $monthStr }}" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
    <button type="submit" class="bg-forest text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-forest-deep transition">Filter</button>
    <a href="{{ route('history.export-teacher-attendance', ['month' => $monthStr]) }}" class="bg-gold text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:opacity-90 transition">Export Excel</a>
</form>

@foreach([['Full-time', $fullTimeRecords, 0], ['Part-time', $partTimeRecords, $fullTimeRecords->count()]] as [$label, $records, $offset])
<div class="font-display text-[11px] tracking-widest uppercase text-ink-soft mb-3 mt-6">{{ $label }} ({{ $records->count() }})</div>
<div class="space-y-2.5 mb-6">
    @forelse($records as $r)
    <a href="{{ route('teacher-history-detail', $r['teacher']) }}?month={{ $monthStr }}"
        class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-3 shadow-sm hover:border-forest hover:bg-forest/5 transition">
        <div class="w-10 h-10 rounded-full border-2 border-forest flex items-center justify-center text-sm font-bold text-forest flex-shrink-0 bg-forest/5">
            #{{ $loop->iteration + $offset }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-forest-deep break-words">{{ $r['teacher']->name }}</div>
            <div class="text-xs text-ink-soft">{{ $r['teacher']->mobile ?: 'No phone' }}</div>
        </div>
        <div class="flex-shrink-0 flex gap-4 text-sm font-bold">
            <span class="text-present">{{ $r['present'] }} Present</span>
            <span class="text-absent">{{ $r['absent'] }} Absent</span>
        </div>
        <div class="flex-shrink-0 text-forest text-xs font-bold hidden sm:block">Details →</div>
    </a>
    @empty
    <div class="text-center text-ink-soft py-8 bg-white border border-gray-200 rounded-xl text-sm">No {{ strtolower($label) }} teachers found.</div>
    @endforelse
</div>
@endforeach
@endsection