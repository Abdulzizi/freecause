@extends('admin.layouts.app')

@section('title', 'Forbidden')

@section('content')

    <div class="fc-box" style="padding:40px; text-align:center; max-width:600px; margin:60px auto;">

        <h1 style="font-size:28px; margin-bottom:15px;">403 - Access Forbidden</h1>

        <p style="margin-bottom:20px; color:#666;">
            You do not have permission to access this section.
        </p>

        <a href="{{ route('admin.options.global') }}" class="fc-btn">
            Go Back to Dashboard
        </a>

    </div>

@endsection
