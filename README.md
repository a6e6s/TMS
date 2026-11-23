# Ticketing Management System (TMS)

TMS is a Laravel 13 and Filament 5 application that collects bug reports and support tickets from other web applications and tracks them in one place. Each web app connects with the [filament-tms-tickets](https://github.com/a6e6s/filament-tms-tickets) plugin, or with the HTTP API directly.

## Features

- **Ticket intake API** for external apps, authenticated with a per-source-app Sanctum token.
- **Screenshot storage** for tickets, shown in a gallery on the ticket view.
- **Browser and reporter metadata** captured with each ticket (user agent, viewport, page URL).
- **Comments** on tickets, with a client API for submitters to follow up.
- **Project management system (PMS) integration** that pushes tickets as tasks, with status sync through webhooks. Supported providers:
  - FlexFlow (`PMS_PROVIDER=flexflow`)
  - A generic HTTP provider (`PMS_PROVIDER=custom`)
- **AI triage** (optional) classifies each new ticket by category (for example bug, feature request, or user error), suggests a priority, and writes a summary. It raises a ticket to `critical` only when the model is highly confident. Works with any OpenAI-compatible endpoint, such as NVIDIA NIM or OpenRouter.
- **Roles and permissions** through `spatie/laravel-permission`, and a Filament admin panel.
- **English and Arabic** interface.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MariaDB or MySQL (SQLite and others work with a change to `DB_CONNECTION`)

## Installation

```bash
git clone https://github.com/a6e6s/TMS.git
cd TMS
composer install
cp .env.example .env
php artisan key:generate
npm install && npm run build
```

Edit `.env` for your database, then run:

```bash
php artisan migrate --seed
php artisan serve
```

The admin panel is at `/admin`. Sign in with `ADMIN_EMAIL` and `ADMIN_PASSWORD` from `.env`. Change the default password after the first sign-in.

## Configuration

Set these in `.env`. The full list is in [.env.example](.env.example).

| Variable | Purpose |
| --- | --- |
| `APP_URL` | Public URL of TMS. Used in links and in the plugin's tracking URL. |
| `DB_*` | Database connection. |
| `QUEUE_CONNECTION` | Background jobs, including PMS pushes. Default is `database`. |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Credentials for the seeded admin account. |
| `PMS_PROVIDER` | `flexflow`, `custom`, or empty. |
| `PMS_BASE_URL` | Base URL of the PMS API. For FlexFlow: `https://todo.3asry.com/api`. |
| `PMS_API_TOKEN` | API key for the PMS. Keep it out of version control. |
| `PMS_DEFAULT_PROJECT_ID` | Fallback PMS project when a source app has none. A source app's own project ID takes priority. |
| `AI_TRIAGE_ENABLED` | Set to `true` to classify new tickets. Default is `false`. |
| `AI_TRIAGE_BASE_URL` | OpenAI-compatible base URL, for example `https://integrate.api.nvidia.com/v1` or `https://openrouter.ai/api/v1`. |
| `AI_TRIAGE_API_KEY` | API key for the AI provider. Keep it out of version control. |
| `AI_TRIAGE_MODEL` | Model ID to use. Required when triage is enabled. |
| `AI_TRIAGE_MIN_CONFIDENCE` | Results below this confidence are stored as unclear. Default is `0.5`. |
| `AI_TRIAGE_AUTO_CRITICAL_MIN_CONFIDENCE` | Confidence needed before a `critical` suggestion is applied. Default is `0.85`. |

## Running the queue

PMS pushes run as queued jobs, so a worker must be running:

```bash
php artisan queue:work
```

Without a worker, "Push to PMS" stays queued and nothing is sent.

## Connecting a source app

1. In TMS admin, create a **Source app**.
2. Optionally set its **PMS project** to the project tasks should go to.
3. Use the token action in the Source apps table. It creates a Sanctum token with the `tickets:create` ability and shows it once. Copy it then.
4. Configure the client app with the TMS URL and the token. With the plugin, see the plugin's README.

Ticket submission endpoint (Bearer token required):

```
POST /api/v1/tickets
```

Other endpoints:

- `GET /api/v1/tickets/{ticketNumber}`: client view of a ticket.
- `POST /api/v1/tickets/{ticketNumber}/comments`: add a comment.
- `POST /api/v1/webhooks/pms/{provider}`: PMS status webhooks, signed with the provider's secret.

## Testing

```bash
php artisan test
```

Run the code style check with:

```bash
vendor/bin/pint --test
```

## Security

- Never commit `.env`. It is in `.gitignore`.
- Source app tokens and `PMS_API_TOKEN` are secrets. Rotate them if they leak.
- Report security issues privately to the maintainer, not in a public issue.

## License

MIT. See [LICENSE](LICENSE).
