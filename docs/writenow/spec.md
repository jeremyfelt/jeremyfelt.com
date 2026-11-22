# WriteNow: spec

WriteNow is a small web app at `https://jeremyfelt.com/writenow/` that Jeremy adds to his iPhone home screen to post notes and likes to his own site. It ships with a JSON API that iOS Shortcuts call directly, so liking a URL from the share sheet never opens the app.

It replaces what Jeremy did on Android (a Micropub client for likes, the WriteWell app for notes) now that he uses an iPhone. "WriteNow" is a working name that sits beside the `writemore` theme and the `writewell` app. Renaming it later means changing one path constant and the display name.

The measure of success is the one from the WriteWell vision: the app treats Jeremy well with every interaction. It opens instantly, never loses text, never double-posts, and gets out of the way.

## 1. What exists today

Read these before writing code. Paths are relative to the directory that holds the repo checkouts.

| Repo | What it is | Relevant files |
|---|---|---|
| `jeremyfelt.com` | The site's `wp-content`. Deploys `mu-plugins/` to production by rsync on every push to `develop`, with no build step. | `mu-plugins/index.php`, `mu-plugins/plugins/micropub.php`, `.deploy_ignore`, `phpcs.xml`, `phpstan.neon.dist`, `advanced-cache.php` (Batcache) |
| `shortnotes` | Plugin providing the `shortnote` post type (archive at `/notes/`). | `includes/post-type-note.php`, `includes/share-on-mastodon.php`, `includes/webmention.php` |
| `likes` | Plugin providing the `like` post type (archive at `/liked/`). | `includes/post-type-like.php` |
| `writemore` | The active theme. Source of the visual language. | `style.css` (design tokens in `:root`), `fonts/` |
| `writewell` | The Android app WriteNow succeeds. Reference only. | `app/src/main/java/com/jeremyfelt/writewell/util/GutenbergConverter.kt`, `docs/` |

Facts the design depends on:

- **Notes.** `shortnote` is REST-enabled and uses the block editor. Shortnotes filters `wp_insert_post_data`: when a note reaches `publish` or `future` it generates the title from the first paragraph ("Note: …"). It only assigns its slug format (`substr( wp_generate_uuid4(), 0, 4 ) . time()`, giving URLs like `/notes/cde61764040689/`) when the incoming title is `Auto Draft`, which only the block editor produces. Replies use the registered meta `shortnotes_note_type`, `shortnotes_reply_to_url` and `shortnotes_reply_to_name`. On publish Shortnotes schedules webmentions, and `ShortNotes\PostType\Note\transform_content()` turns block markup into plain text for Share on Mastodon.
- **Likes.** `like` is REST-enabled but registered with `'supports' => false`, so the core REST controller can set neither a title nor the liked URL. The URL lives in post meta `like_of_url` (the Micropub path writes `mf2_like-of` instead; `get_like_url()` reads both). The Likes plugin renders the content and adds the URL to `webmention_links`.
- **Page cache.** Batcache caches GET responses for requests that carry no WordPress cookie, no `X-WP-Nonce` and no `Origin` header. It does not look at the `Authorization` header.
- **Deploy.** `jeremyfelt.com` rsyncs the repo to the server exactly as committed, minus `.deploy_ignore`. Anything a browser loads must be committed in its final form.
- **Auth precedent.** WriteWell authenticated with a WordPress Application Password. WriteNow does the same.

Verified against WordPress 6.8 core source while writing this spec:

- `wp_insert_post()` saves `meta_input` before it calls `wp_transition_post_status()`, so meta passed that way is in place when publish hooks run.
- `admin-ajax.php?action=rest-nonce` returns a fresh `wp_rest` nonce for a logged-in user.
- `POST /wp/v2/users/me/application-passwords` needs only `edit_user` on oneself and returns the new password in the response body.
- In `rest_cookie_check_errors()`, a REST request that carries a valid login cookie but no nonce is demoted to logged-out, and `wp_validate_application_password()` skips Basic auth when a cookie already identified the user. A request that sends both a login cookie and a Basic header therefore gets a 401.
- Application Passwords are only available over HTTPS or when `wp_get_environment_type()` is `local`.

Not verified, because the live site is unreachable from the authoring environment: which plugins are active in production, how Share on Mastodon is configured, and whether `like` posts currently send webmentions on publish.

