# WriteNow: Shortcuts

Two iOS Shortcuts that post to jeremyfelt.com without opening WriteNow:

- **Like**, in the share sheet: like the page you are looking at.
- **Note**, for the Action Button, Siri or the Lock Screen: dictate a note and save it as a draft (or publish it).

Each one is a single web request to the WriteNow API with an Application Password. You build them once, by hand, in the Shortcuts app. The same steps are in WriteNow under **Settings → Shortcuts → Set up the Like shortcut**.

> **These steps have not been checked on a device.** They were written without access to an iPhone. The action names are the ones current iOS uses as far as the author knows, but a name, a menu or an option may be slightly different on your phone. If a step does not match what you see, look for the action that does the same job. The requests themselves have been checked: every `curl` command below was run against a local copy of the site, and the responses shown are what came back.

## Before you start: a password for Shortcuts

Shortcuts get their own Application Password, separate from the one the WriteNow app uses, so you can revoke one without breaking the other.

1. Open WriteNow and go to **Settings**.
2. Open **Set up the Like shortcut** and tap **Create Shortcut password**. This creates an Application Password named **WriteNow Shortcut** on your site.
3. A field appears with the **Authorization header value**. It starts with `Basic ` followed by a long string of letters and numbers. Tap **Copy**.
4. Keep it somewhere you can paste from while you build the shortcuts (for example, a note you delete afterwards). WriteNow shows it once; when you leave Settings it is gone. If you lose it, create another one and revoke the old one under **Users → Profile → Application Passwords** in wp-admin.

Treat the header value like a password: anyone who has it can post to your site.

The two addresses you need, also shown in Settings with copy buttons:

- Like: `https://jeremyfelt.com/wp-json/writenow/v1/likes`
- Note: `https://jeremyfelt.com/wp-json/writenow/v1/notes`

## The Like shortcut

What it does: takes the URL you share, sends it to your site, and shows the site's answer ("Liked: A post title", or "Already liked: A post title") as a notification. Your site looks up the page's title itself.

1. Open **Shortcuts**, tap **+** to create a new shortcut, and name it **Like** (tap the name at the top to rename it).
2. Tap the **ⓘ** (Details) button and turn on **Show in Share Sheet**. Back in the editor, the first block now reads "Receive **Any** input from **Share Sheet**". Tap **Any** and choose only **URLs** and **Safari web pages**. Tap the "If there's no input" option and choose **Get Clipboard**, so the shortcut also works from the Shortcuts app with a URL you copied.
3. Add the action **Get URLs from Input**. Its input should be **Shortcut Input**.
4. Add the action **Get Item from List** and set it to **First Item**. A page sometimes shares more than one URL, and the site expects exactly one.
5. Add the action **Get Contents of URL**.
   - Set the URL to `https://jeremyfelt.com/wp-json/writenow/v1/likes`.
   - Tap **Show More**. Set **Method** to **POST**.
   - Under **Headers**, tap **Add new header**. Key: `Authorization`. Value: paste the header value from Settings (it begins with `Basic `).
   - Set **Request Body** to **JSON**. Tap **Add new field**, choose **Text**, set the key to `url` and the value to the **Item from List** variable (tap the value, then pick the variable from the bar above the keyboard).
   - Do not add a `title` field. Leave it out and the site looks the title up.
6. Add the action **Get Dictionary Value**. Set it to get the **Value** for the key `message` in **Contents of URL**.
7. Add the action **Show Notification** and set its text to **Dictionary Value**.

Try it: in Safari, open any article, tap the Share button, and choose **Like** (you may need to scroll the list of actions, or tap **Edit Actions** to add it as a favourite). A notification should say "Liked: …". Share the same page again and it should say "Already liked: …", and no second like is created.

If something is wrong, the notification should show the site's explanation instead (Get Contents of URL passes an error response through like any other), for example "That does not look like a web address. Liked URLs start with http:// or https://." If there is no connection at all, Shortcuts shows its own error and nothing is sent; run the shortcut again later. (Unlike the app, a Shortcut has no outbox.)

## The Note shortcut

What it does: asks for text, sends it to your site, and shows "Saved as a draft." or "Posted." as a notification.

Build one of two variants. **The draft variant is recommended for dictation**: dictation makes mistakes, and a draft waits in WriteNow's Recent list (and in wp-admin) for you to read it, fix it and publish it. The publish variant posts at once, which also means it goes wherever a published note goes (webmentions, and Mastodon if your site shares notes there).

1. Create a new shortcut and name it **Note** (or **Draft note**).
2. Add the action **Dictate Text** to speak the note, or **Ask for Input** (type: Text, prompt: "Note") to type it.
3. Add the action **Get Contents of URL**.
   - URL: `https://jeremyfelt.com/wp-json/writenow/v1/notes`
   - Tap **Show More**. **Method**: **POST**.
   - **Headers**: `Authorization` set to the same header value as the Like shortcut.
   - **Request Body**: **JSON**, with two **Text** fields:
     - `content` set to **Dictated Text** (or **Provided Input**).
     - `status` set to `draft` for the draft variant, or `publish` for the publish variant. If `status` is missing or misspelled, the site saves a draft; it never publishes by accident.
4. Add **Get Dictionary Value** for the key `message` in **Contents of URL**.
5. Add **Show Notification** with **Dictionary Value**.

