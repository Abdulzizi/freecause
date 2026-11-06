@extends('emails.layout')

@section('preheader', $preheader)

@section('eyebrow', $eyebrow)
@section('heading', $heading)
@section('title', $title.' · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">{{ $hello }}</p>
    <p style="margin:0 0 16px;">{!! $intro !!}</p>
    <p style="margin:0;">{{ $share }}</p>
@endsection

@section('action', $action)
@section('action_url', $petitionUrl)

@section('footer')
    {{ $footer }}<br>
    <a href="{{ $unsubscribeUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $unsubscribeLabel }}</a>
    &middot;
    <a href="{{ $preferencesUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $manageLabel }}</a>
@endsection
