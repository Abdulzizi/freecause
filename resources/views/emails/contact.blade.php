@extends('emails.layout')

@section('preheader', 'A new message arrived through the FreeCause contact form.')

@section('eyebrow', 'Contact Form · Inbound')
@section('heading', 'New contact form message')
@section('title', 'New contact form message · FreeCause')
@section('accent', true)

@section('content')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 6px;">
        <tr>
            <td style="padding:6px 0;font-family:Menlo,Consolas,monospace;font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:#A39A90;width:86px;">Sender</td>
            <td style="padding:6px 0 6px 16px;font-size:14px;color:#2E2C29;">{{ $senderName }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0;font-family:Menlo,Consolas,monospace;font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:#A39A90;width:86px;">Email</td>
            <td style="padding:6px 0 6px 16px;font-size:14px;color:#2E2C29;">
                <a href="mailto:{{ $senderEmail }}" style="color:#B01F1F;text-decoration:none;">{{ $senderEmail }}</a>
            </td>
        </tr>
    </table>

    <div style="border-left:3px solid #cc1f1f;background:#F7F5F1;background-clip:padding-box;border-radius:0 8px 8px 0;padding:18px 20px;margin:18px 0 4px;white-space:pre-wrap;font-size:14px;line-height:1.7;color:#3A3834;">{{ $body }}</div>
@endsection

@section('action', 'Reply to '.$senderName)
@section('action_url', 'mailto:'.$senderEmail)

@section('note', 'This message was sent through the contact form on www.freecause.com. Replying sends an email directly to the sender.')