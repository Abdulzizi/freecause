@extends('emails.layout')

@php $t = fn ($key, $replace = []) => \App\Support\MailText::get($key, $replace, $emailLocale); @endphp

@section('title', $t('welcome_subject'))
@section('preheader', $t('welcome_intro'))
@section('heading', $t('welcome_subject'))

@section('content')
    <p style="margin:0 0 16px;">{{ $t('welcome_intro') }}</p>
    <p style="margin:0;">{{ $t('welcome_body') }}</p>
@endsection

@section('action', $t('welcome_action'))
@section('action_url', url('/'.$emailLocale.'/petitions'))
@section('footer', $t('welcome_footer'))
