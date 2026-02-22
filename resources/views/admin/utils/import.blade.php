@extends('admin.layouts.app')

@section('title', 'Import')

@section('content')

    <h1>import</h1>

    {{-- @if (session('success'))
        <div class="fc-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="fc-error">
            {{ implode(', ', $errors->all()) }}
        </div>
    @endif --}}

    <div class="fc-box" style="padding:20px;">

        <form method="post" action="{{ route('admin.utils.import.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:15px;">
                <label>Import Type</label><br>
                <select name="type" class="fc-input" required>
                    <option value="users">Users</option>
                    <option value="categories">Categories</option>
                    <option value="petitions">Petitions</option>
                    <option value="signatures">Signatures</option>
                </select>
            </div>

            <div style="margin-bottom:15px;">
                <label>CSV File</label><br>
                <input type="file" name="file" required>
            </div>

            <button class="fc-btn">Import</button>
        </form>

    </div>

@endsection
