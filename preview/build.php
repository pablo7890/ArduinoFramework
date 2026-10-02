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

define( 'PIODESIGN_DIR', $root . '/piodesign/' );

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
// Stand-in for the "Parafia: Kalendarz liturgiczny" plugin: same filter, same
// fields, sample values. On the live site the parish's own data is used.
$W = [ 'biały', '#ffffff' ];
$G = [ 'zielony', '#2e7d32' ];
// [ title, rank, colour, readings, optional memorials, parish, occasional ]
$GLOBALS['pio_demo_liturgy'] = [
	'2026-10-02' => [ 'Świętych Aniołów Stróżów', 'wspomnienie obowiązkowe', $W, 'Wj 23,20-23a; Ps 91; Mt 18,1-5.10', [], [ 'Dni Eucharystyczne' ], [ 'Pierwszy piątek miesiąca' ] ],
	'2026-10-03' => [ 'Sobota XXVI tygodnia zwykłego', 'dzień powszedni', $G, 'Hi 42,1-3.5-6.12-17; Ps 119; Łk 10,17-24', [ 'Najświętszej Maryi Panny w sobotę' ], [ 'Dni Eucharystyczne' ], [ 'Pierwsza sobota miesiąca' ] ],
	'2026-10-04' => [ 'XXVII Niedziela zwykła', 'niedziela', $G, 'Iz 5,1-7; Ps 80; Flp 4,6-9; Mt 21,33-43', [], [], [] ],
	'2026-10-05' => [ 'Św. Faustyny Kowalskiej, dziewicy', 'wspomnienie obowiązkowe', $W, 'Ga 1,6-12; Ps 111; Łk 10,25-37', [], [], [] ],
	'2026-10-06' => [ 'Wtorek XXVII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 1,13-24; Ps 139; Łk 10,38-42', [ 'Św. Brunona, prezbitera' ], [], [] ],
	'2026-10-07' => [ 'Najświętszej Maryi Panny Różańcowej', 'wspomnienie obowiązkowe', $W, 'Ga 2,1-2.7-14; Ps 117; Łk 11,1-4', [], [], [] ],
	'2026-10-08' => [ 'Czwartek XXVII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 3,1-5; Łk 1,69-75; Łk 11,5-13', [], [], [] ],
	'2026-10-09' => [ 'Piątek XXVII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 3,7-14; Ps 111; Łk 11,15-26', [ 'Św. Dionizego, biskupa, i Towarzyszy, męczenników', 'Św. Jana Leonardiego, prezbitera' ], [], [] ],
	'2026-10-10' => [ 'Sobota XXVII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 3,22-29; Ps 105; Łk 11,27-28', [ 'Najświętszej Maryi Panny w sobotę' ], [], [] ],
	'2026-10-11' => [ 'XXVIII Niedziela zwykła', 'niedziela', $G, 'Iz 25,6-10a; Ps 23; Flp 4,12-14.19-20; Mt 22,1-14', [], [], [ 'Dzień Papieski' ] ],
	'2026-10-12' => [ 'Poniedziałek XXVIII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 4,22-24.26-27.31–5,1; Ps 113; Łk 11,29-32', [], [], [] ],
	'2026-10-13' => [ 'Wtorek XXVIII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 5,1-6; Ps 119; Łk 11,37-41', [], [ 'Nabożeństwo fatimskie' ], [] ],
	'2026-10-14' => [ 'Środa XXVIII tygodnia zwykłego', 'dzień powszedni', $G, 'Ga 5,18-25; Ps 1; Łk 11,42-46', [ 'Św. Kaliksta I, papieża i męczennika' ], [], [ 'Dzień Edukacji Narodowej' ] ],
	'2026-10-15' => [ 'Św. Teresy od Jezusa, dziewicy i doktora Kościoła', 'wspomnienie obowiązkowe', $W, 'Ef 1,1-10; Ps 98; Łk 11,47-54', [], [], [] ],
];
function pio_demo_day( $iso ) {
	$d = $GLOBALS['pio_demo_liturgy'][ $iso ] ?? null;
	return $d ? [
		'title'              => $d[0],
		'rank_label'         => $d[1],
		'color_label'        => $d[2][0],
		'color_hex'          => $d[2][1],
		'readings'           => $d[3],
		'optional_memorials' => $d[4],
		'parish'             => $d[5],
		'occasional'         => $d[6],
	] : null;
}
function apply_filters( $hook, $value, ...$args ) {
	if ( 'kalendarz_liturgiczny_day' === $hook ) {
		return pio_demo_day( $args[0] ) ?? $value;
	}
	if ( 'piodesign_category_color' === $hook && isset( $GLOBALS['pio_demo_colors'][ $args[0] ] ) ) {
		return $GLOBALS['pio_demo_colors'][ $args[0] ];
	}
	return $value;
}

