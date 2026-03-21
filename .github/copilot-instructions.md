## Repo snapshot

- Monorepo of several Laravel applications (subfolders: `G16_CAPSTONE`, `Login`, `Logify`, `PN_Counterpart`, `PN-Portion`, `PN-ScholarSync`, `group_13`, ...).
- Backend: Laravel (PHP 8.2+). Frontend: Vite + Tailwind in each app that has a `package.json`.

## What to know first (big picture)

- This workspace contains multiple related Laravel apps organized as separate subprojects. Each app is a mostly-standard Laravel monolith (MVC under `app/Http/Controllers`, `app/Models`, `resources/views`).
- Many apps share conventions that diverge from Laravel defaults: custom primary keys (e.g. `PNUser` uses `user_id`), non-standard column names (`user_password`, `user_email`), and environment-driven cross-subsystem links (e.g. `SYSTEM_3_URL`). Always inspect a model's `$table` and `$primaryKey` before changing queries.
- Auth/token flows frequently rely on Laravel Sanctum personal access tokens. Tokens are created in controllers (see `Login/app/Http/Controllers/API/AuthController.php`) and forwarded in query strings to other subsystems.

## Developer workflows & common commands

- PHP deps (each app root):

```powershell
cd <app-folder>
composer install
```

- Frontend deps & dev server (if `package.json` exists):

```powershell
npm install
npm run dev
```

- Typical DB setup (in an app root):

```powershell
php artisan migrate --seed
```

- Many subprojects include `composer.json`/`package.json` scripts that run `php artisan serve`, queues, and Vite together. Check the app's `composer.json`/`package.json` for `dev` scripts.

## Project-specific patterns & gotchas

- Models deviate from defaults: check `$table`, `$primaryKey`, and `$incrementing`. Example: `Login/app/Models/PNUser.php` uses `user_id` primary key and `user_password` attribute.
- Seeders and migrations often assume strict FK ordering. Avoid `truncate()` unless you handle foreign key checks or delete children first.
- Column renames must account for explicit `foreign()` constraints added in migrations (e.g., `created_by` referencing `pnph_users.user_id`).
- Token forwarding: views often append `?token={{ $token }}` to external subsystem URLs defined in env vars; do not log tokens or remove them from redirects.

## Integration points & external dependencies

- Environment variables often contain subsystem URLs (search for `SYSTEM_` or `SUBSYSTEM_` in `resources/views` and `.env.example`). Update env vars when changing cross-app routes.
- Sanctum config is in `config/sanctum.php` — tokens are used across apps for single-sign-on-like flows.

## Examples (copyable patterns)

- Create a token and redirect (exact pattern found in `Login`):

```php
$user = PNUser::where('user_id', $request->user_id)->first();
$token = $user->createToken('subsystem-token')->plainTextToken;
return redirect()->route('main-menu', ['token' => $token, 'user_role' => $user->user_role]);
```

- Query a user with its related detail (model uses `user_id`):

```php
$user = PNUser::with('studentDetail')->where('user_id', $id)->first();
```

## Where to look for specifics

- Login app: `Login/app/Models/PNUser.php`, `Login/app/Http/Controllers/API/AuthController.php`, `Login/.github/copilot-instructions.md` (has more examples).
- G16_CAPSTONE: `G16_CAPSTONE/app/` — runs tests under `phpunit.xml` in that folder.
- Frontend: check `package.json` and `vite.config.js` in each subproject.

## What to avoid / watch out for

- Don't assume `id` is the primary key. Always use a model's `$primaryKey`.
- Avoid `truncate()` in seeders without handling foreign keys.
- Preserve token query params when implementing cross-subsystem redirects.

---

If you'd like, I can merge additional app-specific snippets (for `G16_CAPSTONE`, `Logify`, etc.) into this file. Which subproject should I prioritize next?
