@extends('emails.layout')

@section('preheader', 'Your petition just passed a new signature milestone.')

@section('eyebrow', 'Milestone Reached')
@section('heading', 'A milestone for your petition')
@section('title', 'Your petition reached '.number_format($milestone).' signatures · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">Hello {{ $firstName }},</p>
    <p style="margin:0 0 16px;">Your petition <strong>{{ $petitionTitle }}</strong> just reached <strong>{{ number_format($milestone) }}</strong> signatures.</p>
    <p style="margin:0;">Share the petition with your supporters to keep the momentum going.</p>
@endsection

@section('action', 'View your petition')
@section('action_url', $petitionUrl)

@section('footer')
    You are receiving this email because you created a petition on FreeCause.<br>
    <a href="{{ $unsubscribeUrl }}" style="color:#8E8A82;text-decoration:none;">Unsubscribe from these emails</a>
    &middot;
    <a href="{{ $preferencesUrl }}" style="color:#8E8A82;text-decoration:none;">Manage preferences</a>
@endsection
