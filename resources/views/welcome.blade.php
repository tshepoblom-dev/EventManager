<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heidedal Scale Up — Event Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --gold:   #C9A84C;
            --gold-lt:#E8D5A3;
            --ink:    #0F0E0C;
            --ink-2:  #1C1A16;
            --ink-3:  #2A2720;
            --mist:   #F5F2EC;
            --mist-2: #EDE9E0;
            --ash:    #8A8478;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--ink);
            color: var(--mist);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ─────────────────────────────────────────── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2.5rem;
            border-bottom: 1px solid rgba(201,168,76,0.15);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .logo-mark {
            width: 34px;
            height: 34px;
            border: 1.5px solid var(--gold);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-mark svg { width: 16px; height: 16px; fill: var(--gold); }
        .logo-name {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--mist);
        }
        .login-link {
            font-size: 0.8rem;
            letter-spacing: 0.06em;
            color: var(--ash);
            text-decoration: none;
            transition: color 0.2s;
        }
        .login-link:hover { color: var(--gold); }

        /* ── Hero ────────────────────────────────────────────── */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4rem 2rem 2rem;
            text-align: center;
        }
        .eyebrow {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .eyebrow::before,
        .eyebrow::after {
            content: '';
            display: block;
            width: 32px;
            height: 1px;
            background: var(--gold);
            opacity: 0.6;
        }
        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.8rem, 6vw, 4.5rem);
            font-weight: 400;
            line-height: 1.1;
            letter-spacing: -0.01em;
            color: var(--mist);
            max-width: 640px;
            margin-bottom: 1rem;
        }
        h1 em {
            font-style: italic;
            color: var(--gold-lt);
        }
        .hero-sub {
            font-size: 0.95rem;
            font-weight: 300;
            color: var(--ash);
            max-width: 440px;
            line-height: 1.7;
            margin-bottom: 3.5rem;
        }

        /* ── Role cards ──────────────────────────────────────── */
        .roles-label {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--ash);
            margin-bottom: 1.25rem;
        }
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            width: 100%;
            max-width: 860px;
        }
        .role-card {
            background: var(--ink-2);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 10px;
            padding: 1.5rem 1.25rem 1.25rem;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: border-color 0.2s, background 0.2s, transform 0.15s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .role-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(201,168,76,0.05) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.25s;
        }
        .role-card:hover {
            border-color: rgba(201,168,76,0.35);
            background: var(--ink-3);
            transform: translateY(-2px);
        }
        .role-card:hover::before { opacity: 1; }

        .role-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .role-icon svg { width: 18px; height: 18px; }

        .role-icon.admin   { background: rgba(201,168,76,0.12); }
        .role-icon.admin svg   { stroke: var(--gold); }
        .role-icon.staff   { background: rgba(56,189,148,0.12); }
        .role-icon.staff svg   { stroke: #38BD94; }
        .role-icon.speaker { background: rgba(130,120,210,0.12); }
        .role-icon.speaker svg { stroke: #8278D2; }
        .role-icon.sponsor { background: rgba(64,169,224,0.12); }
        .role-icon.sponsor svg { stroke: #40A9E0; }
        .role-icon.attendee { background: rgba(234,120,90,0.12); }
        .role-icon.attendee svg { stroke: #EA785A; }

        .role-title {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--mist);
            letter-spacing: 0.01em;
        }
        .role-desc {
            font-size: 0.78rem;
            font-weight: 300;
            color: var(--ash);
            line-height: 1.5;
        }
        .role-arrow {
            margin-top: auto;
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(138,132,120,0.6);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s;
        }
        .role-card:hover .role-arrow { color: var(--gold); }
        .role-arrow svg { width: 12px; height: 12px; stroke: currentColor; transition: transform 0.2s; }
        .role-card:hover .role-arrow svg { transform: translateX(3px); }

        /* ── Footer ──────────────────────────────────────────── */
        .footer {
            padding: 1.5rem 2.5rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .footer p {
            font-size: 0.72rem;
            color: rgba(138,132,120,0.5);
            letter-spacing: 0.04em;
        }
        .footer a {
            font-size: 0.72rem;
            color: rgba(138,132,120,0.5);
            text-decoration: none;
            letter-spacing: 0.04em;
        }
        .footer a:hover { color: var(--gold); }

        /* ── Auth'd user banner ──────────────────────────────── */
        .auth-banner {
            background: rgba(201,168,76,0.08);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-size: 0.82rem;
            color: var(--gold-lt);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            max-width: 860px;
            width: 100%;
            margin-bottom: 1.5rem;
        }
        .auth-banner svg { width: 16px; height: 16px; stroke: var(--gold); flex-shrink: 0; }
        .auth-banner a { color: var(--gold); text-decoration: underline; text-underline-offset: 2px; }

        @media (max-width: 600px) {
            .topbar { padding: 1rem 1.25rem; }
            .hero   { padding: 2.5rem 1.25rem 1.5rem; }
            .footer { padding: 1.25rem; flex-direction: column; gap: 0.5rem; text-align: center; }
        }

        .hero-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .hero-logo {
            width: 80px;
            height: auto;
            object-fit: contain;
        }

        .event-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            font-weight: 400;
            line-height: 1.2;
            text-align: center;
            color: var(--mist);
        }

        .event-title em {
            font-style: italic;
            color: var(--gold-lt);
        }

        /* Mobile tweaks */
        @media (max-width: 600px) {
            .hero-logo {
                width: 60px;
            }

            .event-title {
                font-size: 2rem;
            }
        }

        .hero-logo {
            animation: fadeIn 0.8s ease-in-out;
        }

        .event-title {
            animation: fadeIn 1.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-logo {
            animation: fadeIn 0.8s ease-in-out;
        }

        .event-title {
            animation: fadeIn 1.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    {{-- ── Top bar ────────────────────────────────────────────────── --}}
    <header class="topbar">
        <a href="/" class="logo">
            <div class="logo-mark">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
            </div>
            <span class="logo-name">Heidedal Scale Up</span>
        </a>
        <a href="{{ route('login') }}" class="login-link">Sign in →</a>
    </header>

    {{-- ── Hero ─────────────────────────────────────────────────────── --}}
    <main class="hero">
        <div class="hero-brand">
            <img src="/images/logo.png" alt="Event Logo" class="hero-logo">
            <h1 class="event-title">
                Heidedal Scale Up<br>
                <em>Entrepreneur’s Day</em>
            </h1>
        </div>
{{-- 
        <p class="eyebrow">Event Management Platform</p>

        <h1>Every role,<br><em>one platform</em></h1>

        <p class="hero-sub">
            Manage events from setup to sign-off — check-ins, sessions, sponsors,
            and attendee connections all in one place.
        </p>
 --}}
        {{-- Banner for already-authenticated users --}}
        @auth
        <div class="auth-banner">
            <svg fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4l3 3" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span>
                You're signed in as <strong>{{ Auth::user()->name }}</strong>.
                <a href="{{ route('dashboard') }}">Go to your dashboard →</a>
            </span>
        </div>
        @endauth

        <p class="roles-label">Sign in as</p>

        {{-- ── Role cards ────────────────────────────────────────────── --}}
        <div class="roles-grid">

            {{-- Admin --}}
            <a href="{{ route('login') }}" class="role-card">
                <div class="role-icon admin">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <div class="role-title">Administrator</div>
                <div class="role-desc">Full event control — create events, manage attendees, view analytics</div>
                <div class="role-arrow">
                    Sign in
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- Staff --}}
            <a href="{{ route('login') }}" class="role-card">
                <div class="role-icon staff">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="role-title">Event Staff</div>
                <div class="role-desc">QR scanner, manual check-in, and live door counts</div>
                <div class="role-arrow">
                    Sign in
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- Speaker --}}
            <a href="{{ route('login') }}" class="role-card">
                <div class="role-icon speaker">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                    </svg>
                </div>
                <div class="role-title">Speaker</div>
                <div class="role-desc">Your session schedule, audience Q&A, and feedback ratings</div>
                <div class="role-arrow">
                    Sign in
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- Sponsor --}}
            <a href="{{ route('login') }}" class="role-card">
                <div class="role-icon sponsor">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/>
                    </svg>
                </div>
                <div class="role-title">Sponsor</div>
                <div class="role-desc">Lead scanning, pipeline tracking, and booth insights</div>
                <div class="role-arrow">
                    Sign in
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- Attendee --}}
            <a href="{{ route('login') }}" class="role-card">
                <div class="role-icon attendee">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>
                <div class="role-title">Attendee</div>
                <div class="role-desc">Your QR ticket, session programme, and networking</div>
                <div class="role-arrow">
                    Sign in
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

        </div>{{-- /roles-grid --}}

    </main>

    {{-- ── Footer ───────────────────────────────────────────────────── --}}
    <footer class="footer">
        <p>&copy; {{ date('Y') }} Heidedal Scale Up Day &mdash; All rights reserved</p>
        <a href="mailto:admin@heidedal.co.za">admin@heidedal.co.za</a>
    </footer>

</body>
</html>