## 2. Decisions

1. **Home.** The code lives in `jeremyfelt.com` at `mu-plugins/apps/writenow/`, loaded by one `require_once` in `mu-plugins/index.php`. The directory is self-contained so it can be lifted into a standalone plugin later. PHP namespace: `JeremyFelt\Apps\WriteNow`.
2. **No build step.** Plain PHP, hand-written CSS and JavaScript ES modules, no bundler, no framework, no runtime npm dependencies. Dev-only tooling (tests) is allowed and must be excluded from deploy.
3. **Authentication is an Application Password**, sent as HTTP Basic auth by both the web app and the Shortcuts. The app obtains one without the password ever appearing in a URL (section 5).
4. **Notes are written as light markdown and converted to block markup on the server.** One converter, in PHP, serves the app and the Shortcuts alike. The markdown source is stored in post meta `_markdown_content`, the key WriteWell uses.
5. **Likes get a purpose-built endpoint**, because the core controller cannot write them. It writes the same data wp-admin would (`like_of_url` plus a title), so the Likes plugin keeps rendering and webmention behaviour.
6. **The share-sheet Like is an iOS Shortcut that POSTs to the API.** iOS does not support the Web Share Target API for home screen web apps, and a Shortcut that opens a URL lands in Safari, not in the installed app.
7. **The app shell is identical for every visitor.** It contains no user data, nonce or secret, which makes it safe to cache in Batcache and in the service worker. Everything personal arrives over the API.
8. **Plugins stay untouched.** No changes to `shortnotes`, `likes` or `writemore`. WriteNow reaches them through their public functions and data, and guards every call with `function_exists()` or `post_type_exists()`.

Alternatives considered and set aside: posting through the existing Micropub endpoint (it stores `mf2_*` meta and renders through the Micropub plugin, and has no markdown path); a separate standalone plugin repo (needs its own deploy secrets, and nothing here is reusable by others yet); cookie-plus-nonce auth (nonces expire within a day, and iOS gives each home screen app its own cookie jar).

## 3. Scope

**In v1**

- Compose a note; post it or save it as a draft. Attach images. Mark it as a reply to a URL.
- Edit and publish drafts that were created in WriteNow.
- Like a URL from inside the app.
- Like a URL from the iOS share sheet with a Shortcut.
- Post a note from a Shortcut (dictation, Action Button, Siri).
- A Recent list of notes and likes with their status.
- Installable on the home screen; opens offline; queues posts made offline.

**Not in v1**

- Longform posts, the calendar and canvas model, and monthly collections from WriteWell.
- Editing published notes, or notes that have no stored markdown. Link to wp-admin instead.
- Deleting or unliking. Link to wp-admin instead.
- An undo window after posting. A delayed send would strand a note if the app is closed in the gap, and a real undo cannot recall webmentions or a Mastodon post. The Post button is deliberate and sends immediately.
- Push notifications, bookmarks, reposts, and syndication controls.
- Any change to how the site renders notes or likes.

## 4. Server

### 4.1 Layout

```
mu-plugins/apps/writenow/
  writenow.php            bootstrap: constants, requires
  includes/
    routes.php            /writenow/, manifest, service worker
    rest.php              route registration, permissions, cache headers
    notes.php             create, read, update notes
    likes.php             create likes, title lookup
    recent.php            the Recent list
    markdown.php          markdown to block markup (pure functions, no WordPress calls beyond escaping)
  app/                    everything the browser loads (section 6)
tests/writenow/           dev-only, at the repo root, excluded from deploy
```

File boundaries may shift if a better split emerges; the directory split between PHP and `app/` may not, because two people work in them at once.

Add `tests/`, `docs/`, `node_modules/`, `package.json` and `package-lock.json` to `.deploy_ignore` as each appears. `docs/` is already there.

### 4.2 Front-end routes

Served by PHP by matching the request path early (for example on `parse_request`), not by adding rewrite rules, so that deploying needs no permalink flush.

| Path | Response |
|---|---|
| `/writenow/` | The app shell: a complete HTML document that does not load the theme or call `wp_head()`. Sends `X-Robots-Tag: noindex`. `/writenow` redirects to `/writenow/`. |
| `/writenow/manifest.webmanifest` | The web app manifest, `application/manifest+json`. |
| `/writenow/sw.js` | The service worker, `text/javascript`, `Cache-Control: no-cache`. Served from this path so its scope can be `/writenow/`. PHP injects a version string derived from the modification times of the files in `app/`. |