require PIODESIGN_DIR . 'includes/core.php';
require PIODESIGN_DIR . 'includes/sections-core.php';
function piodesign_now() { return $GLOBALS['now']; }
// Fresh assets/piodesign.css from assets/src/piodesign.css.
passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( PIODESIGN_DIR . 'tools/build-css.php' ) . ' >&2' );

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
	10263 => $cat( 'Wspólnoty', 'wspolnoty' ),
	10181 => $cat( 'Caritas', 'caritas' ),
	10179 => $cat( 'Caritas', 'caritas' ),
	10177 => $cat( 'Caritas', 'caritas' ),
	10107 => $cat( 'Wspólnoty', 'wspolnoty' ),
	10060 => $cat( 'Kultura', 'kultura' ),
	9969  => $cat( 'Wspólnoty', 'wspolnoty' ),
	9936  => $cat( 'Wspólnoty', 'wspolnoty' ),
	9940  => $cat( 'Liturgia', 'liturgia' ),
];

$posts = [];
foreach ( $data['posts'] as $p ) {
	$posts[] = piodesign_post(
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
	$events[] = piodesign_event(
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

$day   = piodesign_day( $now, apply_filters( 'kalendarz_liturgiczny_day', null, $now->format( 'Y-m-d' ) ) );
$today = piodesign_date( $now, true, true );

/* ------------------------------------------------------------ views */

// Sacraments: the parish pages with the homepage slider's texts and images.
$sac_def = piodesign_sacrament_defaults();
$sac     = [];
foreach ( $data['sacraments'] as $sp ) {
	$def   = $sac_def[ $sp['slug'] ] ?? [ '', '', '' ];
	$sac[] = [
		'id'    => $sp['slug'],
		'slug'  => $sp['slug'],
		'title' => $def[2] ?: $sp['title'],
		'text'  => $sp['excerpt'] ?: $def[0],
		'url'   => $sp['url'],
		'image' => $def[1] ? piodesign_frame( [ 'src' => 'https://parafiapio.pl/wp-content/uploads/' . $def[1], 'w' => 1280, 'h' => 853 ], 'post' ) : null,
	];
}
$sacraments = piodesign_render( 'sacraments', [ 'slides' => $sac, 'title' => 'Sakramenty', 'kicker' => 'Droga wiary', 'autoplay' => 7, 'more_url' => '' ] );
$opts       = piodesign_info_defaults();
$quotes     = piodesign_render( 'quotes', [ 'quotes' => piodesign_quotes( $opts['cytaty'] ), 'title' => 'Słowa na dziś', 'author' => 'św. Ojciec Pio', 'photo' => '', 'autoplay' => 9 ] );
$lit        = piodesign_liturgy( $now );
$info       = piodesign_render(
	'info',
	[
		'masses'   => piodesign_mass_schedule( $opts ),
		'office'   => piodesign_office_hours( $opts['kancelaria'] ),
		'o'        => $opts,
		'partners' => piodesign_partners( $opts['partnerzy'] ),
		'advent'   => 'advent' === $lit['slug'],
		'today'    => (int) $now->format( 'N' ),
		'title'    => 'Zapraszamy',
		'kicker'   => 'Parafia św. Ojca Pio',
	]
);
$lit_days = [];
for ( $k = 0; $k < 7; $k++ ) {
	$d          = $now->setTime( 0, 0 )->modify( '+' . $k . ' days' );
	$lit_days[] = piodesign_liturgy_day( $d, pio_demo_day( $d->format( 'Y-m-d' ) ), $now );
}
$liturgy = piodesign_render( 'liturgy', [ 'days' => $lit_days, 'title' => 'Liturgia dnia', 'kicker' => 'Kalendarz liturgiczny' ] );

$home = piodesign_render(
	'news',
	[
		'posts'       => array_slice( $posts, 0, 15 ),
		'day'         => $day,
		'mobile'      => 5,
		'archive_url' => 'https://parafiapio.pl/aktualnosci/',
		'title'       => 'Aktualności',
		'kicker'      => 'Z życia parafii',
	]
) . piodesign_render(
	'events',
	[
		'events'       => $home_ev,
		'grouped'      => piodesign_group_events( $home_ev, $now ),
		'mobile'       => 5,
		'timeline'     => piodesign_timeline( $events, $now ),
		'calendar_url' => '#kalendarz',
		'title'        => 'Wydarzenia',
		'kicker'       => 'Nadchodzące',
	]
) . $liturgy . '<div class="pv-cols">' . $sacraments . $quotes . '</div>' . $info;

// Single: IX Kongres Grupy Modlitwy (poster image shows the "contain" frame).
$single_id = (int) ( getenv( 'PIO_SINGLE' ) ?: 10780 );
$idx       = array_search( $single_id, array_column( $events, 'id' ), true );
$single_e  = $events[ $idx ];
$related   = array_slice( array_values( array_filter( $upcoming, static fn( $e ) => $e['id'] !== $single_id ) ), 0, 3 );
$single    = piodesign_render(
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
		$archive_rows .= piodesign_render( 'partials/month-head', [ 'month' => piodesign_months( 'nom' )[ (int) $d->format( 'n' ) ], 'year' => $d->format( 'Y' ) ] );
		$last_month    = $m;
	}
	$archive_rows .= piodesign_render( 'partials/event-row', [ 'e' => $e, 'context' => 'archive', 'i' => $k ] );
}
$archive = piodesign_render(
	'archive-hero',
	[
		'day'      => $day,
		'timeline' => piodesign_timeline( $events, $now ),
	]
);
$archive .= file_get_contents( __DIR__ . '/tec-bar.html' );
$archive .= '<div class="tribe-events-calendar-list">' . $archive_rows . '</div>';
$archive .= '<nav class="tec-mock-nav"><span>‹ Wcześniejsze wydarzenia</span><span>Dzisiaj</span><span class="is-on">Następne wydarzenia ›</span></nav>';

// News archive, page 1 of the posts page.
$per_page   = 18;
$total      = (int) ( $data['total_posts'] ?? count( $posts ) );
$cat_counts = [];
foreach ( $posts as $p ) {
	$slug = $p['category']['slug'];
	$cat_counts[ $slug ] = ( $cat_counts[ $slug ] ?? [ 'name' => $p['category']['name'], 'url' => '#aktualnosci', 'count' => 0, 'color' => $p['category']['color'], 'active' => false ] );
	$cat_counts[ $slug ]['count']++;
}
uasort( $cat_counts, static fn( $a, $b ) => $b['count'] <=> $a['count'] );
$pages        = (int) ceil( $total / $per_page );
$news_archive = piodesign_render(
	'news-archive',
	[
		'posts'       => array_slice( $posts, 0, $per_page ),
		'title'       => 'Aktualności',
		'kicker'      => 'Z życia parafii',
		'description' => '',
		'cats'        => array_values( $cat_counts ),
		'all_url'     => '#aktualnosci',
		'all_active'  => true,
		'page'        => 1,
		'pages'       => $pages,
		'total'       => $total,
		'pager'       => piodesign_pager( 1, $pages, static fn( $n ) => 'https://parafiapio.pl/aktualnosci/page/' . $n . '/' ),
		'search'      => [ 'action' => 'https://parafiapio.pl/', 'value' => '' ],
	]
);

/* ------------------------------------------------------------ page */

$shell = file_get_contents( __DIR__ . '/shell.html' );
$html  = strtr(
	$shell,
	[
		'{{CSS}}'      => pio_inline_fonts() . "\n" . file_get_contents( PIODESIGN_DIR . 'assets/piodesign.css' ) . "\n" . file_get_contents( __DIR__ . '/preview.css' ),
		'{{JS}}'       => file_get_contents( PIODESIGN_DIR . 'assets/piodesign.js' ),
		'{{HOME}}'     => $home,
		'{{SINGLE}}'   => $single,
		'{{ARCHIVE}}'  => $archive,
		'{{TODAY}}'    => esc_html( $today ),
		'{{NPOSTS}}'   => 15,
		'{{ARCHIVE_NEWS}}' => $news_archive,
		'{{NEVENTS}}'  => count( $home_ev ),
	]
);

/* ------------------------------------------------------------ embed fonts & images */

function pio_inline_fonts() {
	// The site gets its faces from Avada; the preview embeds the same files.
	$css = file_get_contents( __DIR__ . '/fonts/fonts.css' );
	return preg_replace_callback(
		'#url\("([^"]+\.woff2)"\)#',
		static fn( $m ) => 'url("data:font/woff2;base64,' . base64_encode( file_get_contents( __DIR__ . '/fonts/' . $m[1] ) ) . '")',
		$css
	);
}

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
