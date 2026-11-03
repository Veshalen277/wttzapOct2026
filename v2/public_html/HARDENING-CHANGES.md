# Hardening pass – what changed and what you must do

## Do this BEFORE deploying
1. **Rotate the database password and the SMTP mailbox password.** The old values were in `config.php`
   and in ~14 other files, and have been shared in plain text. Treat them as compromised.
2. Copy `config.local.php.example` to `config.local.php` (or set `DB_*`, `SMTP_*`, `CRON_TOKEN`
   environment variables, or point `PORTAL_CONFIG` at a file outside the web root) and fill in the NEW values.
3. Run `bin/check-environment.php` and `bin/release-check.sh` on staging.
4. Schedule these from cron (CLI), not from a browser:
   `php send_daily_report.php`, `php send_income_report.php`, `php auto_archive.php`.
   Over HTTP they now require the `X-Cron-Token` header matching `CRON_TOKEN`.

## Fixed
- `post_react.php` parse error; inverted role check on `admin/admin-stats-report`; missing functions
  (`get_recent_orders`, `get_data_quality_summary`, `fetchWorkRecords`).
- 236 fragile `../` includes converted to `__DIR__`; 9 genuinely broken includes; 9 broken menu links.
- Secrets removed from code; `config.php` no longer emits whitespace (headers-already-sent).
- Authentication / authorization / CSRF / validation / prepared statements on:
  project edit+delete, CSV export, batch print+email, admin task + manager-report endpoints,
  leave approval (now transactional, no double-deduct), stock report updates.
- Global same-origin guard for every POST (covers legacy handlers without tokens); session cookie is
  HttpOnly + SameSite=Lax (+Secure on HTTPS).
- Raw database/exception text no longer shown to users (40 places) – logged instead.
- `display_errors` off everywhere (use `PORTAL_DEBUG=1` locally).
- CSV export neutralises spreadsheet formula injection. Uploads folders cannot execute PHP.
- Removed: web-readable `error_log` files, dead duplicate/legacy scripts, scratch scripts.

## Still open (needs your decision or a database to test)
- Passwords: legacy rows are SHA-1. The sign-in handler lives outside this folder (`/index.php`), so it
  could not be upgraded here. Move it to `password_hash()` and rehash on login.
- ~50 older POST handlers rely on the same-origin guard rather than per-form tokens.
- Nothing was run against a real database or browser. Test sign-in, leave approval, project edit/delete,
  orders, stock and the cron scripts on staging, and back up before deploying.
- Role assumptions I made (change if wrong): project edit/delete = creator or role 7; CSV export, batch
  report, leave admin = roles 2 and 7; admin task/manager-report endpoints = roles 7 and 8.

## Multi-department access for management roles (added later)
1. Run `database/migrations/2026-10-04-user-departments.sql` once (phpMyAdmin > SQL). Back up first. Until you do,
   everything behaves exactly as before (each user has just their main department).
2. Admin > Edit Employee (role 7 only) now has an "Additional departments" checklist. The main department
   (`users_tbl.user_scale`) is untouched and always kept.
3. Who can hold extra departments is controlled by `MULTI_DEPT_ROLES` (default `0,2,3,4,5,6,7`, i.e. everyone except
   role 1 Normal User). Override in `config.local.php`, e.g. `'MULTI_DEPT_ROLES' => '2,6,7'`. If someone is demoted
   to a non-management role their extra departments stop working immediately (rows are kept for re-promotion).
4. Cashup forms show a department picker when the user has more than one; the server re-checks it in the database,
   so a tampered form cannot file under a department the user does not hold.
5. Reports, exports and the dashboard now group by the department stored on each cashup, not the submitter's main one.
6. Also hardened `edit_emp.php`: CSRF, and only role 7 can edit a role-7 account or assign roles 6/7.
