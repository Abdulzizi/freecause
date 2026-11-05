@extends('emails.layout')

@section('title', __('profile.welcome_subject'))
@section('preheader', __('profile.welcome_intro'))
@section('heading', __('profile.welcome_subject'))

@section('content')
    <p style="margin:0 0 16px;">{{ __('profile.welcome_intro') }}</p>
    <p style="margin:0;">{{ __('profile.welcome_body') }}</p>
@endsection

@section('action', __('profile.welcome_action'))
@section('action_url', url('/'.$emailLocale.'/petitions'))
@section('footer', __('profile.welcome_footer'))
