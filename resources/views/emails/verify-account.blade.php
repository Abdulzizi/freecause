@extends('emails.layout')

@php
    $verifyUrl = route('verify.account', [
        'locale' => $locale,
        'token'  => $token,
    ]);
@endphp

@section('preheader', 'One click and your FreeCause account is active.')

@section('eyebrow', 'Account Verification')
@section('heading', $greeting ?? 'Welcome to FreeCause')
@section('title', ($greeting ?? 'Welcome to FreeCause').' · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $firstName }},</p>
    <p style="margin:0 0 16px;">Thank you for joining FreeCause. Please confirm your email address so your account is activated and your signature counts.</p>
    <p style="margin:0;">Confirming takes a single click — no password required.</p>
@endsection

@section('action', $buttonText ?? 'Verify My Account')
@section('action_url', $verifyUrl)

@section('note')
    <strong>Your secure link is valid for 48 hours.</strong><br>
    If the button above does not work, copy and paste this link into your browser:
    <span style="word-break:break-all;">{{ $verifyUrl }}</span>
    <br><br>
    Didn't create an account with FreeCause? You can safely ignore this email.
@endsection