Static files (CSS, JS, icons) are served by the web server straight from the `app/` directory URL, with a version query string from `filemtime()`.

The shell (`app/shell.html`), manifest (`app/manifest.webmanifest`) and service worker (`app/sw.js`) are plain files with `{{NAME}}` tokens. The full list, and how each must be escaped, is in the comment at the top of `app/shell.html` and `app/sw.js`. One `ASSET_VERSION` value covers everything in `app/` and must be identical in all three files: the service worker compares the shell's version to its own.

The shell may reference the theme's font files through `get_theme_file_uri()` when the active theme has them, and must fall back to the system font stack when it does not.

### 4.3 REST API

Namespace `writenow/v1`. JSON in, JSON out. Every route requires an authenticated user.

**Rules for all routes**

- Permission checks use the capabilities of the post type involved (`get_post_type_object( … )->cap->publish_posts`, `edit_post` for a specific note). Unauthenticated requests get `rest_authorization_required_code()`.
- Responses must never enter the page cache: send no-cache headers and call `batcache_cancel()` when it exists.
- Errors use the standard `WP_Error` REST shape with a sentence in `message` that can be shown to Jeremy unchanged.
- Sanitize on the way in, escape on the way out. Liked and reply URLs must be `http` or `https`.

#### `GET /status`

Confirms the credential and tells the client what is available.

```json
{
  "user": { "id": 1, "name": "Jeremy Felt" },
  "site": { "name": "Jeremy Felt", "url": "https://jeremyfelt.com" },
  "features": { "notes": true, "likes": true },
  "note_soft_limit": 500,
  "version": "1.0.0"
}
```

`features.notes` is false when the `shortnote` post type does not exist, and likewise `features.likes`. Routes for a missing feature return `501` with code `writenow_feature_unavailable`.

#### `POST /likes`

Request: `{ "url": "https://example.com/post", "title": "Optional title" }`

1. Trim the URL; reject anything that is not a valid `http(s)` URL with `400 writenow_invalid_url`.
2. Remove tracking parameters (`utm_*`, `fbclid`, `gclid`, `mc_cid`, `mc_eid`, `igshid`). Keep everything else, including the fragment. Make the list filterable.
3. If a published like for this URL already exists (match `like_of_url` and `mf2_like-of`), return it with `200` and `"duplicate": true`. Do not create a second one.
4. Title: use the one supplied. Otherwise fetch the URL with `wp_safe_remote_get()` (5 second timeout, response size capped) and take `og:title`, then `<title>`, decoded and trimmed to 200 characters. If the fetch fails for any reason, use the URL. A title failure never fails the like.
5. Create the post with `wp_insert_post()`: type `like`, status `publish`, the title, `meta_input` carrying `like_of_url`. Passing the meta through `meta_input` matters: it must be saved before publish hooks run so anything that sends webmentions sees the URL.
6. Do not add webmention or syndication logic. Whatever happens when a like is published in wp-admin should happen here, no more.

Response, `201` when created:

```json
{
  "id": 123,
  "url": "https://example.com/post",
  "title": "A post title",
  "link": "https://jeremyfelt.com/liked/a-post-title/",
  "duplicate": false,
  "message": "Liked: A post title"
}
```

`message` exists so a Shortcut can show the result in a notification without building a string. For a duplicate it reads "Already liked: A post title".

#### `POST /notes`

Request:

```json
{
  "content": "Markdown text",
  "status": "publish",
  "reply_to_url": "",
  "reply_to_name": "",
  "media": [ { "id": 456, "alt": "A description" } ],
  "client_id": "a-uuid-generated-by-the-client"
}
```

