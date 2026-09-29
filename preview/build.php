<?php
/**
 * Offline preview: renders the plugin's real templates with real content from
 * parafiapio.pl (preview/data.json) into one self-contained HTML file with
 * images embedded.
 *
 *     python3 preview/fetch.py            # refresh data.json (optional)
 *     php preview/build.php [out.html]    # default: preview/dist/index.html
 *
 * Needs PHP 7.4+ with GD and the `curl` binary (for downloading images).
 */

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

$root = dirname( __DIR__ );
$out  = $argv[1] ?? __DIR__ . '/dist/index.html';
$cache_dir = getenv( 'PIO_CACHE' ) ?: __DIR__ . '/.cache';
@mkdir( dirname( $out ), 0777, true );
@mkdir( $cache_dir, 0777, true );

define( 'PIO_GAZETA_DIR', $root . '/pio-gazeta/' );

/* ------------------------------------------------------------ WordPress shims */

function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function wp_json_encode( $v ) { return json_encode( $v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); }

// Proposed categories for the preview (the live site files almost everything
// under "Aktualności"). Colours are fixed here so the demo reads well.
$GLOBALS['pio_demo_colors'] = [
	'liturgia'    => '#6b4a8f',
	'wspolnoty'   => '#2f6f73',
	'kultura'     => '#c14e00',
	'fotorelacje' => '#9a6a1f',
	'pielgrzymki' => '#3d5a80',
	'caritas'     => '#b3261e',
	'ogloszenia'  => '#3f7a4f',
];
function apply_filters( $hook, $value, ...$args ) {
	if ( 'pio_gazeta_category_color' === $hook && isset( $GLOBALS['pio_demo_colors'][ $args[0] ] ) ) {
		return $GLOBALS['pio_demo_colors'][ $args[0] ];
	}
	return $value;
}

require PIO_GAZETA_DIR . 'includes/core.php';

/* ------------------------------------------------------------ data */

$tz   = new DateTimeZone( 'Europe/Warsaw' );
$now  = new DateTimeImmutable( getenv( 'PIO_NOW' ) ?: 'now', $tz );
$data = json_decode( file_get_contents( __DIR__ . '/data.json' ), true );

$cat = static fn( $name, $slug ) => [ 'name' => $name, 'slug' => $slug ];
$demo_categories = [
	10772 => $cat( 'Fotorelacje', 'fotorelacje' ),
	10606 => $cat( 'Liturgia', 'liturgia' ),
	10547 => $cat( 'Kultura', 'kultura' ),
	10541 => $cat( 'Wspólnoty', 'wspolnoty' ),
	10539 => $cat( 'Wspólnoty', 'wspolnoty' ),
	10261 => $cat( 'Kultura', 'kultura' ),
	10446 => $cat( 'Kultura', 'kultura' ),
	10403 => $cat( 'Caritas', 'caritas' ),
	10468 => $cat( 'Liturgia', 'liturgia' ),
	10336 => $cat( 'Pielgrzymki', 'pielgrzymki' ),
	10272 => $cat( 'Kultura', 'kultura' ),
	10305 => $cat( 'Ogłoszenia', 'ogloszenia' ),
	10213 => $cat( 'Kultura', 'kultura' ),
	10249 => $cat( 'Liturgia', 'liturgia' ),
	10192 => $cat( 'Pielgrzymki', 'pielgrzymki' ),
];

$posts = [];
foreach ( $data['posts'] as $p ) {
	$posts[] = pio_gazeta_post(
		[
			'id'          => $p['id'],
			'title'       => $p['title'],
			'url'         => $p['url'],
			'date'        => new DateTimeImmutable( $p['date'], $tz ),
			'excerpt'     => $p['excerpt'],
			'category'    => $demo_categories[ $p['id'] ] ?? $p['category'],
			'image'       => $p['image'] ?? [],
			'gallery'     => $p['gallery'],
			'photo_count' => $p['photo_count'],
			'words'       => $p['words'],
		],
		$now
	);
}

