<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your QR Check-in Code</title>
<style>
  body      { margin:0; padding:0; background:#f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; }
  .wrapper  { max-width:540px; margin:32px auto; padding:0 16px 32px; }
  .card     { background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
  .header   { background:#1a56db; padding:32px 32px 24px; text-align:center; }
  .header h1{ color:#fff; font-size:20px; font-weight:600; margin:8px 0 4px; }
  .header p { color:#93c5fd; font-size:13px; margin:0; }
  .body     { padding:32px; }
  .greeting { font-size:16px; color:#111827; font-weight:600; margin:0 0 8px; }
  .intro    { font-size:14px; color:#4b5563; line-height:1.6; margin:0 0 24px; }
  .qr-box   { background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:24px; text-align:center; margin-bottom:24px; }
  .qr-box img { width:160px; height:160px; display:block; margin:0 auto 12px; }
  .qr-note  { font-size:12px; color:#9ca3af; margin:0; }
  .detail-row{ display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
  .detail-row:last-child{ border-bottom:none; }
  .detail-label{ color:#6b7280; }
  .detail-val  { color:#111827; font-weight:500; }
  .cta      { display:block; width:100%; margin:24px 0 0; padding:14px; background:#1a56db; color:#fff; text-align:center; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; }
  .footer   { text-align:center; padding:20px 0 0; font-size:11px; color:#9ca3af; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">

    {{-- Header --}}
    <div class="header">
      @if($attendee->event->logo)
        <img src="{{ Storage::disk('public')->url($attendee->event->logo) }}"
             alt="{{ $attendee->event->name }}" style="height:40px; margin-bottom:8px;">
      @else
        <div style="width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;">
          <span style="color:#fff;font-size:20px;">⚡</span>
        </div>
      @endif
      <h1>{{ $attendee->event->name }}</h1>
      <p>
        {{ $attendee->event->event_date->format('l, d F Y') }}
        @if($attendee->event->venue) · {{ $attendee->event->venue }}@endif
      </p>
    </div>

    {{-- Body --}}
    <div class="body">
      <p class="greeting">Hi {{ $attendee->first_name }}!</p>
      <p class="intro">
        You're registered for <strong>{{ $attendee->event->name }}</strong>.
        Your personal QR code is attached to this email and shown below.
        Please have it ready on your phone when you arrive — staff will scan it for instant check-in.
      </p>

      {{-- QR image (inline fallback — actual file is attached) --}}
      @if($attendee->qr_image_path)
      <div class="qr-box">
        <img src="{{ Storage::disk('public')->url($attendee->qr_image_path) }}"
             alt="Your QR code">
        <p class="qr-note">Your check-in QR code · also attached as qr-code.png</p>
      </div>
      @endif

      {{-- Event details --}}
      <div style="margin-bottom:24px;">
        <div class="detail-row">
          <span class="detail-label">Name</span>
          <span class="detail-val">{{ $attendee->full_name }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Ticket type</span>
          <span class="detail-val" style="text-transform:capitalize;">{{ $attendee->ticket_type }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date</span>
          <span class="detail-val">{{ $attendee->event->event_date->format('d F Y') }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Time</span>
          <span class="detail-val">
            {{ \Carbon\Carbon::parse($attendee->event->start_time)->format('H:i') }}
            – {{ \Carbon\Carbon::parse($attendee->event->end_time)->format('H:i') }}
          </span>
        </div>
        @if($attendee->event->venue)
        <div class="detail-row">
          <span class="detail-label">Venue</span>
          <span class="detail-val">{{ $attendee->event->venue }}</span>
        </div>
        @endif
      </div>

      <p style="font-size:13px;color:#6b7280;margin:0 0 8px;">
        <strong style="color:#111827;">Can't find your QR?</strong>
        Simply tell staff your name or email at the check-in desk — they can look you up manually.
      </p>
    </div>
  </div>

  <div class="footer">
    <p>This email was sent by the event organisers for {{ $attendee->event->name }}.</p>
    <p>If you did not register for this event, please ignore this email.</p>
  </div>
</div>
</body>
</html>
