<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DejaVu Sans', sans-serif;
    width: 297mm;
    height: 210mm;
    background: #ffffff;
    color: #1a1a2e;
  }

  .page {
    width: 297mm;
    height: 210mm;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20mm 25mm;
  }

  /* Decorative border */
  .border-outer {
    position: absolute;
    top: 8mm;
    left: 8mm;
    right: 8mm;
    bottom: 8mm;
    border: 3px solid #1a56db;
    border-radius: 4mm;
  }
  .border-inner {
    position: absolute;
    top: 11mm;
    left: 11mm;
    right: 11mm;
    bottom: 11mm;
    border: 1px solid #93c5fd;
    border-radius: 3mm;
  }

  /* Corner ornaments */
  .corner {
    position: absolute;
    width: 15mm;
    height: 15mm;
    border-color: #1a56db;
    border-style: solid;
  }
  .corner-tl { top: 6mm; left: 6mm; border-width: 3px 0 0 3px; border-radius: 2mm 0 0 0; }
  .corner-tr { top: 6mm; right: 6mm; border-width: 3px 3px 0 0; border-radius: 0 2mm 0 0; }
  .corner-bl { bottom: 6mm; left: 6mm; border-width: 0 0 3px 3px; border-radius: 0 0 0 2mm; }
  .corner-br { bottom: 6mm; right: 6mm; border-width: 0 3px 3px 0; border-radius: 0 0 2mm 0; }

  .content {
    position: relative;
    z-index: 1;
    text-align: center;
    width: 100%;
  }

  .event-name {
    font-size: 11pt;
    color: #1a56db;
    font-weight: bold;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 6mm;
  }

  .cert-title {
    font-size: 28pt;
    font-weight: bold;
    color: #1a1a2e;
    letter-spacing: 1px;
    margin-bottom: 5mm;
  }

  .presented-to {
    font-size: 10pt;
    color: #6b7280;
    margin-bottom: 4mm;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .recipient-name {
    font-size: 26pt;
    color: #1a56db;
    font-weight: bold;
    margin-bottom: 4mm;
    border-bottom: 1.5px solid #93c5fd;
    padding-bottom: 3mm;
    display: inline-block;
    min-width: 120mm;
  }

  .company {
    font-size: 11pt;
    color: #4b5563;
    margin-bottom: 6mm;
  }

  .description {
    font-size: 10pt;
    color: #6b7280;
    line-height: 1.6;
    max-width: 180mm;
    margin: 0 auto 8mm;
  }

  .footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    width: 100%;
    margin-top: 4mm;
  }

  .footer-block {
    text-align: center;
    min-width: 50mm;
  }

  .footer-line {
    border-top: 1px solid #1a1a2e;
    margin-bottom: 2mm;
    width: 45mm;
    margin-left: auto;
    margin-right: auto;
  }

  .footer-label {
    font-size: 8pt;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .footer-value {
    font-size: 9pt;
    color: #1a1a2e;
    font-weight: bold;
  }

  .seal {
    width: 22mm;
    height: 22mm;
    border: 3px solid #1a56db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 8pt;
    color: #1a56db;
    font-weight: bold;
    text-align: center;
    line-height: 1.2;
  }
</style>
</head>
<body>
<div class="page">

  <!-- Decorative borders & corners -->
  <div class="border-outer"></div>
  <div class="border-inner"></div>
  <div class="corner corner-tl"></div>
  <div class="corner corner-tr"></div>
  <div class="corner corner-bl"></div>
  <div class="corner corner-br"></div>

  <div class="content">

    <p class="event-name">{{ strtoupper($attendee->event->name) }}</p>

    <h1 class="cert-title">Certificate of Attendance</h1>

    <p class="presented-to">This certifies that</p>

    <p class="recipient-name">{{ $attendee->full_name }}</p>

    @if($attendee->company)
    <p class="company">{{ $attendee->job_title ? $attendee->job_title . ' · ' : '' }}{{ $attendee->company }}</p>
    @endif

    <p class="description">
      attended and participated in <strong>{{ $attendee->event->name }}</strong>
      held on {{ $attendee->event->event_date->format('d F Y') }}
      @if($attendee->event->venue)at {{ $attendee->event->venue }}@endif.
    </p>

    <div class="footer">
      <div class="footer-block">
        <div class="footer-line"></div>
        <p class="footer-label">Date</p>
        <p class="footer-value">{{ $attendee->event->event_date->format('d F Y') }}</p>
      </div>

      <div class="footer-block">
        <div class="seal">
          HEIDEDAL<br>SCALE UP
        </div>
      </div>

      <div class="footer-block">
        <div class="footer-line"></div>
        <p class="footer-label">Authorised by</p>
        <p class="footer-value">Event Organiser</p>
      </div>
    </div>

  </div>
</div>
</body>
</html>
