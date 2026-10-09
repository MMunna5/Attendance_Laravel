@extends('layouts.app')
@section('title', 'Students - Attendance Register')
@section('header_title', 'Students')

@section('content')
<form method="get" class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[140px]">
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Class</label>
        <select name="class" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
            <option value="">All Classes</option>
            @foreach($allClasses as $c)
            <option value="{{ $c['name'] }}" @if($c['is_selected']) selected @endif>Class {{ $c['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Search by Name</label>
        <input type="text" name="q" value="{{ $query }}" placeholder="Type student name..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-forest">
    </div>
    <button type="submit" class="bg-forest text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-forest-deep transition">Search</button>
    <a href="{{ route('students.create') }}" class="bg-gold text-white text-sm font-bold px-5 py-2.5 rounded-lg hover:opacity-90 transition">+ Add Student</a>
    <a href="{{ route('students.upload') }}" class="bg-gray-100 text-ink text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-gray-200 transition">Upload Excel</a>
</form>

@if(!$classFilter && !$query)
<div class="font-display text-[11px] tracking-widest uppercase text-ink-soft mb-4">Choose a Class</div>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
    @forelse($allClasses as $c)
    <a href="?class={{ $c['name'] }}" class="bg-white border border-gray-200 rounded-2xl p-6 text-center shadow-sm hover:shadow-md hover:border-forest hover:-translate-y-0.5 transition-all duration-200">
        <div class="text-2xl font-bold text-forest-deep mb-1">{{ $c['name'] }}</div>
        <div class="text-xs text-ink-soft">Class</div>
    </a>
    @empty
    <div class="col-span-full text-center text-ink-soft py-12">No classes found. Please upload students first.</div>
    @endforelse
</div>

@elseif($students->count())
<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <div class="text-sm text-ink-soft">
        Showing <span class="font-semibold text-ink">{{ $students->count() }}</span> of <span class="font-semibold text-ink">{{ $totalCount }}</span> student{{ $totalCount === 1 ? '' : 's' }}
        @if($classFilter) · Class {{ $classFilter }}@endif
    </div>
    @if($classFilter)
    <a href="{{ route('students.confirm-delete-class', $classFilter) }}" class="text-xs font-semibold text-absent hover:underline">Delete All in Class {{ $classFilter }}</a>
    @endif
</div>

<div class="space-y-2.5">
    @foreach($students as $student)
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-4 shadow-sm hover:shadow-md transition">
        <div class="w-11 h-11 rounded-full border-2 border-forest flex items-center justify-center text-sm font-bold text-forest flex-shrink-0 bg-forest/5">
            {{ $student->roll_no }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-forest-deep break-words text-[15px]">{{ $student->name }}</div>
            <div class="text-xs text-ink-soft mt-0.5">
                Class {{ $student->class_name }}{{ $student->section }}
                @if($student->parent_mobile) · {{ $student->parent_mobile }} @else · <span class="text-absent">No phone</span> @endif
            </div>
        </div>
        <div class="flex gap-4 text-sm font-semibold flex-shrink-0">
            <a href="{{ route('students.edit', $student) }}" class="text-forest hover:underline">Edit</a>
            <a href="{{ route('students.confirm-delete', $student) }}" class="text-absent hover:underline">Delete</a>
        </div>
    </div>
    @endforeach
</div>

<div class="flex justify-center mt-6">{{ $students->links() }}</div>

@else
<div class="text-center text-ink-soft py-20 bg-white border border-gray-200 rounded-2xl">
    <div class="text-lg font-medium mb-1">No students found</div>
    <div class="text-sm">Try changing the class or search term.</div>
</div>
@endif
@endsection