- `status` is `publish` or `draft`. Default `draft`, so a malformed request cannot publish by accident.
- A note needs non-blank `content` or at least one media item, else `400 writenow_empty_note`.
- **Idempotency.** When `client_id` is present, store it in meta `_writenow_client_id`. If a note with that `client_id` already exists, return it with `200` instead of creating another. The offline queue depends on this.
- **Slug and title.** Set `post_name` to `substr( wp_generate_uuid4(), 0, 4 ) . time()` and `post_title` to Shortnotes' placeholder title, matching what Shortnotes does for editor-created notes. Shortnotes replaces the title when the note is published.
- **Content.** Convert the markdown to block markup (section 4.4). Append each media item as a `core/image` block after the text, in the order given, with its alt text. Set each attachment's `post_parent` to the note and save its alt text. Media ids must be image attachments the user may edit.
- **Meta.** `_markdown_content` holds the markdown exactly as received. When `reply_to_url` is set, also save `shortnotes_reply_to_url`, `shortnotes_reply_to_name` and `shortnotes_note_type` = `reply`. Pass all of it through `meta_input`.
- Create with `wp_insert_post()` so Shortnotes' own filters and publish hooks run.

Response, `201` when created:

```json
{
  "id": 789,
  "status": "publish",
  "title": "Note: The first words of the note…",
  "link": "https://jeremyfelt.com/notes/ab121764040689/",
  "edit_link": "https://jeremyfelt.com/wp-admin/post.php?post=789&action=edit",
  "editable": true,
  "message": "Posted."
}
```

`message` is "Posted." or "Saved as a draft."

#### `GET /notes/<id>`

Returns what the composer needs to reopen a note: `id`, `status`, `content` (the stored markdown), `reply_to_url`, `reply_to_name`, `media` (`[ { "id": 456, "alt": "…", "thumbnail": "https://…" } ]`), `link`, `edit_link`, `modified`, and `editable`. `editable` is true only when the note is a draft and has `_markdown_content`.

#### `POST /notes/<id>`

Same body as create. Allowed only when `editable` is true, else `409 writenow_not_editable` with a message that points to wp-admin. Publishing a draft is an update with `"status": "publish"`. Regenerates the block markup and replaces the stored markdown.

#### `GET /recent`

`?per_page=20` (maximum 50). Notes in status `draft`, `future` or `publish`, and published likes, merged and ordered by date descending.

```json
[
  {
    "type": "note",
    "id": 789,
    "status": "draft",
    "date": "2026-11-22T18:45:00+00:00",
    "text": "Plain-text excerpt, 140 characters at most",
    "link": "https://jeremyfelt.com/?post_type=shortnote&p=789",
    "edit_link": "https://jeremyfelt.com/wp-admin/post.php?post=789&action=edit",
    "editable": true
  },
  {
    "type": "like",
    "id": 123,
    "status": "publish",
    "date": "2026-11-22T17:02:00+00:00",
    "text": "A post title",
    "url": "https://example.com/post",
    "link": "https://jeremyfelt.com/liked/a-post-title/",
    "edit_link": "https://jeremyfelt.com/wp-admin/post.php?post=123&action=edit"
  }
]
```

#### Media

Images upload through core, `POST /wp/v2/media`, with the same credential. No custom route.

### 4.4 Markdown to blocks

Input is what someone types on a phone. Output must be block markup the block editor opens without a validation warning, and that `ShortNotes\PostType\Note\transform_content()` turns into sensible plain text.

| Markdown | Block |
|---|---|
| Text separated by blank lines | `core/paragraph`. A single newline inside a paragraph becomes `<br>`. |
| `**bold**`, `*italic*` or `_italic_`, `` `code` `` | `<strong>`, `<em>`, `<code>` inline |
| `[text](https://…)` | `<a href>` |
| A bare URL inside text | Linked, with the URL as its text |
| A known oEmbed URL alone on its line (YouTube, Spotify, Bandcamp, Vimeo, Bluesky, Mastodon-style status URLs) | `core/embed` with the right provider attributes. Use `wp_oembed_get`-style provider detection where practical. |
| Any other URL alone on its line | A paragraph containing the link |
| Lines starting `- ` or `* ` | `core/list` with `core/list-item` inner blocks |
| Lines starting `1. ` | `core/list` (ordered) |
| Lines starting `> ` | `core/quote` wrapping paragraph blocks |
| Fenced code with three backticks | `core/code` |

- A line that starts with `#` is ordinary text. Notes have no headings, and hashtags are far more likely.
- All user text is HTML-escaped. Raw HTML in the input is shown as text, not rendered.
- Anything unrecognised falls back to a paragraph. The converter never throws and never drops text.

`GutenbergConverter.kt` in `writewell` shows the block shapes WriteWell emitted. Use it as a reference for output, not as a design to port.

### 4.5 Server tests

