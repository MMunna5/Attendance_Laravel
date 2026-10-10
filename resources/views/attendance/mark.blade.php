@extends('layouts.app')
@section('title', 'Take Attendance - Attendance Register')
@section('header_title', "Today's Attendance")
@section('header_meta')
<div class="text-sm text-green-100 mt-1">{{ \Carbon\Carbon::parse($today)->format('l, d F Y') }}</div>
@endsection

@section('content')

@if(!empty($error))
<div class="bg-red-50 border border-red-200 text-absent rounded-xl p-4 mb-5 text-sm">{{ $error }}</div>
@else

<div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="text-xs font-semibold text-ink-soft uppercase tracking-wide">Today</div>
        <div class="text-lg font-bold text-forest-deep">{{ \Carbon\Carbon::parse($today)->format('l') }}</div>
        <div class="text-sm text-ink-soft">
            {{ \Carbon\Carbon::parse($today)->format('d F Y') }}@if($currentClass) · Class {{ $currentClass }}@endif
        </div>
    </div>
    @if(collect($studentRows)->contains('is_marked', true) || $isAdminUser)
    <div class="flex gap-3">
        <div class="bg-green-50 text-present font-bold px-4 py-2 rounded-xl text-center">
            <div class="text-xl">{{ collect($studentRows)->where('is_marked', true)->where('is_present', true)->count() }}</div>
            <div class="text-xs font-semibold">Present</div>
        </div>
        <div class="bg-red-50 text-absent font-bold px-4 py-2 rounded-xl text-center">
            <div class="text-xl">{{ collect($studentRows)->where('is_marked', true)->where('is_present', false)->count() }}</div>
            <div class="text-xs font-semibold">Absent</div>
        </div>
        <div class="bg-gray-50 text-ink-soft font-bold px-4 py-2 rounded-xl text-center">
            <div class="text-xl">{{ count($studentRows) }}</div>
            <div class="text-xs font-semibold">Total</div>
        </div>
    </div>
    @endif
</div>

@if(session('saved'))
<div class="bg-green-50 border border-green-200 text-forest rounded-xl p-4 mb-5 text-sm">
    Attendance saved successfully.
    @if(session('sms_queued_count'))· {{ session('sms_queued_count') }} absence SMS queued for background delivery.@endif
</div>
@endif

@if($alreadyMarked && !$isAdminUser && !session('saved'))
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 mb-5 text-sm">
    Attendance for this class has already been taken today. It can only be taken once per day.
</div>
@endif

@if(session('sms_warning'))
<div class="bg-red-50 border border-red-200 text-absent rounded-xl p-4 mb-5 text-sm">{{ session('sms_warning') }}</div>
@endif

@if($showClassSelector)
<form method="get" class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Select Class</label>
        <select name="class" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
            <option value="">-- Choose a class --</option>
            @foreach($allClasses as $c)
            <option value="{{ $c['name'] }}" @if($c['is_selected']) selected @endif>Class {{ $c['name'] }}</option>
            @endforeach
        </select>
    </div>
</form>
@endif

@if(!empty($classStatuses))
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-5 shadow-sm">
    <div class="text-xs font-semibold text-ink-soft uppercase tracking-wide mb-3">Today's class status</div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
        @foreach($classStatuses as $status)
        <a href="?class={{ $status['name'] }}" class="border rounded-lg px-3 py-2 hover:border-forest transition {{ $status['name'] == $currentClass ? 'border-forest bg-forest/5' : 'border-gray-200' }}">
            <div class="font-semibold text-forest-deep text-sm">Class {{ $status['name'] }}</div>
            @if($status['is_complete'])
            <div class="text-xs text-present font-semibold mt-1">Complete · {{ $status['student_count'] }}</div>
            @else
            <div class="text-xs text-amber-700 font-semibold mt-1">Pending: {{ $status['attendance_count'] }}/{{ $status['student_count'] }}</div>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

