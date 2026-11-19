# Filament Ticket Plugin (Client-Side "Create Ticket")

## Problem

Host web projects that run a Filament panel need a way to create a support ticket in the TMS. The plugin is installed via Composer and adds a "Create ticket" action to the host panel. Host staff use it, and it sends the ticket to the TMS over HTTP. The host never touches TMS tables.

## Confirmed Decisions

* **Transport:** HTTP to the existing `POST /api/v1/tickets` endpoint (Sanctum, `SourceApp` token).
* **Users:** Host app staff (Filament panel users).
* **Filament:** v5 only (matches TMS `filament/filament ^5.0`).
* **Languages:** `en` and `ar` with RTL, matching TMS.
* **Form fields:** title (required), description (required), priority hint, page URL (auto-filled), reporter name and email, screenshots, and device metadata (auto-captured).

## Approach

Build a Composer package in this repo at `packages/filament-tms-tickets`, with namespace `Tms\FilamentTickets\`. Host projects install it with a Composer `path` repository (or a VCS repository later). The package has no database tables.

### Key Files (Package)

* `composer.json`: requires `filament/filament ^5.0`, `illuminate/support`, and `php ^8.3`. Auto-discovers the service provider.
* `src/FilamentTicketsPlugin.php`: implements `Filament\Contracts\Plugin`. Registers the action in the panel's header. Each panel opts in with `->plugin(FilamentTicketsPlugin::make())`.
* `src/Actions/CreateTicketAction.php`: Filament action with a modal form. Pre-fills `page_url` from the current request. Fills `device_metadata` from a small Alpine snippet (user agent, viewport, console errors buffered from `window.onerror`).
* `src/Services/TmsTicketClient.php`: `Http::withToken()->acceptJson()->timeout()->post()`. Sends a UUID v4 `Idempotency-Key` per submission. Maps the 202 response to a `ticket_number` notification. Maps 422 to form field errors, and 401, 413, 429, and 5xx to user-facing messages.
* `src/Services/ScreenshotEncoder.php`: converts uploaded files to `data:image/*;base64,` URIs. Enforces a limit of 5 files and 2 MB per file, and checks MIME type by content sniffing.
* `config/tms-tickets.php`: `url`, `token`, `timeout`, `enabled`. Values come from `TMS_TICKETS_URL` and `TMS_TICKETS_TOKEN` in the host `.env`.
* `resources/lang/en/*.php` and `resources/lang/ar/*.php`: all plugin strings.
* `resources/views/...`: only if a custom modal partial is needed.

### TMS-Side Changes

* No schema changes are expected. `IngestTicketAction` and `StoreTicketRequest` already cover the contract.
* Confirm whether `SourceAppResource` can issue tokens for host apps. If it cannot, add token issuing.
* Add an API test showing that a `SourceApp` token can create a ticket with `device_metadata` and screenshots.

## Behavior Notes

* **Idempotency:** The key is generated when the modal opens, not on submit. A retried submit after a network error reuses the same key, so no duplicate ticket is created.
* **Timeouts:** The HTTP call runs synchronously in the action with a short timeout (for example, 10 s). The TMS queues screenshot decoding, so the 202 response is fast.
* **Errors:** A 422 shows field-level errors in the form. Network or 5xx errors keep the modal open and show a retry message.
* **Security:** The token is read from `config` only. It is never rendered into the page or logged. Device metadata is sent only after the user submits, not on modal open.
* **Privacy:** The form tells the user what metadata is captured. Console errors are truncated to 1,000 characters per entry, with at most 50 entries, as the API requires.

## Testing

* **Package tests (Pest with Orchestra Testbench):**
  * The action renders and validates.
  * `TmsTicketClient` sends the bearer token, the `Idempotency-Key` header, and the expected payload (`Http::fake`).
  * 422 maps to field errors.
  * 401, 413, 429, and 5xx map to messages.
  * `ScreenshotEncoder` rejects oversize and non-image files.
* **Locale test:** every key exists in both `en` and `ar`.
* **TMS feature test:** a `SourceApp` token creates a ticket with `device_metadata` and screenshots.
* Run the smallest suite that covers the change: the package tests plus the single TMS feature test.

## Open Items

* Whether the TMS already has a UI to issue `SourceApp` tokens. If not, the plan adds one.
* Whether the package needs a header action or a user-menu action. The default is a header action.
* Package name and vendor namespace (`tms/filament-tickets` proposed).

## Implementation Todos

1. Scaffold the `packages/filament-tms-tickets` package (composer.json, provider, plugin class, config).
2. Build `TmsTicketClient` with idempotency keys and error mapping.
3. Build `ScreenshotEncoder`.
4. Build `CreateTicketAction` with the modal form and device metadata capture.
5. Add `en` and `ar` translations and check RTL.
6. Confirm or add `SourceApp` token issuing in the TMS admin.
7. Write package tests and the TMS feature test.
8. Document installation in the package README and in `Docs/Plan.md`.