In `tests/writenow/`, runnable with one documented command.

- **Converter unit tests**: a table of markdown fixtures and expected block markup, covering every row above plus edge cases (empty input, only whitespace, emoji, unbalanced markers, a URL with parentheses, HTML in the input, a hashtag at line start). Each expected output must also pass through `parse_blocks()` and re-serialize unchanged.
- **Integration tests against real WordPress** with the `shortnotes` and `likes` plugins from the sibling checkouts active: each route's happy path; unauthenticated and under-privileged requests; like dedupe; tracking-parameter stripping; title fallback when the fetch fails (mock HTTP with `pre_http_request`); note idempotency on `client_id`; slug format; title generation on publish; reply meta; media attached and parented; `editable` rules; the result of `transform_content()` on each converter fixture.
- `composer phpcs` and `composer phpstan` pass for the new code under the repo's existing configs.

## 5. Authentication

**Connecting the app**

1. On launch the app looks for a stored credential and checks it with `GET /status`.
2. With none stored, it shows a single Connect button. Tapping it asks `admin-ajax.php?action=rest-nonce` (with cookies) for a nonce.
3. If that shows Jeremy is not logged in, the app navigates to `wp-login.php` with `redirect_to` set to `/writenow/?connect=1`, and resumes when it returns.
4. With a nonce, the app reads its username from `GET /wp/v2/users/me?context=edit` (the application-passwords response does not include it), then calls `POST /wp/v2/users/me/application-passwords` (cookies plus `X-WP-Nonce`) with the name `WriteNow` and a date, and stores the username and returned password in `localStorage`.
5. From then on every API request sends `Authorization: Basic …` and is made with `credentials: 'omit'`.

`credentials: 'omit'` is required, not a nicety. A request that carries both the login cookie and a Basic header is rejected by WordPress (section 1).

Settings also offers a manual path: paste a username and an Application Password created in the WordPress profile screen. This is the fallback if the automatic flow misbehaves on a device.

A `401` from the API at any point clears the "connected" state, keeps all local text and queued items, and shows Connect again. Disconnect, in Settings, removes the stored credential and offers a link to the profile screen to revoke it.

**Shortcuts** use their own Application Password, so it can be revoked on its own. The Settings screen creates one named `WriteNow Shortcut` through the same core route, and shows the ready-made `Authorization` header value once, with a copy button.

**Security notes**

- The credential is stored in `localStorage` on the phone and is readable by any script on the `jeremyfelt.com` origin. That is the accepted trade for a single-author site. The app loads no third-party script, and the shell sends a Content-Security-Policy that allows scripts only from its own origin.
- The password must never appear in a URL, a log line, an error message or the service worker cache.
- The service worker never caches `/wp-json/` or `/wp-admin/` responses.

## 6. Client

### 6.1 Files

Everything in `mu-plugins/apps/writenow/app/`: the shell template, one stylesheet, ES modules split by concern (API client, credential store, local draft store, outbox, one module per view), the service worker source, the manifest source, and icons.

The API client is the only module that knows about HTTP. Tests replace the server by intercepting its requests.

### 6.2 Design

It should feel like a quiet native app that belongs to jeremyfelt.com, not like a web page and not like a WordPress admin screen.

- **Tokens** come from `writemore/style.css`: the Inter and Monaspace fonts, the light and dark colour values, the link blue, the spacing scale. Follow `prefers-color-scheme`.
- **Type**: 17px or larger for anything typed (below 16px iOS zooms the page on focus). Generous line height in the composer.
- **Touch**: targets at least 44 by 44 points. No hover-only affordances. No double-tap zoom on controls.
- **Layout**: `viewport-fit=cover` with safe-area insets respected on every edge. Use dynamic viewport units. Single column, capped near 640px wide and centred on larger screens.
- **Keyboard**: the composer's action bar stays directly above the on-screen keyboard (track `visualViewport`). The page behind does not scroll or bounce while typing.
- **Motion**: short and purposeful, and removed under `prefers-reduced-motion`.
- **Feedback**: every action acknowledges itself within 100ms. Status appears inline, near the control that caused it, in plain sentences. No modal alerts, no spinners that block the screen.
- **Accessibility**: real labels, visible focus, VoiceOver-friendly names and live-region announcements for posting results, WCAG AA contrast in both colour schemes.

