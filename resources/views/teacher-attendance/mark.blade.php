@extends('layouts.app')
@section('title', 'Teacher Attendance - Attendance Register')
@section('header_title', "Today's Teacher Attendance")
@section('header_meta')
<div class="text-sm text-green-100 mt-1">{{ \Carbon\Carbon::parse($today)->format('l, d F Y') }}</div>
@endsection

@section('content')
@if(session('saved'))
<div class="bg-green-50 border border-green-200 text-forest rounded-xl p-4 mb-5 text-sm">
    Teacher attendance saved. @if(session('sms_queued_count'))· {{ session('sms_queued_count') }} absence SMS queued.@endif
</div>
@endif

@foreach([['full', 'Full-time', $fullTimeRows, $fullAlreadyMarked, $fullPresent, $fullAbsent], ['part', 'Part-time', $partTimeRows, $partAlreadyMarked, $partPresent, $partAbsent]] as [$sectionKey, $label, $rows, $already, $present, $absent])
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <div class="font-display text-[11px] tracking-widest uppercase text-ink-soft">{{ $label }} Teachers ({{ $rows->count() }})</div>
        @if($already)
        <div class="flex gap-2 text-xs font-semibold">
            <span class="text-present">{{ $present }} Present</span>
            <span class="text-absent">{{ $absent }} Absent</span>
        </div>
        @endif
    </div>

    @if($rows->count())
    <form method="post" action="{{ route('teacher-attendance.store') }}">
        @csrf
        <input type="hidden" name="section" value="{{ $sectionKey }}">
        <div class="space-y-2 mb-4">
            @foreach($rows as $row)
            <div class="flex items-center gap-3 border border-gray-100 rounded-lg p-2.5">
                <div class="flex-1 min-w-0 font-semibold text-forest-deep text-sm">{{ $row['teacher']->name }}</div>
                @if($already)
                    <span class="px-3 py-1.5 rounded-lg text-[11px] font-bold {{ $row['is_present'] ? 'bg-present text-white' : 'bg-absent text-white' }}">
                        {{ $row['is_present'] ? 'Present' : 'Absent' }}
                    </span>
                @else
                    <label class="cursor-pointer">
                        <input type="radio" name="tatt_{{ $row['teacher']->id }}" value="present" class="peer sr-only" checked>
                        <span class="block text-center px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-white border border-gray-200 text-ink-soft peer-checked:bg-present peer-checked:text-white peer-checked:border-present transition">Present</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tatt_{{ $row['teacher']->id }}" value="absent" class="peer sr-only">
                        <span class="block text-center px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-white border border-gray-200 text-ink-soft peer-checked:bg-absent peer-checked:text-white peer-checked:border-absent transition">Absent</span>
                    </label>
                @endif
            </div>
            @endforeach
        </div>
        @if(!$already)
        <button type="submit" class="w-full bg-forest text-white font-bold py-2.5 rounded-lg hover:bg-forest-deep transition">Save {{ $label }} Attendance</button>
        @endif
    </form>
    @else
    <div class="text-sm text-ink-soft py-4 text-center">No {{ strtolower($label) }} teachers found.</div>
    @endif
</div>
@endforeach
@endsection
