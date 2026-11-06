@extends('emails.layout')

@php $t = fn ($key, $replace = []) => \App\Support\MailText::get($key, $replace, $emailLocale); @endphp

@section('preheader', $heading)

@section('eyebrow', $heading)
@section('heading', $heading)
@section('title', $heading.' · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">{{ $t('common_greeting', ['name' => $firstName]) }}</p>
    <p style="margin:0 0 16px;">{{ $intro }}</p>

    @if ($signers->isNotEmpty())
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0;">
            @foreach ($signers as $signer)
                <tr>
                    <td style="padding:10px 0;border-top:1px solid #E8E6E1;">
                        <div style="font-size:14px;font-weight:600;color:#1E1E1C;">{{ $signer['name'] }}</div>
                        @if ($signer['snippet'])
                            <div style="margin-top:2px;font-size:13px;color:#8E8A82;">&ldquo;{{ $signer['snippet'] }}&rdquo;</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <p style="margin:0;">{{ $guestNote }}</p>
    @endif
@endsection

@section('action', $t('view_petition'))
@section('action_url', $petitionUrl)

@section('footer')
    {{ $footer }}<br>
    <a href="{{ $unsubscribeUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $unsubscribeLabel }}</a>
    &middot;
    <a href="{{ $preferencesUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $manageLabel }}</a>
@endsection