@if(count($studentRows))
<form method="post" action="{{ route('attendance.store') }}" id="attForm" onsubmit="return confirmSave();">
    @csrf
    @if($showClassSelector && $currentClass)
    <input type="hidden" name="class" value="{{ $currentClass }}">
    @endif

    <div class="space-y-2 mb-6">
        @foreach($studentRows as $item)
        <div class="student-attendance-row bg-white border border-gray-200 rounded-xl p-3 flex flex-wrap sm:flex-nowrap items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-full border-2 border-gold flex items-center justify-center text-xs font-bold text-gold flex-shrink-0 bg-gold/5">
                {{ $item['student']->roll_no }}
            </div>
            <div class="flex-1 min-w-[calc(100%-3rem)] sm:min-w-0">
                <div class="font-semibold text-forest-deep break-words text-sm">{{ $item['student']->name }}</div>
                @if($item['student']->parent_mobile)
                <div class="text-[11px] text-ink-soft">{{ $item['student']->parent_mobile }}</div>
                @else
                <div class="text-[11px] text-absent">No phone</div>
                @endif
            </div>
            <div class="w-full sm:w-auto flex gap-1.5 sm:flex-shrink-0 justify-end">
                @if($alreadyMarked && !$isAdminUser)
                    @if($item['is_present'])
                    <span class="px-3 py-1.5 rounded-lg text-[11px] font-bold bg-present text-white">Present</span>
                    @else
                    <span class="px-3 py-1.5 rounded-lg text-[11px] font-bold bg-absent text-white">Absent</span>
                    @endif
                @else
                    <label class="cursor-pointer">
                        <input type="radio" name="att_{{ $item['student']->id }}" value="present" class="peer sr-only" @if($item['present_checked']) checked @endif>
                        <span class="present-option block text-center whitespace-nowrap px-2.5 py-2 rounded-lg text-[11px] font-bold bg-white border border-gray-200 text-ink-soft peer-checked:bg-present peer-checked:text-white peer-checked:border-present transition">Present</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="att_{{ $item['student']->id }}" value="absent" class="peer sr-only" @if($item['absent_checked']) checked @endif>
                        <span class="absent-option block text-center whitespace-nowrap px-2.5 py-2 rounded-lg text-[11px] font-bold bg-white border border-gray-200 text-ink-soft peer-checked:bg-absent peer-checked:text-white peer-checked:border-absent transition">Absent</span>
                    </label>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if(!$alreadyMarked || $isAdminUser)
    <div class="sticky bottom-4">
        <button type="submit" class="w-full bg-forest text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-forest-deep transition text-base">
            Save Attendance & Send SMS
        </button>
    </div>
    @endif
</form>

<script>
    function syncAttendanceButtons(input) {
        const row = input.closest('.student-attendance-row');
        if (!row) return;
        const presentInput = row.querySelector('input[value="present"]');
        const presentButton = row.querySelector('.present-option');
        const absentButton = row.querySelector('.absent-option');
        if (!presentButton || !absentButton) return;
        presentButton.className = 'present-option block text-center whitespace-nowrap px-2.5 py-2 rounded-lg text-[11px] font-bold transition border';
        absentButton.className = 'absent-option block text-center whitespace-nowrap px-2.5 py-2 rounded-lg text-[11px] font-bold transition border';
        if (presentInput && presentInput.checked) {
            presentButton.classList.add('bg-present', 'text-white', 'border-present');
            absentButton.classList.add('bg-white', 'text-ink-soft', 'border-gray-200');
        } else {
            presentButton.classList.add('bg-white', 'text-ink-soft', 'border-gray-200');
            absentButton.classList.add('bg-absent', 'text-white', 'border-absent');
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.student-attendance-row input[type="radio"]').forEach(function (input) {
            input.addEventListener('change', function () { syncAttendanceButtons(input); });
            if (input.checked) syncAttendanceButtons(input);
        });
    });
    function confirmSave() { return confirm("Save attendance and send SMS?"); }
</script>

@elseif($showClassSelector && !$currentClass)
<div class="text-center text-ink-soft py-16 bg-white border border-gray-200 rounded-2xl">
    Please select a class above to take attendance.
</div>
@else
<div class="text-center text-ink-soft py-16 bg-white border border-gray-200 rounded-2xl">
    No students found in this class yet.
</div>
@endif

@endif
@endsection
