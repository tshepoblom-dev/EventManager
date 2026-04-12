<?php

use App\Http\Controllers\Admin\AttendeeController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\FormResponseController;
use App\Http\Controllers\NetworkingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\Speaker\SessionController as SpeakerSessionController;
use App\Http\Controllers\Sponsor\LeadController as SponsorLeadController;
use App\Http\Controllers\Staff\CheckInController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'));

// Public programme (read-only, no auth)
Route::get('/events/{event}/programme', [ProgrammeController::class, 'index'])->name('programme.index');

// Public form (anonymous or auth)
Route::get('/forms/{form}',  [FormResponseController::class, 'show'])->name('forms.show');
Route::post('/forms/{form}', [FormResponseController::class, 'store'])->name('forms.store');

// ── Authenticated ────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Programme feedback (auth required to prevent spam)
    Route::post('/events/{event}/sessions/{session}/feedback',
        [ProgrammeController::class, 'submitFeedback'])->name('programme.feedback');

    // Networking
    Route::prefix('events/{event}/networking')->name('networking.')->group(function () {
        Route::get('/',                           [NetworkingController::class, 'index'])->name('index');
        Route::post('/connect',                   [NetworkingController::class, 'connect'])->name('connect');
        Route::get('/profile/{attendee}',         [NetworkingController::class, 'profile'])->name('profile');
    });

    // ── Admin ──────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        Route::resource('events', EventController::class);
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Attendees
        Route::prefix('events/{event}/attendees')->name('events.attendees.')->group(function () {
            Route::get('/',                      [AttendeeController::class, 'index'])->name('index');
            Route::get('/create',                [AttendeeController::class, 'create'])->name('create');
            Route::post('/',                     [AttendeeController::class, 'store'])->name('store');
            Route::get('/import/csv',            [AttendeeController::class, 'importForm'])->name('import');
            Route::post('/import/csv',           [AttendeeController::class, 'import'])->name('import.store');
            Route::post('/send-qr-bulk',         [AttendeeController::class, 'sendQrBulk'])->name('send-qr-bulk');
            Route::get('/{attendee}',            [AttendeeController::class, 'show'])->name('show');
            Route::delete('/{attendee}',         [AttendeeController::class, 'destroy'])->name('destroy');
            Route::post('/{attendee}/send-qr',   [AttendeeController::class, 'sendQr'])->name('send-qr');
        });

        // Sessions (Phase 4)
        Route::prefix('events/{event}/sessions')->name('events.sessions.')->group(function () {
            Route::get('/',            [SessionController::class, 'index'])->name('index');
            Route::get('/create',      [SessionController::class, 'create'])->name('create');
            Route::post('/',           [SessionController::class, 'store'])->name('store');
            Route::get('/{session}/edit',    [SessionController::class, 'edit'])->name('edit');
            Route::patch('/{session}',       [SessionController::class, 'update'])->name('update');
            Route::delete('/{session}',      [SessionController::class, 'destroy'])->name('destroy');
        });

        // Forms (Phase 3)
        Route::prefix('events/{event}/forms')->name('events.forms.')->group(function () {
            Route::get('/',             [FormController::class, 'index'])->name('index');
            Route::get('/create',       [FormController::class, 'create'])->name('create');
            Route::post('/',            [FormController::class, 'store'])->name('store');
            Route::get('/{form}/edit',  [FormController::class, 'edit'])->name('edit');
            Route::patch('/{form}',     [FormController::class, 'update'])->name('update');
            Route::get('/{form}',       [FormController::class, 'show'])->name('show');
            Route::delete('/{form}',    [FormController::class, 'destroy'])->name('destroy');
            // Field management (AJAX)
            Route::post('/{form}/fields',              [FormController::class, 'storeField'])->name('fields.store');
            Route::delete('/{form}/fields/{field}',    [FormController::class, 'destroyField'])->name('fields.destroy');
            Route::post('/{form}/fields/reorder',      [FormController::class, 'reorderFields'])->name('fields.reorder');
        });

        // Sponsors (Phase 6)
        Route::prefix('events/{event}/sponsors')->name('events.sponsors.')->group(function () {
            Route::get('/',             [SponsorController::class, 'index'])->name('index');
            Route::get('/create',       [SponsorController::class, 'create'])->name('create');
            Route::post('/',            [SponsorController::class, 'store'])->name('store');
            Route::get('/{sponsor}/edit',   [SponsorController::class, 'edit'])->name('edit');
            Route::patch('/{sponsor}',      [SponsorController::class, 'update'])->name('update');
            Route::delete('/{sponsor}',     [SponsorController::class, 'destroy'])->name('destroy');
        });

        // Leads admin view (Phase 6)
        Route::prefix('events/{event}/leads')->name('events.leads.')->group(function () {
            Route::get('/',                         [LeadController::class, 'index'])->name('index');
            Route::get('/export',                   [LeadController::class, 'export'])->name('export');
            Route::patch('/{lead}/stage',           [LeadController::class, 'updateStage'])->name('stage');
        });

        // Certificates (Phase 7)
        Route::prefix('events/{event}/certificates')->name('events.certificates.')->group(function () {
            Route::get('/',                             [CertificateController::class, 'index'])->name('index');
            Route::post('/generate-bulk',              [CertificateController::class, 'generateBulk'])->name('bulk');
            Route::get('/{attendee}/download',         [CertificateController::class, 'download'])->name('download');
            Route::post('/{attendee}/email',           [CertificateController::class, 'emailCertificate'])->name('email');
        });
    });

    // ── Staff ──────────────────────────────────────────────────────────
    Route::middleware('role:admin,staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('events/{event}/checkin',             [CheckInController::class, 'index'])->name('checkin.index');
        Route::post('events/{event}/checkin/scan',       [CheckInController::class, 'scanQr'])->name('checkin.scan');
        Route::get('events/{event}/checkin/search',      [CheckInController::class, 'searchCheckIn'])->name('checkin.search');
        Route::post('events/{event}/checkin/{attendee}', [CheckInController::class, 'checkInById'])->name('checkin.manual');
    });

    // ── Sponsor ────────────────────────────────────────────────────────
    Route::middleware('role:sponsor')->prefix('sponsor')->name('sponsor.')->group(function () {
        Route::get('events/{event}/dashboard',        [SponsorLeadController::class, 'dashboard'])->name('dashboard');
        Route::get('events/{event}/leads/create',     [SponsorLeadController::class, 'create'])->name('leads.create');
        Route::post('events/{event}/leads',           [SponsorLeadController::class, 'store'])->name('leads.store');
        Route::patch('events/{event}/leads/{lead}/stage', [SponsorLeadController::class, 'updateStage'])->name('leads.stage');
        Route::patch('events/{event}/leads/{lead}/note',  [SponsorLeadController::class, 'addNote'])->name('leads.note');
        Route::get('events/{event}/leads/export',    [SponsorLeadController::class, 'export'])->name('leads.export');
    });

    // ── Speaker ────────────────────────────────────────────────────────
    Route::middleware('role:speaker')->prefix('speaker')->name('speaker.')->group(function () {
        Route::get('events/{event}/sessions',          [SpeakerSessionController::class, 'index'])->name('sessions.index');
        Route::get('events/{event}/sessions/{session}',[SpeakerSessionController::class, 'show'])->name('sessions.show');
    });
});

require __DIR__ . '/auth.php';
