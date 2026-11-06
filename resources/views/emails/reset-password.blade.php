@extends('emails.layout')

@php $t = fn ($key, $replace = []) => \App\Support\MailText::get($key, $replace, $emailLocale); @endphp

@section('title', $t('reset_subject'))
@section('heading', $t('reset_subject'))
@section('preheader', $t('reset_intro'))
@section('content')
    <p style="margin:0 0 16px;">{{ $t('common_greeting', ['name' => $firstName]) }}</p>
    <p style="margin:0 0 16px;">{{ $t('reset_intro') }}</p>
    <p style="margin:0;">{{ $t('reset_note', ['minutes' => $resetMinutes]) }}</p>
@endsection
@section('action', $t('reset_action'))
@section('action_url', $resetUrl)
@section('note')
    <span style="word-break:break-all;">{{ $t('link_help', ['url' => $resetUrl]) }}</span>
@endsection