$strip = static function ( $html ) {
	$html = preg_replace( '#<(script|style|iframe)\b.*?</\1>#is', '', $html );
	$html = preg_replace( '#<img\b[^>]*>#i', '', $html );
	$html = preg_replace( '#<p>(\s|&nbsp;)*</p>#i', '', $html );
	return trim( $html );
};

$events = [];
foreach ( $data['events'] as $e ) {
	$events[] = pio_gazeta_event(
		[
			'id'        => $e['id'],
			'title'     => $e['title'],
			'url'       => $e['url'],
			'start'     => new DateTimeImmutable( $e['start'], $tz ),
			'end'       => new DateTimeImmutable( $e['end'], $tz ),
			'all_day'   => $e['all_day'],
			'excerpt'   => $e['excerpt'],
			'content'   => $strip( $e['content'] ),
			'image'     => $e['image'] ?? [],
			'venue'     => $e['venue'],
			'organizer' => $e['organizer'],
			'category'  => $e['category'],
			'featured'  => $e['featured'],
			'cost'      => $e['cost'],
			'ics_url'   => $e['url'] . '?ical=1',
		],
		$now
	);
}
usort( $events, static fn( $a, $b ) => $a['start_ts'] <=> $b['start_ts'] );
$upcoming = array_values( array_filter( $events, static fn( $e ) => 'past' !== $e['status'] ) );
$home_ev  = array_slice( $upcoming, 0, 10 );

$liturgy = pio_gazeta_liturgy( $now );
$today   = pio_gazeta_date( $now, true, true );

/* ------------------------------------------------------------ views */

$home = pio_gazeta_render(
	'news',
	[
		'posts'       => $posts,
		'liturgy'     => $liturgy,
		'today'       => $today,
		'archive_url' => 'https://parafiapio.pl/aktualnosci/',
		'title'       => 'Aktualności',
		'kicker'      => 'Z życia parafii',
	]
) . pio_gazeta_render(
	'events',
	[
		'events'       => $home_ev,
		'grouped'      => pio_gazeta_group_events( $home_ev, $now ),
		'timeline'     => pio_gazeta_timeline( $events, $now ),
		'calendar_url' => '#kalendarz',
		'title'        => 'Wydarzenia',
		'kicker'       => 'Nadchodzące',
	]
);

// Single: IX Kongres Grupy Modlitwy (poster image shows the "contain" frame).
$single_id = (int) ( getenv( 'PIO_SINGLE' ) ?: 10780 );
$idx       = array_search( $single_id, array_column( $events, 'id' ), true );
$single_e  = $events[ $idx ];
$related   = array_slice( array_values( array_filter( $upcoming, static fn( $e ) => $e['id'] !== $single_id ) ), 0, 3 );
$single    = pio_gazeta_render(
	'single-event',
	[
		'e'            => $single_e,
		'content'      => $single_e['content'],
		'related'      => $related,
		'prev'         => $events[ $idx - 1 ] ?? null,
		'next'         => $events[ $idx + 1 ] ?? null,
		'calendar_url' => '#kalendarz',
	]
);

// Archive: TEC list view = hero (our hook) + TEC events bar + our rows.
$archive_rows = '';
$last_month   = '';
foreach ( array_slice( $upcoming, 0, 12 ) as $k => $e ) {
	$sep = max( $e['ymd'], $now->format( 'Y-m-d' ) );
	$m   = substr( $sep, 0, 7 );
	if ( $m !== $last_month ) {
		$d             = new DateTimeImmutable( $sep, $tz );
		$archive_rows .= pio_gazeta_render( 'partials/month-head', [ 'month' => pio_gazeta_months( 'nom' )[ (int) $d->format( 'n' ) ], 'year' => $d->format( 'Y' ) ] );
		$last_month    = $m;
	}
	$archive_rows .= pio_gazeta_render( 'partials/event-row', [ 'e' => $e, 'context' => 'archive', 'i' => $k ] );
}
$archive = pio_gazeta_render(
	'archive-hero',
	[
		'liturgy'  => $liturgy,
		'today'    => $today,
		'timeline' => pio_gazeta_timeline( $events, $now ),
	]
);
$archive .= file_get_contents( __DIR__ . '/tec-bar.html' );
$archive .= '<div class="tribe-events-calendar-list">' . $archive_rows . '</div>';
$archive .= '<nav class="tec-mock-nav"><span>‹ Wcześniejsze wydarzenia</span><span>Dzisiaj</span><span class="is-on">Następne wydarzenia ›</span></nav>';

