@extends('layouts.app')
@section('title', 'Teachers - Attendance Register')
@section('header_title', 'Teachers')

@section('content')

@foreach([
    ['Full-time', $fullTimeTeachers, 'full', 0],
    ['Part-time', $partTimeTeachers, 'part', $fullTimeCount],
] as [$label, $list, $type, $offset])

<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div class="font-display text-[11px] tracking-widest uppercase text-ink-soft">{{ $label }} Teachers</div>
    <div class="flex gap-2">
        <a href="{{ route('teachers.upload', ['type' => $type]) }}"
            class="bg-forest text-white text-xs font-bold px-3 py-2 rounded-lg hover:bg-forest-deep transition">Upload Excel</a>
        <a href="{{ route('teachers.create', ['type' => $type]) }}"
            class="{{ $type === 'full' ? 'bg-gold hover:opacity-90' : 'bg-forest hover:bg-forest-deep' }} text-white text-xs font-bold px-3 py-2 rounded-lg transition">+ Add</a>
    </div>
</div>

@if($list->count())
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm {{ $type === 'full' ? 'mb-8' : '' }}">
    <div class="hidden sm:grid grid-cols-12 gap-2 px-4 py-2.5 bg-forest text-white text-xs font-bold uppercase tracking-wide">
        <div class="col-span-1">#</div>
        <div class="col-span-5">Name</div>
        <div class="col-span-3">Number</div>
        <div class="col-span-3 text-right">Action</div>
    </div>

    @foreach($list as $teacher)
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 px-4 py-3.5 items-center border-t border-gray-100 text-sm hover:bg-paper/60 transition">
        <div class="sm:col-span-1 font-mono text-ink-soft font-semibold">#{{ $loop->iteration + $offset }}</div>

        <div class="sm:col-span-5 break-words">
            <div class="font-semibold text-forest-deep">{{ $teacher->name }}</div>
            <div class="mt-1 flex flex-wrap gap-1">
                @forelse($teacher->class_list as $c)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-forest/10 text-forest">Class {{ $c }}</span>
                @empty
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-ink-soft">No class</span>
                @endforelse
            </div>
        </div>

        <div class="sm:col-span-3 text-ink-soft font-mono text-sm break-words">
            @if($teacher->mobile){{ $teacher->mobile }}@else<span class="text-absent">—</span>@endif
        </div>

        <div class="sm:col-span-3 flex gap-3 justify-start sm:justify-end text-sm font-semibold">
            <a href="{{ route('teachers.edit', $teacher) }}" class="text-forest hover:underline">Edit</a>
            <a href="{{ route('teachers.confirm-delete', $teacher) }}" class="text-absent hover:underline">Delete</a>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="text-center text-ink-soft py-8 {{ $type === 'full' ? 'mb-8' : '' }} bg-white border border-gray-200 rounded-xl text-sm">
    No {{ strtolower($label) }} teachers yet. Use Add or Upload Excel above.
</div>
@endif

@endforeach

@endsection
