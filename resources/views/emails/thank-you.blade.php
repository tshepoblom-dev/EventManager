<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { margin:0;padding:0;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; }
  .wrap { max-width:540px;margin:32px auto;padding:0 16px 32px; }
  .card { background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08); }
  .hdr  { background:#059669;padding:32px;text-align:center; }
  .hdr h1 { color:#fff;font-size:22px;font-weight:600;margin:8px 0 4px; }
  .hdr p  { color:#a7f3d0;font-size:13px;margin:0; }
  .body { padding:32px; }
  .foot { text-align:center;padding:20px 0 0;font-size:11px;color:#9ca3af; }
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="hdr">
    <div style="font-size:32px;margin-bottom:8px;">🎉</div>
    <h1>Thank you!</h1>
    <p>{{ $attendee->event->name }}</p>
  </div>
  <div class="body">
    <p style="font-size:16px;font-weight:600;color:#111827;margin:0 0 12px;">Hi {{ $attendee->first_name }},</p>
    <p style="font-size:14px;color:#4b5563;line-height:1.7;margin:0 0 16px;">
      Thank you for attending <strong>{{ $attendee->event->name }}</strong> on
      {{ $attendee->event->event_date->format('d F Y') }}.
      We hope you found it valuable and made some great connections.
    </p>
    <p style="font-size:14px;color:#4b5563;line-height:1.7;margin:0 0 24px;">
      Your certificate of attendance will be emailed to you shortly.
      We look forward to seeing you at future events!
    </p>
    <div style="background:#f9fafb;border-radius:12px;padding:16px;text-align:center;">
      <p style="font-size:13px;color:#6b7280;margin:0 0 4px;">Your attendance is recorded</p>
      <p style="font-size:15px;font-weight:600;color:#111827;margin:0;">{{ $attendee->full_name }}</p>
      @if($attendee->company)<p style="font-size:13px;color:#6b7280;margin:4px 0 0;">{{ $attendee->company }}</p>@endif
    </div>
  </div>
</div>
<div class="foot"><p>{{ $attendee->event->name }} · Thank you for attending</p></div>
</div>
</body>
</html>
