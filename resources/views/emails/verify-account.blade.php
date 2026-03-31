@php
    $verifyUrl = route('verify.account', [
        'locale' => $locale,
        'token'  => $user->verification_token
    ]);
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Verify your account</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:30px;">
    <div style="max-width:600px;margin:auto;background:#ffffff;padding:30px;border-radius:6px;">
        <h2 style="margin-top:0;">{{ $greeting ?? 'Welcome to FreeCause' }}</h2>

        <p>
            Hi {{ $user->first_name }},
        </p>

        <p>
            Thank you for registering on FreeCause.
        </p>

        <p>
            Please confirm your email address by clicking the button below:
        </p>

        <p style="text-align:center;margin:30px 0;">
            <a href="{{ $verifyUrl }}"
                style="background:#d61f26;color:#ffffff;padding:12px 25px;text-decoration:none;border-radius:4px;display:inline-block;">
                {{ $buttonText ?? 'Verify My Account' }}
            </a>
        </p>

        <p>
            If you did not create this account, you can ignore this email.
        </p>

        <p style="margin-top:30px;font-size:13px;color:#777;">
            {{ $footer ?? 'FreeCause – Online Petition Platform' }}
        </p>
    </div>
</body>

</html>
