#!/usr/bin/env bash
# Local WordPress for front-end testing in the cloud sandbox (no MySQL needed).
#   bash setup-local-wp.sh <dir> [plugin-dir ...]
# → WordPress (pl_PL) on SQLite, The Events Calendar, twentytwentyone,
#   given plugins symlinked + activated, server on http://127.0.0.1:8099 (admin/admin).
set -euo pipefail
DIR="${1:?usage: setup-local-wp.sh <dir> [plugin-dir ...]}"; shift || true
TEC_VERSION="${TEC_VERSION:-6.18.0}"
SQLITE_VERSION="${SQLITE_VERSION:-}"   # empty = latest
PORT="${PORT:-8099}"
# Resolve plugin paths before changing directory.
PLUGINS=()
for p in "$@"; do PLUGINS+=("$(cd "$p" && pwd)"); done
mkdir -p "$DIR" && DIR="$(cd "$DIR" && pwd)" && cd "$DIR"
dl() { for i in 1 2 3 4; do curl -fsSL -o "$2" "$1" && return 0; sleep $((i*2)); done; return 1; }

[ -f wp-cli.phar ] || dl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar wp-cli.phar
WP="php $DIR/wp-cli.phar --allow-root"
if [ ! -d wordpress ]; then
  dl https://wordpress.org/latest.zip wp.zip && unzip -q wp.zip
fi
cd wordpress
if [ ! -f wp-content/db.php ]; then
  dl "https://downloads.wordpress.org/plugin/sqlite-database-integration${SQLITE_VERSION:+.$SQLITE_VERSION}.zip" ../sqlite.zip
  unzip -qo ../sqlite.zip -d wp-content/plugins/
  sed "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$PWD/wp-content/plugins/sqlite-database-integration#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
    wp-content/plugins/sqlite-database-integration/db.copy > wp-content/db.php
fi
[ -f wp-config.php ] || $WP config create --dbname=wp --dbuser=x --dbpass=x --skip-check 2>/dev/null
if ! $WP core is-installed 2>/dev/null; then
  $WP core install --url="http://127.0.0.1:$PORT" --title="Parafia test" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email 2>/dev/null
  $WP language core install pl_PL --activate 2>/dev/null || true
  $WP option update timezone_string Europe/Warsaw 2>/dev/null
  $WP rewrite structure '/%year%/%monthnum%/%day%/%postname%/' 2>/dev/null
  $WP theme install twentytwentyone --activate 2>/dev/null || true
fi
if [ ! -d wp-content/plugins/the-events-calendar ]; then
  dl "https://downloads.wordpress.org/plugin/the-events-calendar.$TEC_VERSION.zip" ../tec.zip
  unzip -qo ../tec.zip -d wp-content/plugins/
  $WP plugin activate the-events-calendar 2>/dev/null
  $WP language plugin install the-events-calendar pl_PL 2>/dev/null || true
fi
for p in "${PLUGINS[@]}"; do
  slug="$(basename "$p")"
  ln -sfn "$p" "wp-content/plugins/$slug"
  $WP plugin activate "$slug" 2>/dev/null || true
done
# WordPress can record the folder in the URL (…/wordpress); the router serves the root.
$WP option update home "http://127.0.0.1:$PORT" 2>/dev/null
$WP option update siteurl "http://127.0.0.1:$PORT" 2>/dev/null
$WP rewrite flush 2>/dev/null
cat > ../router.php <<'PHP'
<?php
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = __DIR__ . '/wordpress' . $path;
if ( $path !== '/' && is_file( $file ) && substr( $file, -4 ) !== '.php' ) { return false; }
if ( is_file( $file ) && substr( $file, -4 ) === '.php' ) { chdir( dirname( $file ) ); require $file; return; }
chdir( __DIR__ . '/wordpress' );
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/wordpress/index.php';
PHP
echo "Ready. Start the server in the background:"
echo "  cd $DIR/wordpress && php -d display_errors=0 -S 127.0.0.1:$PORT ../router.php"
