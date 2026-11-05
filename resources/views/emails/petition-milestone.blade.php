@extends('emails.layout')

@php $t = fn ($key, $replace = []) => \App\Support\MailText::get($key, $replace, $emailLocale); @endphp

@section('title', $t('milestone_subject', ['count' => number_format($milestone)]))
@section('heading', $t('milestone_subject', ['count' => number_format($milestone)]))
@section('preheader', $t('milestone_intro', ['title' => $petitionTitle, 'count' => number_format($milestone)]))
@section('content')
    <p style="margin:0 0 16px;">{{ $t('common_greeting', ['name' => $firstName]) }}</p>
    <p style="margin:0;">{{ $t('milestone_intro', ['title' => $petitionTitle, 'count' => number_format($milestone)]) }}</p>
@endsection
@section('action', $t('view_petition'))
@section('action_url', $petitionUrl)
@section('footer')
    {{ $t('petition_new_signatures_footer') }}<br>
    <a href="{{ $unsubscribeUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $t('petition_new_signatures_unsubscribe') }}</a>
    &middot;
    <a href="{{ $preferencesUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $t('common_manage_preferences') }}</a>
@endsection
