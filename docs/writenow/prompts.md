# WriteNow: agent prompts

Three briefs for building WriteNow from `docs/writenow/spec.md`. Each is written to be handed to a fresh agent as-is.

## How to run them

- **Server** and **Client** touch disjoint paths and can run at the same time, each in its own worktree or branch of `jeremyfelt.com`. Running them one after the other in a single checkout also works and is simpler.
- **Integration** runs after both are merged into one branch.
- All seven repos need to be checked out side by side, as they are in the session that wrote the spec. The agents read `shortnotes`, `likes`, `writemore` and `writewell`, and change only `jeremyfelt.com`.
- Replace `<branch>` with the branch each agent should commit to. It must not be `develop`: a push to `develop` deploys to production.

---

## Prompt 1: Server

You are building the server half of WriteNow, a small web app that lets Jeremy Felt post notes and likes to his WordPress site, jeremyfelt.com, from his iPhone. He recently moved from Android, where he had a comfortable setup for this, and wants something that feels at least as good. The site is his personal home on the web and is live, so care matters more than speed.

The full design is in `jeremyfelt.com/docs/writenow/spec.md`. Read all of it before you start. It records what the existing code does, the decisions already made, and why. Then read the files it lists in section 1, especially `shortnotes/includes/post-type-note.php` and `likes/includes/post-type-like.php`, because your code has to produce exactly the data those plugins expect.

**Your part** is sections 4 and 5 of the spec as they apply to PHP: the mu-plugin bootstrap, the three front-end routes, the `writenow/v1` REST API, the markdown-to-blocks converter, and the server tests. Someone else is building the browser side in `mu-plugins/apps/writenow/app/` at the same time and is coding against the API exactly as section 4.3 describes it. Treat that section as a contract: if you find a reason it has to change, make the smallest change you can, update the spec in the same commit, and call it out at the top of your report.

For the routes that serve the shell, manifest and service worker, read the files from `app/` if they exist and serve a minimal placeholder if they do not, so that your work stands on its own before the client lands.

**What good looks like**

- A note or like created through the API is indistinguishable, in the database and on the site, from one Jeremy would have made in wp-admin: same slug format, same title behaviour, same meta, same hooks fired.
- Block markup opens in the block editor with no validation warning. The spec asks you to prove this by round-tripping through `parse_blocks()`.
- Nothing an authenticated request returns can end up in the page cache. Section 1 of the spec explains how Batcache decides.
- A retried request never creates a second post.
- The code reads like the rest of `mu-plugins/`: namespaced functions, hooks registered at the top of each file, doc blocks, WordPress coding standards.

**Constraints**

- Work in `jeremyfelt.com` on the branch `<branch>`. Never push to `develop`; that deploys to production. Do not open a pull request.
- Change nothing in the other repos, and nothing in `advanced-cache.php` or `object-cache.php`.
- No build step and no runtime dependencies. Dev-only tooling goes under `tests/writenow/` and must be added to `.deploy_ignore`.
- Never contact the live site.

**Verifying your work**

