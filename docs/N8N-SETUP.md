# n8n + Google Gemini + Gmail SMTP setup — Blush & Bloom Salon

This guide connects the three workflows in `n8n-workflows/` to your Laravel app.

```
Browser chatbot ──► Laravel POST /api/chat ──► n8n Workflow 1 (Gemini AI Agent) ──► Laravel /api/n8n/* tools
Booking / admin status change ──► Laravel ──► n8n Workflow 2 ──► Gmail SMTP ──► report result to Laravel
n8n Workflow 3 (every hour) ──► Laravel /api/n8n/reminders/claim ──► Gmail SMTP ──► report result to Laravel
```

Laravel keeps control of the database, validation and bookings. n8n only handles AI and email.

---

## 1. Run n8n locally

Choose one:

**npx (simplest on Windows):**
```bash
npx n8n
```
Open <http://localhost:5678> and create the owner account.

**Docker:**
```bash
docker run -it --rm --name n8n -p 5678:5678 -v n8n_data:/home/node/.n8n docker.n8n.io/n8nio/n8n
```
With Docker, n8n cannot reach `127.0.0.1:8000` on your PC. Use `http://host.docker.internal:8000` instead (see step 5), and start Laravel with `php artisan serve --host=0.0.0.0`.

## 2. Create the secrets in Laravel `.env`

```bash
php -r "echo bin2hex(random_bytes(24)), PHP_EOL;"   # run twice
```
```dotenv
N8N_WEBHOOK_SECRET=<first random value>
N8N_API_TOKEN=<second random value>
```

## 3. Get a Google Gemini API key (free tier)

1. Go to **Google AI Studio** → <https://aistudio.google.com/apikey> and sign in with a Google account.
2. Click **Create API key** and copy it.
3. Check which models are marked **"Free of charge"** on <https://ai.google.dev/gemini-api/docs/pricing>. Free-tier models have per-minute and per-day request limits. When the limit is reached, Gemini returns HTTP 429. Laravel then automatically answers from the database fallback.
4. Free-tier prompts may be used by Google to improve its products, so do not send private data in tests. The workflow ships with `models/gemini-3-flash-preview`. If that model is not free or not available for your key, open the **Google Gemini Chat Model** node and pick another Flash model from the list.

## 4. Create a Gmail app password (for SMTP)

1. Turn on **2-Step Verification** for the Gmail account: <https://myaccount.google.com/security>.
2. Open <https://myaccount.google.com/apppasswords>, create an app password named "n8n", and copy the 16-character code.
3. SMTP settings: host `smtp.gmail.com`, port `465`, SSL/TLS **on**, user = your Gmail address, password = the app password.

## 5. Create the n8n credentials

In n8n → **Credentials** → **Add credential**:

| Credential type | Name (use exactly this so the import matches) | Values |
|---|---|---|
| **Header Auth** | `Blush & Bloom - Webhook Secret` | Name: `X-Webhook-Secret` · Value: your `N8N_WEBHOOK_SECRET` |
| **Header Auth** | `Blush & Bloom - Laravel API Token` | Name: `Authorization` · Value: `Bearer ` + your `N8N_API_TOKEN` (note the space) |
| **Google Gemini(PaLM) Api** | `Google Gemini (PaLM) API` | API key from step 3 |
| **SMTP** | `Gmail SMTP - Blush & Bloom` | Settings from step 4 |

## 6. Import the workflows

For each file in `n8n-workflows/`: n8n → **Workflows** → **⋯ / Import from File**.

After importing, open every node that shows a warning ⚠ and:
- choose the matching credential from step 5;
- in the **Send Email** nodes, replace `REPLACE_WITH_YOUR_GMAIL@gmail.com` with your Gmail address;
- if Laravel is not at `http://127.0.0.1:8000` from n8n's point of view (Docker, another PC, deployment), change the URL in every HTTP Request / tool node. Workflow 1 has 6 tool nodes, Workflow 2 has 2 report nodes, Workflow 3 has 3 HTTP nodes.

Save, then switch each workflow to **Active** (Published).

## 7. Connect Laravel to the webhooks

Open the webhook node of Workflows 1 and 2 and copy the **Production URL**, then set:
```dotenv
N8N_CHAT_WEBHOOK_URL=http://localhost:5678/webhook/blush-bloom-chat
N8N_APPOINTMENT_WEBHOOK_URL=http://localhost:5678/webhook/blush-bloom-appointments
```
```bash
php artisan config:clear
```
(The *Test URL* `/webhook-test/...` only works while you click "Listen for test event" in the editor.)

## 8. Test each workflow

Check **Admin → Automation Logs** after every step. The "Integration configuration" box should show every item as *Configured*.

| Test | Expected result |
|---|---|
| Open the website chatbot and ask "How much is a haircut?" | Reply from Gemini (no "AI assistant offline" note). Log: `chat.request` = **Success** |
| Ask the chatbot "Is there a 2 PM slot for a facial on (a future date)?" | The agent calls `check_availability` (visible in the n8n execution) and only offers real times |
| Ask the chatbot to book. Give name, email, phone, service, date, time, then confirm | A new **Pending** appointment with source `Chatbot` in Admin → Appointments |
| Book through the website form | Log `appointment.created`: **Sent to n8n**, then **Email sent** a few seconds later. Pending email arrives |
| Admin → Appointments → **Confirm** | Confirmation email. Log `appointment.status_changed` → **Email sent** |
| **Cancel** or **Reject** an appointment | Cancellation email |
| Confirm an appointment for tomorrow, set *Send reminders* to 24 h in Settings, then in Workflow 3 click **Execute workflow** | Reminder email. Appointment page shows "Reminder sent". Running again sends nothing (no duplicates) |
| Stop n8n and make a booking | Booking still succeeds. Log shows **Failed** (n8n unreachable) |

Only report an integration as working after you have seen these results yourself.

## 9. How duplicates are prevented
- **Bookings:** each form submission has a one-time token, and the button is disabled after the first click.
- **Appointment emails:** Laravel sends each `appointment.created` event, and each status change to a given status, only once per appointment (unique `idempotency_key`).
- **Reminders:** `/api/n8n/reminders/claim` marks appointments as *processing* inside a database lock, so overlapping runs cannot take the same appointment. `reminder_sent_at` is set only after n8n reports success. Failed reminders are retried on the next run.

## 10. Troubleshooting

| Problem | Fix |
|---|---|
| Chatbot always shows "AI assistant offline" | Check `N8N_CHAT_WEBHOOK_URL`, run `php artisan config:clear`, make sure Workflow 1 is Active. See the log message in Automation Logs |
| n8n tool calls return 401 | The `Authorization` header value must be `Bearer <N8N_API_TOKEN>`, exactly the same as in `.env` |
| Webhook returns 403 / "Authorization data is wrong" | `X-Webhook-Secret` in n8n must equal `N8N_WEBHOOK_SECRET` |
| n8n cannot reach Laravel (ECONNREFUSED) | Using Docker? Use `host.docker.internal` and `php artisan serve --host=0.0.0.0` |
| Gemini 429 / quota exceeded | Free-tier limit reached. Wait, or choose another free model. Laravel falls back automatically |
| Gmail "Invalid login" | Use an **app password**, not your normal Gmail password; 2-Step Verification must be on |
