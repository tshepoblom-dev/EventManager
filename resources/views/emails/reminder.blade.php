<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { margin:0;padding:0;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; }
  .wrap { max-width:540px;margin:32px auto;padding:0 16px 32px; }
  .card { background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08); }
  .hdr  { background:#1a56db;padding:32px;text-align:center; }
  .hdr h1 { color:#fff;font-size:20px;font-weight:600;margin:8px 0 4px; }
  .hdr p  { color:#93c5fd;font-size:13px;margin:0; }
  .body { padding:32px; }
  .row  { display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px; }
  .row:last-child { border-bottom:none; }
  .foot { text-align:center;padding:20px 0 0;font-size:11px;color:#9ca3af; }
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="hdr">
    <div style="font-size:28px;margin-bottom:8px;">⏰</div>
    <h1>See you tomorrow!</h1>
    <p>{{ $attendee->event->name }}</p>
  </div>
  <div class="body">
    <p style="font-size:16px;font-weight:600;color:#111827;margin:0 0 8px;">Hi {{ $attendee->first_name }},</p>
    <p style="font-size:14px;color:#4b5563;line-height:1.6;margin:0 0 24px;">
      Just a reminder that <strong>{{ $attendee->event->name }}</strong> is tomorrow.
      Don't forget to bring your QR code for quick check-in!
    </p>
    <div>
      <div class="row"><span style="color:#6b7280;">Date</span><span style="color:#111827;font-weight:500;">{{ $attendee->event->event_date->format('l, d F Y') }}</span></div>
      <div class="row"><span style="color:#6b7280;">Time</span><span style="color:#111827;font-weight:500;">{{ \Carbon\Carbon::parse($attendee->event->start_time)->format('H:i') }}</span></div>
      @if($attendee->event->venue)
      <div class="row"><span style="color:#6b7280;">Venue</span><span style="color:#111827;font-weight:500;">{{ $attendee->event->venue }}</span></div>
      @endif
    </div>
  </div>
</div>
<div class="foot"><p>{{ $attendee->event->name }} · Event reminder</p></div>
</div>
</body>
</html>
