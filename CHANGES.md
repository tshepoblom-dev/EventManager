# CHANGES.md

## Session 5 — User Accounts, Invites & Speaker Management

### Overview
Three major features added on top of the Session 4 Programme Builder:

1. **Attendee → User Account invite system** (admin & staff can invite)
2. **Role selection on public registration + full admin user management**
3. **Dedicated Speaker model** with admin CRUD + assignment in the session builder

---

### New migrations

#### `2026_04_13_000001_add_invite_and_speakers.php`
- Adds `invite_token` (unique, 64 chars) and `invite_sent_at` to `attendees`
- Creates the `speakers` table (see below)
- Adds `speaker_id` FK to `session_speakers` pointing at `speakers.id`

---

### New models

#### `app/Models/Speaker.php`
Decoupled speaker profile — not tied to a user account. Columns:
- `user_id` (nullable FK → `users`) — set when the speaker has a platform login
- `attendee_id` (nullable FK → `attendees`) — set when the speaker is also a registered attendee
- `name`, `email`, `title`, `bio`, `photo`, `linkedin`, `twitter`

Helper methods: `syncFromAttendee()`, `syncFromUser()`, `display_name` accessor.

---

### Modified models

#### `app/Models/Attendee.php`
New fillable fields: `invite_token`, `invite_sent_at`
New relation: `speaker()` HasOne
New helpers:
- `hasAccount()` — true if `user_id` is set
- `hasPendingInvite()` — token present but no account yet
- `generateInviteToken()` — generates 64-char hex token, saves timestamp
- `clearInviteToken()` — nulls the token after account creation

#### `app/Models/User.php`
- Removed the old `sessions()` BelongsToMany (speakers are now via the `Speaker` model)
- Added `attendees()` HasMany
- Added `speaker()` HasOne

#### `app/Models/Session.php`
- `speakers()` relation now uses `Speaker` model via `session_speakers.speaker_id`
  (was `User` via `session_speakers.user_id`)
- `user_id` remains on the pivot for backward compatibility

---

### New controllers

#### `app/Http/Controllers/Admin/UserController.php`
- `index()` — paginated user list with name/email search and role filter
- `create()` / `store()` — admin creates a user with any role incl. admin/staff
- `edit()` / `update()` — change name, email, role
- `destroy()` — unlinks attendees, deletes user
- `inviteAttendee(Attendee)` — generates token, queues `AttendeeInviteMail`
- `inviteAttendeeBulk(Request)` — batch invite, skips those with accounts

#### `app/Http/Controllers/Admin/SpeakerController.php`
- `index()` — card grid with attendee/user link badges
- `create()` / `store()` — create speaker, optionally link to attendee and/or user
- `edit()` / `update()` — update profile, swap links, replace photo
- `destroy()` — deletes storage photo, removes speaker record
- `list()` — JSON endpoint (`GET /admin/speakers/list`) consumed by the session builder

---

### Modified controllers

#### `app/Http/Controllers/Auth/RegisteredUserController.php`
- `create()` — now passes `$roles` (excluding admin/staff) for role selector
- `store()` — validates `role_id`, blocks admin/staff self-assignment
- `createFromInvite(token)` — validates token, checks 72h expiry, renders pre-filled form
- `storeFromInvite(request, token)` — creates user, links to attendee, clears token

#### `app/Http/Controllers/Admin/SessionController.php`
- `$speakers` query changed from `User::whereHas('role','speaker')` → `Speaker::orderBy('name')`
- `store()` / `update()`: `speaker_ids.*` now validates against `speakers` table
- Pivot sync now uses `speaker_id` key (was `user_id`)
- `sessionPayload()` now returns `title` from `Speaker` (was `company` from `User`)

---

### New mail

#### `app/Mail/AttendeeInviteMail.php`
Queued mailable. Constructor accepts `Attendee $attendee` and `string $inviteUrl`.
View: `resources/views/emails/attendee-invite.blade.php`

---

### New views

| View | Purpose |
|---|---|
| `auth/register.blade.php` | Updated — role radio buttons (attendee/speaker/sponsor) |
| `auth/register-invited.blade.php` | **New** — invite-based registration, email readonly |
| `emails/attendee-invite.blade.php` | **New** — HTML invite email |
| `admin/users/index.blade.php` | **New** — user list with Edit/Delete |
| `admin/users/create.blade.php` | **New** — create user form (all roles available) |
| `admin/users/edit.blade.php` | **New** — edit name/email/role |
| `admin/speakers/index.blade.php` | **New** — speaker card grid |
| `admin/speakers/create.blade.php` | **New** — create speaker form |
| `admin/speakers/edit.blade.php` | **New** — edit speaker form |
| `admin/speakers/_form.blade.php` | **New** — shared speaker form partial |
| `admin/attendees/show.blade.php` | Modified — invite card injected before Danger Zone |

---

### Updated routes

#### `routes/auth.php`
```
GET  /register/invite/{token}   register.invited        (guest)
POST /register/invite/{token}   register.invited.store  (guest)
```

#### `routes/web.php`
```
POST admin/attendees/{attendee}/invite        admin.attendees.invite
POST admin/attendees/invite-bulk              admin.attendees.invite-bulk

GET  admin/speakers/list                      admin.speakers.list    (JSON)
GET  admin/speakers                           admin.speakers.index
GET  admin/speakers/create                    admin.speakers.create
POST admin/speakers                           admin.speakers.store
GET  admin/speakers/{speaker}/edit            admin.speakers.edit
PATCH admin/speakers/{speaker}                admin.speakers.update
DELETE admin/speakers/{speaker}               admin.speakers.destroy

GET  admin/users                              admin.users.index
GET  admin/users/create                       admin.users.create
POST admin/users                              admin.users.store
GET  admin/users/{user}/edit                  admin.users.edit
PATCH admin/users/{user}                      admin.users.update
DELETE admin/users/{user}                     admin.users.destroy
```

---

### Admin layout nav
- Added **Speakers** link (microphone icon) above Events
- Added **Users** link (group icon) above Events

---

### Post-deploy steps
```bash
php artisan migrate
php artisan config:clear
php artisan route:clear
php artisan storage:link   # needed for speaker photo uploads
```

---

### Architecture notes

**Why a separate `Speaker` model instead of just using `User`?**

Previously sessions linked to users with role=speaker. This meant:
- You could only assign speakers who had created accounts
- Creating a "speaker" entry forced creating a dummy user first

The new model separates *speaker profile* (for programme display) from *user account* (for login). A speaker can:
- Have neither (standalone profile, entered manually)
- Have an attendee record only (they're attending but haven't logged in)
- Have a user account only (they have login but weren't imported as attendee)
- Have both (full linkage — can check in, log in, see their sessions in the speaker portal)

The `session_speakers` pivot now carries both `speaker_id` (required for new sessions) and `user_id` (preserved for backward compatibility with pre-migration sessions).
