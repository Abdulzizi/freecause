@php
    $logoUrl = asset('legacy/images/logo7.png');
@endphp
<!DOCTYPE html>
<html lang="{{ $emailLocale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'FreeCause')</title>
</head>
<body style="margin:0;padding:0;background:#F4F3F0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F3F0;">
        <tr>
            <td align="center" style="padding:40px 16px 48px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

                    <tr>
                        <td align="center" style="padding:0 0 30px;">
                            <a href="{{ url('/'.($emailLocale ?? 'en')) }}" target="_blank" style="text-decoration:none;">
                                <img src="{{ $logoUrl }}" width="168" alt="FreeCause" style="display:block;border:0;outline:none;width:168px;height:auto;">
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#ffffff;border:1px solid #E8E6E1;border-radius:12px;padding:44px 46px 40px;">

                            @hasSection('preheader')
                                <div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#ffffff;">
                                    @yield('preheader')
                                </div>
                            @endif

                            @hasSection('eyebrow')
                                <p style="margin:0 0 18px;text-align:center;font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:0.16em;text-transform:uppercase;color:#A39A90;">
                                    @yield('eyebrow')
                                </p>
                            @endif

                            @hasSection('heading')
                                <h1 style="margin:0 0 10px;text-align:center;font-family:Georgia,'Times New Roman',serif;font-weight:400;font-size:26px;line-height:1.3;letter-spacing:-0.01em;color:#1E1E1C;">
                                    @yield('heading')
                                </h1>
                            @endif

                            @hasSection('accent')
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 26px;">
                                    <tr><td align="center"><span style="display:inherit;width:44px;height:3px;background:#cc1f1f;border-radius:2px;"></span></td></tr>
                                </table>
                            @endif

                            <div style="font-size:15px;line-height:1.7;color:#4A4842;">
                                @yield('content')
                            </div>

                            @hasSection('action')
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0 4px;">
                                    <tr>
                                        <td align="center">
                                            <a href="@yield('action_url', '#')" target="_blank"
                                                style="display:inline-block;background:#cc1f1f;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 36px;border-radius:6px;letter-spacing:0.01em;">
                                                @yield('action')
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @hasSection('note')
                                <div style="margin-top:30px;background:#FBF3F1;border:1px solid #F0DDD8;border-radius:8px;padding:16px 18px;font-size:13px;line-height:1.65;color:#7E5A53;">
                                    @yield('note')
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:28px 8px 0;">
                            <p style="margin:0 0 6px;font-family:Menlo,Consolas,monospace;font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:#B6AFA5;">
                                FreeCause
                            </p>
                            <p style="margin:0 auto 8px;max-width:420px;font-size:12px;line-height:1.6;color:#A39A90;">
                                @yield('footer', \App\Support\MailText::get('common_footer', [], $emailLocale ?? 'en'))
                            </p>
                            <p style="margin:0;font-size:12px;color:#A39A90;">
                                <a href="{{ url('/'.($emailLocale ?? 'en')) }}" style="color:#8E8A82;text-decoration:none;">www.freecause.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
