# Deploying Blush & Bloom Salon to Vercel

The app runs as a container on Vercel (FrankenPHP + Docker), following Vercel's guide
"Deploy Laravel on Vercel with Docker". Vercel has no database and a non-durable filesystem, so:

| Need | Solution |
|---|---|
| MySQL | External MySQL reachable from the internet (managed MySQL host of your choice) |
| Sessions | `SESSION_DRIVER=cookie` (no table needed) |
| Cache | `CACHE_STORE=array` (per-request; throttling is best-effort) |
| Logs | `LOG_CHANNEL=stderr` -> Vercel runtime logs |
| Admin image uploads | Written to `public/uploads` and **lost on redeploy/new instance**. Use the default Unsplash/local images, or move uploads to Vercel Blob later. |

## 1. Files added for Vercel
`Dockerfile.vercel`, `Caddyfile`, `.dockerignore`, `vercel.json`, `.env.vercel.example`.
Also: `trustProxies(at: '*')` in `bootstrap/app.php` and optional MySQL TLS in `config/database.php`.

## 2. Check `vercel.json` against Vercel's guide (one command)
The `services` block is included. Vercel's guide also adds a catch-all rewrite to the `app` service.
Print the exact current text and copy any `rewrites` block into `vercel.json`:

    npx vercel@latest kb inspect laravel-php-with-docker

## 3. Create the database and load the schema (from your computer)
1. Create a MySQL database on a managed host and note host, port, database, user, password.
2. Put those values in a **temporary local** `.env`, then run (never `migrate:fresh`):

       php artisan migrate --force
       php artisan db:seed --force

3. Switch your local `.env` back to XAMPP afterwards.

## 4. Deploy
    npm i -g vercel
    vercel login
    vercel link
    # add every variable from .env.vercel.example (use real values) in the dashboard or with `vercel env add`
    vercel deploy --prod

Set `APP_URL` and `GOOGLE_REDIRECT_URI` to the final Vercel URL **after** the first deploy, then redeploy.

## 5. After the first deploy
1. **Google sign-in:** Google Cloud Console -> Credentials -> your OAuth client -> add
   `https://YOUR-PROJECT.vercel.app/auth/google/callback` under Authorized redirect URIs.
2. **n8n -> Laravel:** in the 3 workflows replace `https://YOUR-TUNNEL-URL` with `https://YOUR-PROJECT.vercel.app`
   (the paths stay the same). No tunnel is needed any more.
3. **Reminders:** the hourly n8n workflow calls `/api/n8n/reminders/claim` - nothing to run on Vercel.
4. Test: book an appointment, check the email, ask the chat assistant for a service price.

## Known limits
- The Laravel scheduler and queue workers do not run on Vercel (jobs run `sync`).
- Uploaded admin images are not durable (see table above).
- Never commit `.env`; never put real secrets in the ZIP.
