# WriteNow tests

Dev-only. Nothing in `tests/` is deployed (see `.deploy_ignore`).

- `client/` holds the browser tests. See `client/README.md`.
- Everything else here tests the PHP side, against a real WordPress.

## Run the server tests

From the repository root:

```sh
tests/writenow/run.sh
```

That one command:

1. Downloads WordPress core and the SQLite database integration with shallow `git clone`s, the first time only. Both go into `~/.cache/writenow-tests/`, outside the repository.
2. Builds a fresh site in `~/.cache/writenow-tests/site-8881/` on every run. It runs on SQLite and uses the real `shortnotes` and `likes` plugins and the `writemore` theme, symlinked from their sibling checkouts (`../shortnotes`, `../likes`, `../writemore`). It also loads this repository's `mu-plugins/` and `advanced-cache.php` (Batcache) straight from the working tree. `WP_ENVIRONMENT_TYPE` is `local`, so Application Passwords work over plain HTTP.
3. Serves the site with PHP's built-in server on `127.0.0.1:8881`, using four workers so concurrent requests really are concurrent.
4. Runs every `server/test-*.php`. Some tests run in-process with `rest_do_request()`. Others make real HTTP requests to the local server with Basic auth.
5. Opens the real block editor in Chromium (Playwright), and checks that every converter fixture, and a note created through the API, load as valid blocks.
6. Fails if the site logged any PHP notice, warning or error. The one exception is the known PHP 8.2+ deprecations from the existing `advanced-cache.php`, which are counted and listed instead.

It exits non-zero if anything fails. Pass a filter to run only the PHP tests whose names contain it: `tests/writenow/run.sh like`.

### Requirements

- PHP 8.0 or later on the command line, with `pdo_sqlite`, `curl`, `gd` and `mbstring`.
- `git` and `curl`.
- Node with Playwright and a Chromium-based browser, for the block editor check. Without them that check is skipped, and the run says so. Set `CHROME_PATH` to pick the browser. Set `WRITENOW_SKIP_EDITOR=1` to skip the check on purpose.

### Settings

| Variable | Default | |
|---|---|---|
| `WP_VERSION` | `7.1.5` | Any tag of `github.com/WordPress/WordPress`. Also tested with `6.8.3`. |
| `SQLITE_VERSION` | `v3.1.3` | A tag of `github.com/WordPress/sqlite-database-integration`. |
| `WRITENOW_TEST_PORT` | `8881` | The site is built for this port. |
| `WRITENOW_TEST_CACHE` | `~/.cache/writenow-tests` | Where downloads and the site live. |

## Use the app against the local site

```sh
tests/writenow/run.sh --serve
```

This builds the same fresh site and keeps it running in the foreground:

- the app: `http://127.0.0.1:8881/writenow/`
- wp-admin: `http://127.0.0.1:8881/wp-admin/`, logging in as `admin` / `password`

Application Passwords for the `admin`, `editor`, `author`, `contributor` and `subscriber` test users are in `~/.cache/writenow-tests/site-8881/credentials.json`. They are generated on every build and exist only on that local site. Each build also deletes the previous site.

## What the tests cover

- `test-markdown.php`: every converter fixture in `fixtures/markdown.php`. For each one it checks the exact block markup, that `parse_blocks()` and `serialize_blocks()` return it unchanged, that it uses only blocks Shortnotes allows in a note, and the plain text Shortnotes' `transform_content()` makes of it for Mastodon. It also checks attribute escaping against core, and awkward input.
- `test-status.php`: `/status`, a 401 on every route without credentials, a 403 for under-privileged roles, and a 501 when a plugin is missing.
- `test-notes.php`: compares a WriteNow note field by field with one made through the block editor's own REST flow. Also covers the slug and title, meta in place before publish hooks, `client_id` idempotency (including a retry that finishes a half-done publish), replies, images (order, alt text, parent, validation), the multibyte title repair, the `editable` rules, and updates and conflicts.
- `test-likes.php`: meta and rendering through the Likes plugin, tracking-parameter stripping, dedupe (including Micropub's `mf2_like-of`), title lookup and every fallback (with outbound HTTP mocked), invalid URLs, and duplicate checks that bypass the query cache.
- `test-recent.php`: contents, order, statuses, `per_page`, and text lines.
- `test-http.php`: Basic auth end to end, including the note and like pages as the theme renders them. Also: no-cache headers, the real Batcache never storing or serving a private response (with a control showing Batcache does cache public pages here), concurrent duplicate requests creating one post, and the shell, manifest and service worker with their tokens filled.

## Files

- `run.sh`: the one command.
- `bin/setup-site.php`, `bin/install-site.php`: build and install the local site.
- `bin/router.php`: the router for PHP's built-in server.
- `bin/run-tests.php`: loads the site and runs the PHP tests.
- `lib/`: a deliberately small harness of assertions, an in-process REST client and an HTTP client.
- `support/test-helpers.php`: a test-site plugin. It answers outbound HTTP locally, so nothing leaves the machine, and gives the title lookup tests canned pages.
- `support/object-cache.php`: a test-only object cache that keeps Batcache's group in files. Production uses memcached, which is not available here.
- `editor/validate-blocks.cjs`: the block editor check.

Nothing here contacts the live site. Nothing here is needed at runtime.