/* ------------------------------------------------------------ page */

$shell = file_get_contents( __DIR__ . '/shell.html' );
$html  = strtr(
	$shell,
	[
		'{{CSS}}'      => file_get_contents( PIO_GAZETA_DIR . 'assets/pio-gazeta.css' ) . "\n" . file_get_contents( __DIR__ . '/preview.css' ),
		'{{JS}}'       => file_get_contents( PIO_GAZETA_DIR . 'assets/pio-gazeta.js' ),
		'{{HOME}}'     => $home,
		'{{SINGLE}}'   => $single,
		'{{ARCHIVE}}'  => $archive,
		'{{TODAY}}'    => esc_html( $today ),
		'{{NPOSTS}}'   => count( $posts ),
		'{{NEVENTS}}'  => count( $home_ev ),
	]
);

/* ------------------------------------------------------------ embed images */

function pio_fetch_image( $url, $max_w, $cache_dir ) {
	$file = $cache_dir . '/' . md5( $url . $max_w ) . '.webp';
	if ( ! is_file( $file ) ) {
		$raw = $cache_dir . '/' . md5( $url ) . '.raw';
		if ( ! is_file( $raw ) ) {
			$cmd = 'curl -sSfL -m 60 -o ' . escapeshellarg( $raw ) . ' ' . escapeshellarg( $url );
			exec( $cmd, $o, $code );
			if ( 0 !== $code ) {
				fwrite( STDERR, "! download failed: $url\n" );
				return null;
			}
		}
		$im = @imagecreatefromstring( file_get_contents( $raw ) );
		if ( ! $im ) {
			fwrite( STDERR, "! decode failed: $url\n" );
			return null;
		}
		if ( imagesx( $im ) > $max_w ) {
			$im = imagescale( $im, $max_w, -1, IMG_BICUBIC );
		}
		imagepalettetotruecolor( $im );
		imagewebp( $im, $file, 68 );
	}
	return 'data:image/webp;base64,' . base64_encode( file_get_contents( $file ) );
}

preg_match_all( '#https://parafiapio\.pl/wp-content/uploads/[^"\'\s&]+\.(?:jpe?g|png|webp)#i', $html, $m );
$urls    = array_values( array_unique( $m[0] ) );
$gallery = [];
foreach ( $posts as $p ) {
	foreach ( $p['gallery'] as $g ) {
		$gallery[ $g ] = true;
	}
}
$map = [];
foreach ( $urls as $n => $url ) {
	$uri = pio_fetch_image( $url, isset( $gallery[ $url ] ) ? 560 : 900, $cache_dir );
	if ( ! $uri ) {
		continue;
	}
	$key         = 'k' . $n;
	$map[ $key ] = $uri;
	$html        = str_replace( 'src="' . esc_url( $url ) . '"', 'data-k="' . $key . '"', $html );
	$html        = str_replace( '&quot;' . esc_url( $url ) . '&quot;', '&quot;' . $key . '&quot;', $html );
}
$html = str_replace( '{{IMAGES}}', json_encode( $map, JSON_UNESCAPED_SLASHES ), $html );

file_put_contents( $out, $html );
fprintf( STDERR, "Wrote %s (%.1f MB, %d images)\n", $out, filesize( $out ) / 1048576, count( $map ) );
