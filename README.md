# Postard — a Twitter/X clone (PHP + MongoDB)
https://postard.onrender.com

A full-featured social app: accounts, posts, image uploads, likes, replies,
reposts, follows, search, notifications, and direct messages — with a
dark, X-style responsive UI.

## Features

- Secure auth: bcrypt password hashing, CSRF protection on every form, rate limiting on login/register/posting, hardened session cookies
- Posts: 280-char limit, image attachments, delete your own posts
- Likes, replies (threaded), reposts
- Follow / unfollow, follower & following counts
- Home feed (people you follow) + Explore feed (everyone)
- Search for people and posts
- Notifications (likes, replies, reposts, follows, messages) with unread badge
- Direct messages between users
- Profile editing: display name, bio, avatar, banner
- Responsive UI down to mobile, with a bottom tab bar on small screens

## Project structure

```
config.php          App config, DB connection, security headers
includes/           functions.php (helpers), auth.php (auth/session helpers)
partials/           head.php, navbar.php, tweet_card.php (shared UI pieces)
assets/             css/style.css, js/app.js, img/ (default avatar/banner)
uploads/             User-uploaded avatars/banners/tweet images (gitignored)
db_setup.php         Run once to create MongoDB indexes
Dockerfile            Builds a PHP 8.2 + Apache + mongodb-extension image
docker-compose.yml    Local dev: app + a local MongoDB container
```

## Security notes (what's already handled)

- Passwords are hashed with bcrypt (`password_hash`), never stored or logged in plain text
- All output is escaped with `htmlspecialchars()` to prevent XSS
- Every state-changing form/request requires a CSRF token
- All MongoDB IDs from user input are validated against the ObjectId format before use, preventing NoSQL injection
- Session cookies are `HttpOnly`, `SameSite=Lax`, and `Secure` in production
- Uploaded files are validated by real MIME type (not just extension), size-capped, renamed to random names, and the `uploads/` folder has PHP execution disabled so an uploaded file can never run as a script
- Basic rate limiting on login, registration, posting, following, and messaging
- Security headers set on every response (CSP, X-Frame-Options, nosniff, etc.)

Nothing is ever 100% "hacker-proof" — if you plan to handle real user data at
scale, consider adding: email verification, 2FA, a Web Application Firewall,
and a professional security review.

---

## Run it locally (Docker)

You'll need [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed.

```bash
docker compose up --build
```

Then open **http://localhost:8080**. The first time, create indexes:

```bash
docker compose exec app php db_setup.php
```

That's it — a local MongoDB container is included, so there's nothing else to configure.

---

## Deploy for free (MongoDB Atlas + Render.com)

This combo hosts the database and the app for **$0/month**. It'll take about 15 minutes.

### 1. Push this project to GitHub

Create a new GitHub repo and push this folder to it (the `.gitignore` already excludes `.env` and uploaded files).

### 2. Create a free MongoDB database (Atlas)

1. Go to [mongodb.com/cloud/atlas/register](https://www.mongodb.com/cloud/atlas/register) and sign up.
2. Create a new project, then build a database → choose the **free M0** tier.
3. Under **Database Access**, create a database user with a username/password (save these).
4. Under **Network Access**, add IP address `0.0.0.0/0` (allow access from anywhere) — Render's IPs are dynamic, so this is the simplest option for a small app.
5. Click **Connect → Drivers**, copy the connection string. It looks like:
   `mongodb+srv://<user>:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority`
   Replace `<user>` and `<password>` with the values from step 3.

### 3. Deploy the app (Render)

1. Go to [render.com](https://render.com) and sign up (free, no card required for this tier).
2. Click **New → Web Service**, connect your GitHub repo.
3. Render will detect the `Dockerfile` automatically — choose **Docker** as the environment (Instance type: **Free**).
4. Under **Environment Variables**, add:
   - `MONGODB_URI` = the Atlas connection string from step 2 (make sure it includes a database name, e.g. `.../postarddb?retryWrites=true...`, or set `MONGODB_DB` separately as below)
   - `MONGODB_DB` = `postarddb`
   - `APP_ENV` = `production`
   - `APP_URL` = your Render URL (you'll get this after the first deploy, e.g. `https://postard.onrender.com`) — update it once known
5. Click **Create Web Service**. Render will build the Docker image and deploy it — this takes a few minutes on first deploy.
6. Once live, open the **Shell** tab for your service on Render and run:
   ```bash
   php db_setup.php
   ```
   (creates the database indexes — only needs to run once)
7. Visit your Render URL — your Postard app is live and accessible from anywhere. 🎉

**Note on the free tier:** Render's free web services spin down after ~15 minutes
of inactivity and take ~30-60s to wake back up on the next visit — normal for
a free demo, but consider a paid instance ($7/mo) if you want it always-on.

**Note on uploaded images:** Render's free tier has an ephemeral filesystem,
so avatars/banners/tweet images uploaded by users will be **wiped on every
redeploy or restart**. This is fine for testing. For a persistent production
setup, either upgrade to a Render paid plan with a persistent disk, or (better)
swap the `handle_image_upload()` function in `includes/functions.php` to
upload to a service like Cloudinary, AWS S3, or Backblaze B2 instead of local disk.

### Alternative free hosts

Any host that lets you deploy a Docker container works the same way (set the
same 3-4 environment variables, run `db_setup.php` once): [Railway](https://railway.app),
[Fly.io](https://fly.io), or a small VPS.

---

## Local environment variables

For local (non-Docker) PHP setups, copy `.env.example` to `.env` and fill in
your own values — `config.php` loads it automatically.
