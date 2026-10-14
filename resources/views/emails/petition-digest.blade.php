@extends('emails.layout')

@section('preheader', $preheader)

@section('eyebrow', $eyebrow)
@section('heading', $heading)
@section('title', $heading.' · FreeCause')

@section('content')
    <p style="margin:0 0 16px;">{{ $greeting }}</p>
    <p style="margin:0 0 20px;">{{ $intro }}</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0;">
        @foreach ($items as $item)
            <tr>
                <td style="padding:14px 0;border-top:1px solid #E8E6E1;">
                    <a href="{{ $item['slug'] ? lroute('petition.show', ['locale' => $item['locale'], 'slug' => $item['slug'], 'id' => $item['id']]) : lroute('home', ['locale' => $locale]) }}" target="_blank" style="color:#1E1E1C;text-decoration:none;font-size:16px;font-weight:600;line-height:1.4;">{{ $item['title'] }}</a>
                    <div style="margin-top:4px;font-size:13px;color:#8E8A82;">{{ number_format($item['signature_count']) }} {{ $signaturesLabel }}</div>
                </td>
            </tr>
        @endforeach
    </table>
@endsection

@section('action', $actionLabel)
@section('action_url', lroute('petitions.index', ['locale' => $locale]))

@section('footer')
    {{ $footer }}<br>
    <a href="{{ $unsubscribeUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $unsubscribeLabel }}</a>
    &middot;
    <a href="{{ $preferencesUrl }}" style="color:#8E8A82;text-decoration:none;">{{ $manageLabel }}</a>
@endsection
