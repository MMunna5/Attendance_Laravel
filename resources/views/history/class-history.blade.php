@extends('layouts.app')
@section('title', 'Class History - Attendance Register')
@section('header_title', 'Class Attendance History')

@section('content')
@if(!empty($noAccess))
<div class="text-center text-ink-soft py-16 bg-white border border-gray-200 rounded-2xl">You don't have access to any class yet.</div>
@else
<form method="get" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm flex flex-wrap gap-3 items-end">
    <div class="min-w-[140px]">
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Class</label>
        <select name="class" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
            @foreach($allowedClasses as $c)
            <option value="{{ $c }}" @if($c === $classFilter) selected @endif>Class {{ $c }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-[140px]">
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">View</label>
        <select name="view" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
            <option value="day" @if($viewMode === 'day') selected @endif>By Date</option>
            <option value="month" @if($viewMode === 'month') selected @endif>By Month</option>
        </select>
    </div>
    @if($viewMode === 'day')
    <input type="date" name="date" value="{{ $dateFilter ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
    @else
    <input type="month" name="month" value="{{ $monthStr ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
    @endif
    <button type="submit" class="bg-forest text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-forest-deep transition">Filter</button>
    @if($classFilter)
    <a href="{{ route('history.export-attendance-month', ['class' => $classFilter, 'month' => $monthStr ?? '']) }}" class="bg-gold text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:opacity-90 transition">Export Month Excel</a>
    @endif
</form>

@if($viewMode === 'month')
<div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm">
    <table class="w-full text-sm">
        <thead><tr class="bg-forest text-white"><th class="p-2 text-left">Roll</th><th class="p-2 text-left">Name</th><th class="p-2">Present</th><th class="p-2">Absent</th><th class="p-2"></th></tr></thead>
        <tbody>
            @foreach($records as $r)
            <tr class="border-b">
                <td class="p-2">{{ $r['student']->roll_no }}</td><td class="p-2">{{ $r['student']->name }}</td>
                <td class="p-2 text-center text-present font-bold">{{ $r['present'] }}</td>
                <td class="p-2 text-center text-absent font-bold">{{ $r['absent'] }}</td>
                <td class="p-2 text-center"><a href="{{ route('student-history-detail', $r['student']) }}" class="text-forest text-xs font-semibold hover:underline">Details</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="flex gap-4 text-sm font-semibold mb-3">
    <span class="text-present">Present: {{ $presentCount }}</span>
    <span class="text-absent">Absent: {{ $absentCount }}</span>
</div>
<div class="space-y-2">
        @foreach($records as $r)
    <a href="{{ route('student-history-detail', $r['student']) }}" class="bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between hover:border-forest hover:bg-forest/5 transition">
        <div>
            <div class="font-semibold text-forest-deep text-sm">{{ $r['student']->name }}</div>
            <div class="text-xs text-ink-soft">Roll {{ $r['student']->roll_no }}</div>
        </div>
        <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $r['status'] === 'present' ? 'bg-present text-white' : ($r['status'] === 'absent' ? 'bg-absent text-white' : 'bg-gray-100 text-ink-soft') }}">
            {{ ucfirst(str_replace('_', ' ', $r['status'])) }}
        </span>
    </div>
    @endforeach
</div>
@endif
@endif
@endsection
