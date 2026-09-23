@extends('emails.layout')

@section('preheader', 'Use the secure link below to set a new password for your FreeCause account.')

@section('eyebrow', 'Password Reset')
@section('heading', 'Reset your password')
@section('title', 'Reset your password · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $firstName }},</p>
    <p style="margin:0 0 16px;">We received a request to reset the password for your FreeCause account. Click the button below to choose a new one.</p>
    <p style="margin:0;">This link is private and expires in 60 minutes. If you didn't request a reset, you can ignore this email and your password stays the same.</p>
@endsection

@section('action', 'Set a New Password')
@section('action_url', $resetUrl)

@section('note')
    <strong>Can't see the button?</strong> Copy and paste this link into your browser:
    <span style="word-break:break-all;">{{ $resetUrl }}</span>
@endsection