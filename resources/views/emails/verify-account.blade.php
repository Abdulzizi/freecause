@extends('emails.layout')

@php
    $verifyUrl = route('verify.account', [
        'locale' => $locale,
        'token'  => $token,
    ]);
@endphp

@section('preheader', $preheader)

@section('eyebrow', $eyebrow)
@section('heading', $greeting)
@section('title', $greeting.' · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">{{ $hello }}</p>
    <p style="margin:0 0 16px;">{{ $intro }}</p>
    <p style="margin:0;">{{ $hint }}</p>
@endsection

@section('action', $buttonText)
@section('action_url', $verifyUrl)

@section('note')
    <strong>{{ $noteExpiry }}</strong><br>
    {{ $noteLinkHelp }}
    <span style="word-break:break-all;">{{ $verifyUrl }}</span>
    <br><br>
    {{ $noteIgnore }}
@endsection

@section('footer', $footer)