To run it from the Action Button: **Settings → Action Button**, swipe to **Shortcut**, and choose **Note**. To run it with Siri, say the shortcut's name ("Hey Siri, Note"). To run it from the Lock Screen, add the Shortcuts widget or a Lock Screen control for it.

Text from the shortcut is treated as the same light markdown the app uses: a blank line starts a new paragraph, `- ` starts a list, a URL on its own line becomes an embed when it is a known provider. A line that starts with `#` stays as text, so dictated hashtags are safe.

Notes made by a Shortcut have no `client_id`, so running the shortcut twice makes two notes. That is deliberate: each run is a new note.

## Check the endpoints from a laptop first

These `curl` commands do exactly what the shortcuts do. Run them before building a shortcut to make sure the header value works and the server accepts Basic authentication.

Set two variables first. Paste the header value from Settings into `AUTH` (everything, including `Basic `):

```sh
SITE=https://jeremyfelt.com
AUTH='Basic …paste the value from Settings…'
```

Check the credential (read-only, posts nothing):

```sh
curl -sS "$SITE/wp-json/writenow/v1/status" -H "Authorization: $AUTH"
```

```json
{"user":{"id":1,"name":"admin"},"site":{"name":"WriteNow Test Site","url":"http:\/\/127.0.0.1:8881"},"features":{"notes":true,"likes":true},"note_soft_limit":500,"version":"1.0.0"}
```

On your site the name, site and URL will be yours. If this says `"code":"writenow_not_connected"` with a 401, the header value is wrong or has been revoked. If it is a 401 with a correct value, the web server may not be passing the `Authorization` header to PHP; that would stop the app and the shortcuts alike, and needs a server configuration change.

Like a URL (this one creates a real like, so use a page you mean to like). Tracking parameters such as `utm_source` are removed before it is stored:

```sh
curl -sS -X POST "$SITE/wp-json/writenow/v1/likes" \
  -H "Authorization: $AUTH" -H "Content-Type: application/json" \
  -d '{"url":"https://titles.test/title?for=docs&utm_source=mastodon"}'
```

```json
{"id":153,"url":"https:\/\/titles.test\/title?for=docs","title":"Just a title — here","link":"http:\/\/127.0.0.1:8881\/liked\/just-a-title-here-3\/","edit_link":"http:\/\/127.0.0.1:8881\/wp-admin\/post.php?post=153&action=edit","duplicate":false,"message":"Liked: Just a title — here"}
```

That is a `201`. Send the same URL again and nothing new is created; the answer is a `200` with `"duplicate":true` and `"message":"Already liked: Just a title — here"`.

Save a dictated note as a draft:

```sh
curl -sS -X POST "$SITE/wp-json/writenow/v1/notes" \
  -H "Authorization: $AUTH" -H "Content-Type: application/json" \
  -d '{"content":"Dictated on a walk.","status":"draft"}'
```

```json
{"id":151,"status":"draft","title":"Note","content":"Dictated on a walk.","reply_to_url":"","reply_to_name":"","media":[],"link":"http:\/\/127.0.0.1:8881\/?post_type=shortnote&p=151","edit_link":"http:\/\/127.0.0.1:8881\/wp-admin\/post.php?post=151&action=edit","modified":"2026-11-22T20:26:31+00:00","editable":true,"message":"Saved as a draft."}
```

Publish a note (this one goes live):

```sh
curl -sS -X POST "$SITE/wp-json/writenow/v1/notes" \
  -H "Authorization: $AUTH" -H "Content-Type: application/json" \
  -d '{"content":"Posted from curl.","status":"publish"}'
```

```json
{"id":152,"status":"publish","title":"Note: Posted from curl.","content":"Posted from curl.","reply_to_url":"","reply_to_name":"","media":[],"link":"http:\/\/127.0.0.1:8881\/notes\/31531795379191\/","edit_link":"http:\/\/127.0.0.1:8881\/wp-admin\/post.php?post=152&action=edit","modified":"2026-11-22T20:26:31+00:00","editable":false,"message":"Posted."}
```

What errors look like (each is a `400` with a `message` the notification shows as it is):

```sh
curl -sS -X POST "$SITE/wp-json/writenow/v1/notes" \
  -H "Authorization: $AUTH" -H "Content-Type: application/json" -d '{"content":"   "}'
# {"code":"writenow_empty_note","message":"Write something or add an image before posting.","data":{"status":400}}

curl -sS -X POST "$SITE/wp-json/writenow/v1/likes" \
  -H "Authorization: $AUTH" -H "Content-Type: application/json" -d '{"url":"not a url"}'
# {"code":"writenow_invalid_url","message":"That does not look like a web address. Liked URLs start with http:\/\/ or https:\/\/.","data":{"status":400}}
```

If the shortcut sends a list of URLs instead of one (step 4 of the Like recipe prevents this), the site answers `"message":"Invalid parameter(s): url"`.

The responses above came from a local test site at `http://127.0.0.1:8881` with test data (the `titles.test` pages are canned pages the test site answers itself). On jeremyfelt.com the addresses, IDs and titles will differ, but the shape and the messages are the same.

## Revoking

To stop the shortcuts working, revoke **WriteNow Shortcut** under **Users → Profile → Application Passwords** in wp-admin. The WriteNow app keeps working, because it has its own password (named "WriteNow" and the date it connected).
