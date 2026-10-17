# Laravel Email Campaign Manager

A Laravel + Filament admin panel for creating email campaigns, targeting a filtered audience of leads, and sending the campaign in the background through a queue, with per-lead delivery tracking and automatic campaign status updates.

## Features

- **Campaign management** (Filament): create and edit campaigns with a subject, an HTML body, and audience filters.
- **Audience targeting**: choose recipients by name, email, country, status, and age range (min / max).
- **Background sending**: clicking **Send** only prepares the work; a queue worker sends the emails.
- **Per-lead tracking**: every recipient has its own status in `campaign_leads` (`pending`, `processing`, `sent`, `failed`) with attempt count and sent time.
- **Automatic campaign status**: the campaign moves from `in_progress` to `completed` or `failed` on its own when the last recipient finishes.
- **Safe retries**: each email is retried up to 3 times with a delay between attempts, and a lead is never emailed twice.
- **Crash recovery**: if a worker dies mid-send, the retry picks the lead back up instead of leaving it stuck.

## Tech Stack

- PHP / Laravel
- Filament (admin panel)
- Database queue driver
- SQLite (default for local development)
- [Laravel Herd](https://herd.laravel.com) for local development on Windows

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- SQLite (or another database supported by Laravel)

## Installation

```bash
git clone https://github.com/MazenHassan-10/laravel-email-campaign-manager.git
cd laravel-email-campaign-manager

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
```

Create an admin user for the Filament panel:

```bash
php artisan make:filament-user
```

## Configuration

Set these values in `.env`:

```env
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

For local testing, use a catch-all inbox such as [Mailpit](https://mailpit.axllent.org) or Mailtrap so no real emails go out.

## Running the Project

The queue worker **must be running**, otherwise emails will never be sent and campaigns will stay `in_progress`.

```bash
composer dev
```

This starts the web server, the queue worker, and the log viewer together. To run the worker on its own:

```bash
php artisan queue:work
```

During development, `php artisan queue:listen` reloads your code on every job, so you don't need to restart it after edits. With `queue:work`, run `php artisan queue:restart` after changing job code.

Open the admin panel at `/admin` and log in with the user you created.

## How Sending Works

1. **Send** is clicked on a campaign.
2. The campaign is marked `in_progress`.
3. Every matching lead is written to `campaign_leads` with status `pending`.
4. Only after all rows exist, one `SendCampaignEmailJob` is dispatched per pending lead.
5. The worker picks up each job:
   - claims the row (`pending` → `processing`),
   - sends the email,
   - marks the row `sent` and checks whether the campaign is finished.
6. If a job fails 3 times, the row is marked `failed` and the campaign is checked again.
7. When no row is left `pending` or `processing`, the campaign becomes:
   - `completed` if every email was sent,
   - `failed` if at least one lead failed.

Rows are written before any job is dispatched so that the first finished job can never close the campaign while others are still being added.

### Status reference

| Level | Status | Meaning |
|---|---|---|
| Campaign | `in_progress` | Sending has started and is not finished |
| Campaign | `completed` | All emails were sent |
| Campaign | `failed` | Finished, but at least one lead failed permanently |
| Lead (`campaign_leads`) | `pending` | Waiting to be sent |
| Lead | `processing` | A worker is sending it right now |
| Lead | `sent` | Delivered to the mail server |
| Lead | `failed` | Failed after all retries; will not be retried |

## Project Structure

```
app/
├── Filament/Resources/Campaigns/   # Campaign resource, form, and table (Send action)
├── Jobs/SendCampaignEmailJob.php   # Sends one email to one lead
├── Mail/CampaignMail.php           # Mailable (subject + HTML body)
└── Models/
    ├── Campaign.php                # matchingLeadsQuery(), refreshStatus()
    └── Lead.php
```

## Troubleshooting

| Problem | Likely cause and fix |
|---|---|
| Campaign stays `in_progress` and nothing is sent | The queue worker is not running. Start it with `composer dev` or `php artisan queue:work`. |
| Campaign stays `in_progress` but all leads are `sent` | The worker is running old code. Stop it and start it again, or use `queue:listen`. |
| `View [php] not found` | The body was passed as a view name. Use `htmlString:` (not `text:`) in `CampaignMail::content()`. |
| `no such table` | Run `php artisan migrate` and check the table name spelling (`campaign_leads`). |
| Status doesn't change on screen | Refresh the page, or enable `->poll('5s')` on the Filament table. |
| A job failed | Check `storage/logs/laravel.log`, and `php artisan queue:failed` for the full exception. |

Useful commands:

```bash
php artisan queue:failed         # list failed jobs
php artisan queue:retry all      # retry failed jobs
php artisan queue:restart        # make workers reload code
php artisan tinker               # inspect data manually
```

## Production Notes

- Run the queue worker under **Supervisor** (or a similar process manager) so it restarts automatically.
- Keep the job `$timeout` lower than the queue connection's `retry_after`, so a slow job is not picked up by a second worker while still running.
- Consider Redis instead of the database queue driver for large campaigns.
- Use a real SMTP provider and respect its sending limits.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
