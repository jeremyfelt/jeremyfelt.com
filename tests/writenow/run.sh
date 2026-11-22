#!/usr/bin/env bash
#
# Run the WriteNow server tests against a real, local WordPress.
#
#   tests/writenow/run.sh [filter]
#   tests/writenow/run.sh --serve
#
# Builds a fresh WordPress site on SQLite outside the repository, with the
# real shortnotes and likes plugins from their sibling checkouts, serves it
# with PHP's built-in server, and runs every server test. An optional filter
# runs only the tests whose names contain it. --serve builds the same site
# and keeps it running in the foreground for using the app by hand.
# See README.md for details.

set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CACHE="${WRITENOW_TEST_CACHE:-$HOME/.cache/writenow-tests}"
WP_VERSION="${WP_VERSION:-7.1.5}"
SQLITE_VERSION="${SQLITE_VERSION:-v3.1.3}"
PORT="${WRITENOW_TEST_PORT:-8881}"
SITE="$CACHE/site-$PORT"
CORE="$CACHE/wordpress-$WP_VERSION"
SQLITE="$CACHE/sqlite-database-integration-$SQLITE_VERSION"

mkdir -p "$CACHE"

if [ ! -f "$CORE/wp-includes/version.php" ]; then
	echo "Downloading WordPress $WP_VERSION..."
	rm -rf "$CORE"
	git clone --quiet --depth 1 --branch "$WP_VERSION" https://github.com/WordPress/WordPress.git "$CORE"
fi

if [ ! -f "$SQLITE/packages/plugin-sqlite-database-integration/db.copy" ]; then
	echo "Downloading the SQLite database integration $SQLITE_VERSION..."
	rm -rf "$SQLITE"
	git clone --quiet --depth 1 --branch "$SQLITE_VERSION" https://github.com/WordPress/sqlite-database-integration.git "$SQLITE"
fi

php "$HERE/bin/setup-site.php" "$SITE" "$CORE" "$SQLITE" "$PORT"

if [ "${1:-}" = "--serve" ]; then
	echo
	echo "WriteNow:  http://127.0.0.1:$PORT/writenow/"
	echo "wp-admin:  http://127.0.0.1:$PORT/wp-admin/  (admin / password)"
	echo "Application Passwords for each test user: $SITE/credentials.json"
	echo "Press Ctrl-C to stop."
	exec env PHP_CLI_SERVER_WORKERS=4 php -S "127.0.0.1:$PORT" -t "$SITE/wordpress" "$HERE/bin/router.php"
fi

# Several workers, so that concurrent requests are really concurrent.
PHP_CLI_SERVER_WORKERS=4 php -S "127.0.0.1:$PORT" -t "$SITE/wordpress" "$HERE/bin/router.php" > "$SITE/server.log" 2>&1 &
SERVER_PID=$!
trap 'kill "$SERVER_PID" 2>/dev/null || true' EXIT

for _ in $(seq 1 50); do
	if curl --silent --output /dev/null "http://127.0.0.1:$PORT/wp-login.php"; then
		break
	fi
	sleep 0.2
done

export WRITENOW_TEST_SITE="$SITE"
export WRITENOW_TEST_URL="http://127.0.0.1:$PORT"

status=0
php "$HERE/bin/run-tests.php" "${1:-}" || status=$?

if [ -z "${1:-}" ] || [[ "editor" == *"${1:-}"* ]]; then
	if [ -n "${WRITENOW_SKIP_EDITOR:-}" ]; then
		echo "Skipping the block editor check (WRITENOW_SKIP_EDITOR is set)."
	elif command -v node > /dev/null && NODE_PATH="$(npm root -g)" node -e "require('playwright')" 2> /dev/null; then
		NODE_PATH="$(npm root -g)" node "$HERE/editor/validate-blocks.cjs" || status=$?
	else
		echo "Skipping the block editor check: Node with Playwright is not available."
	fi
fi

# Any PHP notice, warning or error fails the run, except the known PHP 8.2+
# deprecations in the existing advanced-cache.php (Batcache), which are listed.
if [ -s "$SITE/debug.log" ]; then
	known="$(grep -c 'advanced-cache.php' "$SITE/debug.log" || true)"
	if [ "$known" -gt 0 ]; then
		echo
		echo "Known: $known PHP deprecation notices from advanced-cache.php (Batcache) on PHP $(php -r 'echo PHP_VERSION;'), for example:"
		grep 'advanced-cache.php' "$SITE/debug.log" | sed 's/^\[[^]]*\] //' | sort -u | head -3
	fi
	if grep -v 'advanced-cache.php' "$SITE/debug.log" | grep -q .; then
		echo
		echo "PHP notices, warnings or errors logged by the test site ($SITE/debug.log):"
		grep -v 'advanced-cache.php' "$SITE/debug.log"
		status=1
	fi
fi

exit "$status"
