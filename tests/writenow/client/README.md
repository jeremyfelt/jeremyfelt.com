# WriteNow client tests

Dev-only browser tests for the WriteNow web app in `mu-plugins/apps/writenow/app/`. Excluded from deploy by `.deploy_ignore`.

They use the Playwright and Chromium already installed on the machine (`require('playwright')`). The only npm dependency is `axe-core`, for the accessibility scan.

## Run

```sh
cd tests/writenow/client
npm ci        # first time only: installs axe-core into node_modules/ (git-ignored)
npm test      # all suites, about 40 seconds
```

`npm run screenshots` rewrites `screenshots/` (every view, light and dark, at 390×844 @3x). `npm run icons` re-exports the PNG icons from the SVG sources. `npm run serve` starts the static server alone at http://localhost:8891/writenow/ (API calls fail without the tests' interception).

## How it works

- `server.js` serves the shell, manifest and service worker with their `{{NAME}}` tokens filled, the way the PHP side does, plus `app/` as static files, the writemore theme's fonts, and a stand-in `wp-login.php` that sets a login cookie and redirects back. It uses ports 8891 to 8899.
- `fake-wp.js` answers intercepted `wp-json` and `admin-ajax.php` requests per spec sections 4.3 and 5, in memory. Like WordPress, it rejects a request that carries both a login cookie and a Basic header, so a missing `credentials: 'omit'` fails the tests.
- Each test gets a fresh iPhone-sized context with touch. Service workers are blocked except in `sw.test.js`.

Chromium is not iOS Safari. Passing tests say nothing about home screen install, the keyboard, storage persistence, the photo picker or the login round trip on a real iPhone.
