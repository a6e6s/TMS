# TMS Implementation Todo

Short checklist derived from [Plan.md](./Plan.md). The first milestone is a manual, single-tenant ticketing system in Filament (Phases 1 and 2). Phases 3 and 4 are deferred and start only after the MVP passes its exit criteria.

## Phase 1: Foundation & Manual Ticketing Core

- [x] Create migrations: `settings`, `users` (add `reports_to_id`, `role`), `tickets`, `comments`, `ticket_activities`
- [x] Add the `settings` model and seed defaults for the MVP keys (retention, reopen and auto-close window)
- [x] Store attachments on the local private disk (`FILESYSTEM_DISK=local`), not publicly reachable
- [x] Set up `en` and `ar` locales (`lang/en`, `lang/ar`) using Laravel's built-in translation only
- [x] Add the session-based language switcher (`POST /admin/language`, `filament_locale` session key, `SetFilamentLocale` middleware) in the user menu and on the login page
- [x] Enable RTL layout for `ar`
- [x] Verify exit criteria: both locales render, switcher persists across pages, all text stored as UTF-8

## Phase 2: Filament Admin & Workflow

- [x] Build Filament resources: `TicketResource` (filter tabs: All, Unassigned, Open, In Progress, Resolved), `UserResource`
- [x] Build the Settings page for the MVP `settings` values
- [x] Add manual priority, assignment, and status transitions (write `ticket_activities`)
- [x] Add internal and public comments (internal notes hidden from clients)
- [x] Implement role checks for `Super Admin`, `Team Lead`, and `Support Agent` with Filament policies (`role` column; revisit Spatie in Phase 3)
- [x] Verify exit criteria: role-based visibility is tested, every status change, assignment, and priority change writes an activity row

## Phase 3 (Deferred): Production API & PMS Bridge

Start only after Phase 2 passes and Sanctum and Spatie installation is approved.

- [ ] Install `laravel/sanctum` and `spatie/laravel-permission` (approval required first)
- [ ] Build `POST /api/v1/tickets` with Sanctum auth, `Idempotency-Key`, and payload limits (5 MB, 5 screenshots at 2 MB)
- [ ] Create migrations: `webhook_payloads`, `project_syncs`
- [ ] Scrub PII and secrets before saving `webhook_payloads`
- [ ] Decode screenshots in `DecodeScreenshotsJob`
- [ ] Build `SourceAppResource` for connected apps and their tokens
- [ ] Choose the first PMS provider (open decision in Plan.md Section 6)
- [ ] Implement `PmsConnectorInterface` and the provider connector with status mapping
- [ ] Build `PushTicketToPmsJob` (5 tries, backoff: 1 min, 5 min, 30 min, 2 h, 12 h) and the **Push to PMS** action (guarded so a double click creates one `project_syncs` row)
- [ ] Build the inbound PMS webhook `POST /api/v1/webhooks/pms/{provider}` with HMAC signature, 5-minute replay window, and `event_id` idempotency
- [ ] Add client endpoints: `GET /api/v1/tickets/{ticket_number}` and `POST /api/v1/tickets/{ticket_number}/comments` (`X-Tracking-Token`)
- [ ] Build the client portal (optional Filament/Blade)
- [ ] Verify exit criteria: stale webhooks rejected, replayed events don't change state twice, all statuses mapped, failed syncs retry and show in Filament

## Phase 4 (Deferred): AI Triage Engine

Start only after Phase 3 passes.

- [ ] Add `ai_priority_score` and `ai_analysis` columns
- [ ] Build `AiTriageService` as an LLM queue job
- [ ] Add duplicate detection (24-hour fingerprint window, `duplicate_of_id`)
- [ ] Add auto-escalation settings (`auto_escalate.enabled`, `auto_escalate.score` = 85, `auto_escalate.shadow_mode` = true) with a kill switch
- [ ] Build `AiAccuracyChart` and review shadow-mode agreement before enabling auto-escalation
- [ ] Verify exit criteria: duplicates detected, kill switch stops auto-push within one job cycle

## Cross-Cutting (MVP)

- [ ] Tests: ticket CRUD, status transitions, role policies, activity log, internal versus public comment visibility, attachment privacy, localization (`lang/en` and `lang/ar` key parity)
- [ ] Structured JSON logs with `ticket_number` and `request_id`
- [ ] Migrations each have a tested rollback

## Cross-Cutting (Deferred to Phase 3 and 4)

- [ ] Rate limiters: `ticket-ingestion` per source-app token, client endpoints per tracking token, public routes per IP
- [ ] Structured JSON logs add `source_app_id`
- [ ] Tests: endpoint feature tests, idempotency, webhook signature and replay, scrubber unit tests, job double-dispatch, connector contract fixtures, duplicate detection, AI shadow mode
- [ ] Load test the ingestion endpoint before Phase 3 sign-off
