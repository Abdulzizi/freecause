<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact form message</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:30px;">
    <div style="max-width:600px;margin:auto;background:#ffffff;padding:30px;border-radius:6px;">
        <h2 style="margin-top:0;">New contact form message</h2>

        <p><strong>Name:</strong> {{ $senderName }}</p>
        <p><strong>Email:</strong> {{ $senderEmail }}</p>

        <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">

        <p style="white-space:pre-wrap;">{{ $message }}</p>

        <p style="margin-top:30px;font-size:13px;color:#777;">
            xPetition – Online Petition Platform
        </p>
    </div>
</body>
</html>
