@extends('layouts.app')
@section('title', 'Upload Teachers - Attendance Register')
@section('header_title', 'Upload Teachers (Excel)')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-2xl mx-auto space-y-5">
    <p class="text-sm text-ink-soft">
        Columns required, in order: <strong>Name, Number, Class</strong>. New teachers log in with their mobile number as username;
        the default password comes from <code>DEFAULT_TEACHER_PASSWORD</code>.
    </p>
    <form method="post" action="{{ route('teachers.upload') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-ink-soft mb-1.5">Employment Type</label>
            <select name="employment_type" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm">
                <option value="full" @if($employmentType === 'full') selected @endif>Full-time</option>
                <option value="part" @if($employmentType === 'part') selected @endif>Part-time</option>
            </select>
        </div>
        <input type="file" name="excel_file[]" accept=".xlsx" multiple required class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5">
        <button type="submit" class="bg-forest text-white font-bold px-5 py-2.5 rounded-lg hover:bg-forest-deep transition">Upload</button>
    </form>

    @if(!empty($fileResults))
    <div class="space-y-3 mt-4">
        @foreach($fileResults as $r)
        <div class="border rounded-lg p-3 text-sm {{ isset($r['error']) ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50' }}">
            <div class="font-semibold">{{ $r['filename'] }}</div>
            @if(isset($r['error']))
                <div class="text-absent">{{ $r['error'] }}</div>
            @else
                <div class="text-ink-soft">Created: {{ $r['created'] }} · Updated: {{ $r['updated'] }} · Skipped: {{ $r['skipped'] }}</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
