# WriteNow

A small home screen web app at `/writenow/` for posting notes (the Shortnotes `shortnote` post type) and likes (the Likes `like` post type) to jeremyfelt.com from an iPhone, plus a JSON API that iOS Shortcuts call directly. It succeeds the Micropub client and the WriteWell Android app.

- Design and contract: [`docs/writenow/spec.md`](../../../docs/writenow/spec.md)
- Shortcut recipes: [`docs/writenow/shortcuts.md`](../../../docs/writenow/shortcuts.md)

It authenticates with a WordPress Application Password sent as HTTP Basic auth. The app obtains one through `wp-login.php` and the core application-passwords route, or takes one pasted in Settings. It writes notes as light markdown, converted to block markup on the server, and keeps everything typed on the phone until the site confirms it, with an IndexedDB outbox for posts made offline.

## Layout

```
mu-plugins/apps/writenow/
  writenow.php           Bootstrap: constants (VERSION, PATH, REST namespace, meta keys) and requires.
  includes/
    routes.php           Serves /writenow/ (the shell), manifest.webmanifest and sw.js on parse_request,
                         filling the {{TOKENS}} in the app/ templates. No rewrite rules.
    rest.php             writenow/v1 routes, permission checks, shared helpers.
    notes.php            POST /notes, GET and POST /notes/<id>.
    likes.php            POST /likes, URL cleaning, dedupe, title lookup.
    recent.php           GET /recent.
    markdown.php         Markdown to block markup. Pure functions.
    cache.php            Keeps authenticated responses and REST refusals out of Batcache and shared caches.
    lock.php             A short options-table lock so a repeated request cannot create a second post.
  app/                   Everything the browser loads, committed in final form (no build step).
    shell.html           The app shell template. Its header comment lists the tokens.
    manifest.webmanifest, sw.js
    app.css, js/*.js     ES modules: api.js is the only module that does HTTP.
    icons/
```

The PHP talks to Shortnotes and Likes only through their public functions and post types, guarded with `function_exists()` and `post_type_exists()`. With either plugin inactive, its routes answer `501 writenow_feature_unavailable` and `/status` reports the feature as off.

## Tests

All dev-only, under `tests/writenow/`, excluded from deploy by `.deploy_ignore`. Nothing contacts the live site.

```sh
# Server tests, block editor check and end-to-end tests, against a fresh
# local WordPress on SQLite with the real shortnotes and likes plugins:
tests/writenow/run.sh

tests/writenow/run.sh e2e          # only the end-to-end tests
tests/writenow/run.sh like         # only PHP tests whose names contain "like"
WP_VERSION=6.8.3 WRITENOW_TEST_PORT=8882 tests/writenow/run.sh   # an older core

# Browser tests against an intercepted API (fast, no WordPress):
cd tests/writenow/client && npm ci && npm test

# Use the app by hand at http://127.0.0.1:8881/writenow/ (admin / password):
tests/writenow/run.sh --serve
```

Requirements, settings and what each suite covers are in `tests/writenow/README.md` and `tests/writenow/client/README.md`. The sibling checkouts `../shortnotes`, `../likes` and `../writemore` must sit beside this repository. Also run `composer phpcs` and `composer phpstan` (set `COMPOSER_ALLOW_SUPERUSER=1` when running as root).

## Deploy

There is no build step. A push to `develop` rsyncs `mu-plugins/` to production, and `mu-plugins/index.php` loads `apps/writenow/writenow.php`. Nothing needs a permalink flush or a database change.

The service worker version is derived from the modification times and sizes of the files in `app/`, so any deploy that changes the app gives it a new version; an open app switches to it on its next launch, never mid-session.

Production needs HTTPS (Application Passwords are only offered over HTTPS, or in a `local` environment) and a web server that passes the `Authorization` header to PHP. The `curl` checks in `docs/writenow/shortcuts.md` confirm both.

## Renaming

"WriteNow" is a working name. To rename it:

1. Change `PATH` in `writenow.php` (the URL segment, `writenow`). Routes, the shell URL, the manifest scope and the service worker scope all follow it.
2. Change the display name in `app/shell.html` (`<title>`, `apple-mobile-web-app-title`, the Connect heading) and in `app/manifest.webmanifest` (`name`, `short_name`).
3. Optionally rename the directory and the PHP namespace `JeremyFelt\Apps\WriteNow`, the REST namespace `REST_NAMESPACE` (`writenow/v1`, which existing Shortcuts call), and the Application Password names in `app/js/connect.js` and `app/js/views/settings.js`.

Changing the path or the REST namespace breaks an installed home screen app and any Shortcut built against the old addresses: reinstall the app and edit the Shortcut URLs. Keep the meta keys (`_markdown_content`, `_writenow_client_id`) unchanged so existing notes stay editable and retries stay idempotent.