### 6.3 Views

A bottom tab bar with Note, Like and Recent, and a gear for Settings. The tab bar hides while the keyboard is open.

**Note** (the default view)

- A text area that fills the screen and grows with the text. Placeholder: "What's on your mind?" Request focus on open, and make the whole empty area tappable since iOS may refuse programmatic focus.
- Action bar: add image, reply, a character count, Save draft, Post.
- **Local draft**: text, reply fields and attached image references are saved to the device on every change (debounced) and restored on next launch, including after iOS discards the app. Text is cleared only after the server confirms.
- **Count**: appears past 400 characters and changes colour past the `note_soft_limit` from `/status` (500, the usual Mastodon limit, since notes are shared there). It never blocks posting.
- **Reply**: reveals a URL field and an optional name field.
- **Images**: one file input accepting images, multiple allowed, which gives the iOS photo library and camera choices. Before upload, re-encode each through a canvas to JPEG with the long edge capped at 2400px. That respects orientation, keeps uploads small on mobile data, and removes location metadata. Show a thumbnail with per-image progress, an alt text field, and a remove button. Posting waits for uploads to finish.
- **Post / Save draft**: the bar shows progress, then the composer clears and a quiet confirmation appears with a View link (published) or an "in Recent" pointer (draft). On failure nothing is lost and the reason is shown with a Retry.
- Opening a draft from Recent loads it here with a banner naming it and a Discard changes option; saving updates that note.

**Like**

- A URL field (`type="url"`), a Paste button that reads the clipboard only when tapped, an optional title field, and a Like button.
- The result shows the resolved title and a View link. A duplicate says so without alarm.

**Recent**

- Queued items first, marked as waiting, each with Retry and Remove.
- Then the `/recent` list: type, status (Draft, Scheduled, or nothing for published), relative time, the text line. Tapping an editable draft opens it in Note. Tapping anything else opens its public link. Each row has a secondary action to open the wp-admin edit screen.
- Refreshes when the view opens and on pull-down. Shows the last loaded list immediately while refreshing, and shows it with a notice when offline.

**Settings**

- Who is connected and to which site; Connect or Disconnect; the manual credential form.
- "Set up the Like shortcut": the endpoint URL and `Authorization` header with copy buttons, and the steps from section 7.
- App version, and a link to wp-admin.

**Deep links**: `/writenow/?like=<url>&title=<text>` opens Like prefilled, and `/writenow/?text=<text>` opens Note prefilled. Both strip the query from the address afterwards. They make a desktop bookmarklet possible.

### 6.4 Installability and offline

- Manifest: name and short name "WriteNow", `display: standalone`, `start_url` and `scope` both `/writenow/`, theme and background colours, icons at 192 and 512 plus a maskable variant.
- The shell also carries the iOS specifics: a 180px `apple-touch-icon`, the mobile-web-app-capable metas, a status bar style, and `theme-color` metas for light and dark.
- Icon: simple and legible at small sizes, drawn as SVG and exported to the PNG sizes. Committed, not generated at deploy.
- **Service worker**: precaches the shell and static assets. The shell is network-first with a 2 second timeout that falls back to cache; versioned static assets are cache-first. A new version activates on the next launch, never mid-session.
- **Outbox**: when a post, draft save or like cannot reach the server, it is stored in IndexedDB with its `client_id` and shown in Recent as waiting. The outbox flushes on launch, when the app returns to the foreground, and on the `online` event, oldest first. iOS has no Background Sync, so say "will send when you next open WriteNow" rather than promising background delivery. A `4xx` response other than `401` and `429` marks the item failed with the server's message; it is not retried automatically.
- Images added offline are kept as blobs in IndexedDB with the queued note and uploaded when the outbox flushes.

### 6.5 Client tests

Playwright, using the Chromium already in the environment, at an iPhone-sized viewport with touch enabled, against the real shell with the API intercepted and answered according to section 4.3.

Cover: first run and Connect (both the automatic and the manual path); post a note; save, reopen, edit and publish a draft; local draft surviving a reload; image attach, alt text and removal; reply fields; like, and duplicate like; Recent in each state; offline queueing and flush on reconnect, with a single request per item; a `401` returning to Connect without losing text; deep links; light and dark; an automated accessibility scan with no serious violations.

Take screenshots of each view in both colour schemes and look at them. A view that passes tests but looks broken is not done.

