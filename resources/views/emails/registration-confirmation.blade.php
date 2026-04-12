<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { margin:0; padding:0; background:#f4f5f7; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; }
  .wrap { max-width:540px; margin:32px auto; padding:0 16px 32px; }
  .card { background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
  .hdr  { background:#1a56db; padding:32px; text-align:center; }
  .hdr h1 { color:#fff; font-size:20px; font-weight:600; margin:8px 0 4px; }
  .hdr p  { color:#93c5fd; font-size:13px; margin:0; }
  .body { padding:32px; }
  .row  { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px; }
  .row:last-child { border-bottom:none; }
  .lbl  { color:#6b7280; }
  .val  { color:#111827; font-weight:500; }
  .cta  { display:block; margin:24px 0 0; padding:14px; background:#1a56db; color:#fff; text-align:center; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; }
  .foot { text-align:center; padding:20px 0 0; font-size:11px; color:#9ca3af; }
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="hdr">
    <div style="font-size:28px;margin-bottom:8px;">✅</div>
    <h1>You're registered!</h1>
    <p>{{ $attendee->event->name }}</p>
  </div>
  <div class="body">
    <p style="font-size:16px;font-weight:600;color:#111827;margin:0 0 8px;">Hi {{ $attendee->first_name }},</p>
    <p style="font-size:14px;color:#4b5563;line-height:1.6;margin:0 0 24px;">
      Your registration is confirmed. We're looking forward to seeing you!
      Your QR check-in code will be sent separately closer to the event.
    </p>
    <div>
      <div class="row"><span class="lbl">Event</span><span class="val">{{ $attendee->event->name }}</span></div>
      <div class="row"><span class="lbl">Date</span><span class="val">{{ $attendee->event->event_date->format('l, d F Y') }}</span></div>
      <div class="row"><span class="lbl">Time</span><span class="val">{{ \Carbon\Carbon::parse($attendee->event->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($attendee->event->end_time)->format('H:i') }}</span></div>
      @if($attendee->event->venue)
      <div class="row"><span class="lbl">Venue</span><span class="val">{{ $attendee->event->venue }}</span></div>
      @endif
      <div class="row"><span class="lbl">Ticket</span><span class="val" style="text-transform:capitalize;">{{ $attendee->ticket_type }}</span></div>
    </div>
  </div>
</div>
<div class="foot"><p>{{ $attendee->event->name }} · Registration confirmation</p></div>
</div>
</body>
</html>
