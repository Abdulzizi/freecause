@extends('emails.layout')

@php $t = fn ($key, $replace = []) => \App\Support\MailText::get($key, $replace, $emailLocale); @endphp

@php $verifyUrl = route('verify.account', ['locale' => $emailLocale, 'token' => $token]); @endphp
@section('title', $t('verify_subject'))
@section('heading', $t('verify_heading'))
@section('preheader', $t('verify_intro'))
@section('content')
    <p style="margin:0 0 16px;">{{ $t('common_greeting', ['name' => $firstName]) }}</p>
    <p style="margin:0 0 16px;">{{ $t('verify_intro') }}</p>
    <p style="margin:0;">{{ $t('verify_note', ['hours' => 48]) }}</p>
@endsection
@section('action', $t('verify_action'))
@section('action_url', $verifyUrl)
@section('note')
    <span style="word-break:break-all;">{{ $t('link_help', ['url' => $verifyUrl]) }}</span>
@endsection

@section('footer', $t('verify_footer'))
