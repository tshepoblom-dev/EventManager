<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Invitation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 40px 0; color: #18181b; }
        .card { background: #fff; max-width: 560px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #1d4ed8; padding: 32px 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 22px; margin: 0; font-weight: 700; }
        .body { padding: 40px; }
        .body p { line-height: 1.7; margin: 0 0 16px; font-size: 15px; color: #3f3f46; }
        .cta { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #1d4ed8; color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .note { font-size: 13px; color: #71717a; border-top: 1px solid #f4f4f5; padding-top: 20px; margin-top: 24px; }
        .url-fallback { word-break: break-all; color: #1d4ed8; font-size: 13px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>Heidedal Scale Up</h1>
    </div>
    <div class="body">
        <p>Hi <strong>{{ $attendee->first_name }}</strong>,</p>

        <p>You've been registered as an attendee for an upcoming event and you've been invited to create a personal account on the Heidedal Scale Up platform.</p>

        <p>Your account will be pre-configured with your attendee details so you can access your QR code, event programme, networking features, and more.</p>

        <div class="cta">
            <a href="{{ $inviteUrl }}" class="btn">Create My Account →</a>
        </div>

        <p class="note">
            This link will expire in <strong>72 hours</strong>. If you didn't expect this email, you can safely ignore it.<br><br>
            If the button above doesn't work, copy and paste this URL into your browser:<br>
            <span class="url-fallback">{{ $inviteUrl }}</span>
        </p>
    </div>
</div>
</body>
</html>
