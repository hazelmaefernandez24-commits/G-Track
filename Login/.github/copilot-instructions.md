## Repo snapshot

- Framework: Laravel 12 (PHP 8.2). Key entry points: `public/index.php`, `routes/web.php`, `routes/api.php`.
- Frontend: Vite + Tailwind (see `package.json`, `vite.config.js`).
- Auth: Custom `PNUser` model backed by `pnph_users` table and Laravel Sanctum tokens (`app/Models/PNUser.php`, `config/sanctum.php`).

## High-level architecture (what to know first)

- Monolithic Laravel application with classic MVC organization under `app/Http/Controllers`, `app/Models`, `resources/views`.
- Persistent user model differs from default: `PNUser` uses `user_id` as primary key and `public $incrementing = false`. Many relations rely on `user_id` and custom column names (example: `StudentDetail->user()` uses `user_id`). Always inspect model `$table`, `$primaryKey`, and `$fillable` before editing queries.
- Several domain tables use non-standard names and composite unique constraints (see migrations under `database/migrations`). Migrations often add `foreign()` constraints; when modifying or truncating data be mindful of FK ordering.
- Authentication uses Sanctum personal access tokens for cross-subsystem redirects. Tokens are created in `AuthController::login()` and looked up via `PersonalAccessToken::findToken()` in controllers and views.

## Developer workflows & commands (do this first)

- Install PHP deps: run Composer in the project root. Typical on Windows:

```powershell
composer install
```

- Install frontend deps and start Vite (dev):

```powershell
npm install
npm run dev
```

- Run the full dev stack (see `composer.json` scripts -> `dev`): uses `concurrently` to run `php artisan serve`, `php artisan queue:listen`, `php artisan pail`, and `npm run dev`.

- Database migrations & seeds:

```powershell
php artisan migrate --seed
```

Note: migration/seed order and foreign keys matter — truncating a parent table will fail if children exist. Use `DB::table('child')->truncate()` first or disable foreign key checks temporarily in seeders.

## Project-specific conventions & pitfalls

- Models often deviate from Laravel defaults:
  - `PNUser` uses `user_id` primary key and custom attribute names (`user_password`, `user_email`, etc.). Use these names when querying or writing migrations.
  - `StudentDetail` relates to `PNUser` via `user_id` not `id`.
- Password hashing: the code stores bcrypt hashes in `user_password` and checks them via `Hash::check()` (see `AuthController`). Prefer `Hash::make()` when setting passwords.
- Seeders & truncation: do not call `truncate()` on tables referenced by foreign keys. Use ordered deletes or wrap truncation with `DB::statement('SET FOREIGN_KEY_CHECKS=0;')` then re-enable — only in seeding/dev.
- Token flows: many views consume a `token` query param and redirect to other subsystems via `env('SYSTEM_3_URL')?token={{ $token }}` — maintain token forwarding and do not expose tokens in logs.

## Integration points & external dependencies

- External subsystems are referenced in views via environment variables such as `SYSTEM_3_URL` (see `resources/views/landing-page.blade.php`). When updating cross-subsystem links keep the token in the query string.
- Sanctum configuration lives in `config/sanctum.php`. The app sets `guard` => `['web']` and does not limit `expiration` by default.

## Examples to follow

- Creating a token and redirecting to main menu (exact pattern):

```
$user = PNUser::where('user_id', $request->user_id)->first();
$token = $user->createToken('subsystem-token')->plainTextToken;
return redirect()->route('main-menu', ['token' => $token, 'user_role' => $user->user_role]);
```

- Relation example when adding a model or query:

```
$user = PNUser::with('studentDetail')->where('user_id', $id)->first();
$batch = $user?->batch;
```

## What to avoid / watch out for

- Don't assume `id` is the primary key on user-related tables. Use the model's `$primaryKey` and `$table`.
- Avoid `truncate()` in seeders unless foreign keys are handled. Common seeder failures stem from FK constraints (example error: "Cannot truncate a table referenced in a foreign key constraint").
- Be careful when renaming columns — migrations add explicit `foreign()` constraints to named columns like `created_by` referencing `pnph_users.user_id`.

## Where to look next (quick map)

- Auth flows & token handling: `app/Http/Controllers/API/AuthController.php`
- User model and related fields: `app/Models/PNUser.php`, `app/Models/StudentDetail.php`
- Migrations for table names/foreign keys: `database/migrations/*` (search for `pnph_users`, `assignments_members`, `student_group16`)
- Frontend dev: `package.json`, `vite.config.js`, `resources/views/*`

---

If you'd like, I can open and merge this into an existing `.github/copilot-instructions.md` if present, or iterate on wording and include additional examples (seeders, common failing stack traces). Which sections should I expand or add examples for next?