Run the tests against real WordPress, not only against mocks. Section 8 of the spec describes what is and is not reachable in this kind of environment and a route that is known to work (WordPress core through Composer, the SQLite drop-in through git, PHP's built-in server). Activate the real `shortnotes` and `likes` plugins from the sibling checkouts so you are testing against the code that runs in production. Make the whole suite runnable with one command and document it in `tests/writenow/README.md`. Run `composer phpcs` and `composer phpstan` and leave your code clean under both.

If something cannot be set up, say so and say what you verified another way. Do not report a test as passing unless you ran it and saw it pass.

**Report back with**

1. What you built, as a short list of files and what each does.
2. Any place you departed from the spec, and why.
3. The exact commands you ran and their results, with failures included.
4. Anything you could not verify, stated as such.
5. Anything in the existing plugins or site code that surprised you and that Jeremy should know about.

Commit in small, described steps and push to `<branch>`.

---

## Prompt 2: Client

You are building the browser half of WriteNow, a web app that Jeremy Felt will add to his iPhone home screen to post notes and likes to his own site, jeremyfelt.com. He recently moved from Android, where posting a thought took a few seconds and felt pleasant. That is the bar. He has said he wants the app to treat him well with every interaction: open instantly, never lose what he typed, never post twice, and stay out of the way.

The full design is in `jeremyfelt.com/docs/writenow/spec.md`. Read all of it first. **Your part** is section 6, plus the client side of section 5 (connecting) and the in-app Shortcut setup panel from section 7. Everything you write lives in `jeremyfelt.com/mu-plugins/apps/writenow/app/`.

Someone else is building the PHP side at the same time. You will not have a running server, so build against the API exactly as section 4.3 and section 5 describe it, and test by intercepting requests. If the contract seems wrong or is missing something you need, do not work around it silently: make the smallest change to the spec, in the same commit, and call it out at the top of your report. The PHP side serves your shell template, manifest and service worker source from `/writenow/`; write them as plain files it can read and send, and keep anything that needs a PHP-supplied value (asset URLs and versions, the REST root, font URLs) to a short, clearly marked list of placeholders at the top of the shell, documented in a comment so the other side can fill them.

**What good looks like**

- It feels like a quiet native app that belongs to jeremyfelt.com. The visual language comes from `writemore/style.css`: read its `:root` tokens and use those fonts and colours, in light and dark.
- Typing is never interrupted. Text is saved to the device as it is typed and is still there after a reload, a crash, a failed request, or a lost connection.
- Every tap acknowledges itself immediately, and every outcome is explained in a plain sentence next to the control that caused it.
- It is correct on a phone with a notch and an on-screen keyboard: safe areas respected, the action bar pinned above the keyboard, no zoom on focus.
- It is usable with VoiceOver.

Section 6 gives the views and behaviours. Where it leaves a visual or interaction detail open, decide it yourself in favour of calm and clarity, and keep the app small. Do not add features that are not in the spec.

**Constraints**

- No build step, no framework, no bundler, no runtime dependencies: hand-written HTML, CSS and JavaScript modules that work as committed. The site deploys by copying the repo. Dev-only test tooling is fine; put it under `tests/writenow/client/` and add what is needed to `.deploy_ignore`.
- Every API request sends the Basic `Authorization` header and uses `credentials: 'omit'`. Section 5 explains why this is required and not optional.
- The password never appears in a URL, a log, an error message or a cache.
- Work in `jeremyfelt.com` on the branch `<branch>`. Never push to `develop`; that deploys to production. Do not open a pull request. Change nothing outside `mu-plugins/apps/writenow/app/`, `tests/writenow/client/`, `.deploy_ignore` and, if needed, the spec.

**Verifying your work**

Chromium and Playwright are already installed; do not install another copy. Section 6.5 lists what the tests must cover. Serve the shell with a tiny local static server that fills the placeholders, intercept the API, and run at an iPhone-sized viewport with touch. Make the suite runnable with one command and document it.

Then look at your own work. Take screenshots of every view in light and dark and read them critically, as someone who has to use this every day would. Fix what looks off, even if the tests pass.

Chromium is not iOS Safari. Do not claim that anything works on an iPhone. Where behaviour depends on Safari (home screen install, keyboard and viewport handling, storage persistence, the photo picker, the login round trip), build it the way current WebKit documentation says is right, and list it in your report as needing a device check.

**Report back with**

1. What you built, as a short list of files and what each does.
2. The screenshots, and anything about the design you are unsure of.
3. Any place you departed from the spec, and why.
4. The exact commands you ran and their results, with failures included.
5. The list of behaviours that still need checking on a real iPhone.

Commit in small, described steps and push to `<branch>`.

---

## Prompt 3: Integration, Shortcuts and review

WriteNow is a web app that lets Jeremy Felt post notes and likes to his WordPress site, jeremyfelt.com, from his iPhone home screen and from iOS Shortcuts. The design is in `jeremyfelt.com/docs/writenow/spec.md`. Two people have built it in parallel from that spec without seeing each other's work: the PHP side (`mu-plugins/apps/writenow/` and `tests/writenow/`) and the browser side (`mu-plugins/apps/writenow/app/` and `tests/writenow/client/`). Both are now on the branch `<branch>`. Neither has run against the other.

Your job is to make the two halves work together, prove it, write the Shortcut instructions, and hand Jeremy something he can merge with confidence. This site is live and personal. Once this ships, a bug means a note lost on his phone or a duplicate published to the web and to Mastodon, so be sceptical of everything, including the reports the first two left behind.

Read the spec in full, then read the code. Form your own view of whether it does what the spec says before you trust any test.

**Do these, in order**

1. **Wire and run.** Stand up real WordPress locally with the real `shortnotes` and `likes` plugins from the sibling checkouts (section 8 of the spec says what works in this kind of environment, and `tests/writenow/README.md` should say how the server tests already do it). Load `/writenow/` in Chromium and use the app end to end with nothing mocked: connect, post a note, save and publish a draft, attach an image, reply, like a URL, like it again, go offline and come back. Fix whatever breaks at the seam. Where the two halves disagree, the spec decides; where the spec is wrong, fix the spec too.
2. **Add end-to-end tests** for those flows against the real server, alongside the existing suites, runnable with one command.
3. **Check the results in WordPress itself**, not only through the API: the slug and title of a published note, its block markup opened through `parse_blocks()`, its meta, the like's meta and rendered content, and that authenticated responses carry no-cache headers.
4. **Review for the failures that would hurt most.** Can anything publish twice? Can typed text be lost? Can the credential leak into a URL, a log, a cache or an error message? Can an unauthenticated or under-privileged request do anything? Can an authenticated response be cached and served to someone else? Does any path call into `shortnotes` or `likes` without checking they are active? Fix what you find and add a test for each fix.
5. **Write `docs/writenow/shortcuts.md`** as described in section 7 of the spec: the Like and Note recipes, step by step, for someone who has not built a Shortcut before, with `curl` equivalents that you have actually run against your local server. You cannot build or run a Shortcut, so state clearly that the action names have not been checked on a device.
6. **Write `mu-plugins/apps/writenow/README.md`**: what this is, how it is laid out, how to run the tests, how it deploys, and how to rename it.
7. **Write the handoff** described below.

**Constraints**

- Work in `jeremyfelt.com` on `<branch>`. Never push to `develop`; that deploys to production. Do not open a pull request unless you are asked to.
- Change nothing in the other repos, and nothing in `advanced-cache.php` or `object-cache.php`.
- Never contact the live site, and do not ask for production credentials.
- Keep the app small. Fix and verify what the spec describes; do not add features.
- `composer phpcs` and `composer phpstan` stay clean.

**The handoff**

Your final message is what Jeremy reads before deciding to merge, and merging deploys. Lead with whether you would ship it, and the one or two things he most needs to know. Then:

1. What works, and how you know: for each flow, whether you saw it work against real WordPress, or only in a mocked test, or not at all.
2. What you changed in each half, and why.
3. Every command you ran and its result, with failures included. Do not describe a check as passing unless you ran it.
4. What remains unverified. Include the device checklist from section 9 of the spec, updated with anything you learned, and the open questions from section 10 that are still open.
5. First steps for Jeremy after merging: what to tap, in order, to install the app, connect it, and build the Like shortcut.

Commit in small, described steps and push to `<branch>`.