Chromium is not Safari. Section 9 lists what only a real iPhone can confirm.

## 7. Shortcuts

Agents cannot build or run iOS Shortcuts. The deliverable is a recipe Jeremy follows once, in `docs/writenow/shortcuts.md` and mirrored in the Settings screen. Describe each action by what it does and name the Shortcuts action as it appears in current iOS, and say plainly that the names were not checked on a device.

**Like** (appears in the share sheet)

1. Accept URLs and Safari web pages from the share sheet. If there is no input, use the clipboard.
2. Get the URL from the input.
3. Get Contents of URL: `POST https://jeremyfelt.com/wp-json/writenow/v1/likes`, header `Authorization: Basic …` (copied from Settings), JSON body with `url`. Leave `title` out and let the server look it up.
4. Read `message` from the response and show it as a notification.

**Note** (for the Action Button, Siri, or the Lock Screen)

1. Ask for text, or dictate it.
2. Get Contents of URL: `POST …/writenow/v1/notes` with the same header and a JSON body of `content` and `status`.
3. Show `message` as a notification.

Offer two variants in the recipe: one that saves a draft and one that publishes. Recommend the draft variant for dictation.

Include `curl` equivalents so the endpoints can be checked from a laptop before building the Shortcut.

## 8. Constraints

- **Pushing to `develop` in `jeremyfelt.com` deploys to production.** Work on the branch you are given. Never push to `develop`, and do not open a pull request unless asked.
- Do not modify `shortnotes`, `likes`, `writemore`, `writewell`, `advanced-cache.php` or `object-cache.php`.
- Do not send anything to the live site. There are no production credentials in this work, and none should be asked for.
- Commit nothing secret. Test credentials are generated at test time.
- PHP must run on the version the repo targets (`composer.json` sets the platform to 8.0; `phpcs.xml` checks 7.4 and up).

**Test environment.** In the authoring environment, `wordpress.org` downloads and GitHub archive downloads are blocked and Docker has no running daemon, so `wp-env` and WordPress Playground are not options. What does work: Composer installs `johnpbloch/wordpress-core` and `wp-phpunit/wp-phpunit` (it falls back to git), `git clone https://github.com/WordPress/sqlite-database-integration.git` works, PHP 8.3 has the SQLite extensions, and Chromium with Playwright is installed. So real WordPress can run locally on SQLite behind `php -S`, with `WP_ENVIRONMENT_TYPE` set to `local` so Application Passwords work over HTTP. If your environment differs, use what works and say what you used.

## 9. What only Jeremy can check

Nothing in sections 4 to 7 can be proven on iOS by an agent. The final handoff includes this checklist for a real iPhone:

1. Add to Home Screen from Safari; the icon, name and splash look right; the app opens without browser chrome.
2. Connect: the trip to `wp-login.php` and back stays inside the app and ends connected. If it does not, the manual path in Settings works.
3. The connection survives closing the app, restarting the phone, and a week of not opening it.
4. The keyboard does not cover the action bar; the page does not jump or zoom on focus.
5. Photo library and camera both work; a HEIC photo uploads; the posted image has no location data.
6. Post a note, then confirm on the site: URL format, title, markup, and whether it reached Mastodon as expected.
7. Like from inside the app, then from the share sheet in Safari and in one other app.
8. Airplane mode: write a note, post, reopen with a connection, and see exactly one note published.
9. Dark mode, and Dynamic Type at a large size.

## 10. Open questions for Jeremy

Each has a default that the build follows unless he says otherwise.

1. **Name and path.** Default: "WriteNow" at `/writenow/`.
2. **Mastodon.** Should a note published from WriteNow be shared to Mastodon? Default: do nothing special, so it behaves like any other programmatically published note. If production differs from what he wants, the fix is a small follow-up once the first real note shows what happens.
3. **Tracking parameters.** Default: strip them from liked URLs.
4. **`_markdown_content`.** No code in these repos registers this meta key, so WriteWell's copies may never have been saved by the REST API. WriteNow writes it directly and does not depend on registration. Worth knowing if WriteWell's stored markdown was ever relied on.
5. **Webmentions for likes.** The standalone Likes plugin registers the post type with no `webmentions` support, so it is unclear whether publishing a like sends one today. WriteNow preserves current behaviour either way.
