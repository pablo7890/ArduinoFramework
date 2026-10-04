<?php
/**
 * Our own calendar pages. TEC keeps its URLs, queries and settings, but the
 * HTML of the list, month and day views comes from PioDesign, so no part of
 * TEC's default look (events bar, date picker, "subscribe" dropdown, month
 * grid, messages) ever shows up:
 *
 *  - /wydarzenia/lista/            upcoming events, paged, grouped by month
 *  - /wydarzenia/lista/?eventDisplay=past   past events, newest first
 *  - /wydarzenia/miesiac/(Y-m/)    month grid; agenda under a mini grid on phones
 *  - /wydarzenia/Y-m-d/, /dzisiaj/ one day
 *  - …/kategoria/{slug}/…          any of the above for one category
 *  - ?tribe-bar-search=…           search in any of them
 *
 * Switch off with `add_filter( 'piodesign_tec_views', '__return_false' );`
 * (TEC's list view then falls back to the row overrides in tec.php).
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'tribe_events_views_v2_bootstrap_pre_get_view_html',
	static function ( $html, $slug, $query, $context ) {
		if ( null !== $html || ! apply_filters( 'piodesign_tec_views', true ) || ! is_object( $context ) ) {
			return $html;
		}
		// Single events and embeds go through TEC (and our single template).
		if ( is_singular( 'tribe_events' ) || is_embed() || ( $query instanceof WP_Query && $query->is_singular() ) ) {
			return $html;
		}
		wp_enqueue_style( 'piodesign' );
		wp_enqueue_script( 'piodesign' );
		return piodesign_calendar_html( piodesign_cal_state( $slug, $context ) );
	},
	20,
	4
);

/*
 * Our month and day views can be browsed to any date, so TEC must not answer
 * a month or day without events with a 404 (TEC 6.x does that for dates
 * outside the range of existing events). We lift that 404 right after TEC
 * sets it and send `noindex` instead, so search engines still skip them.
 */
add_action(
	'send_headers',
	static function () {
		global $wp_query;
		if ( ! apply_filters( 'piodesign_tec_views', true ) || ! $wp_query instanceof WP_Query || ! $wp_query->is_404() ) {
			return;
		}
		$q = $wp_query->query;
		if ( ( $q['post_type'] ?? '' ) !== 'tribe_events' || ! in_array( $q['eventDisplay'] ?? '', [ 'month', 'day', 'list', 'past' ], true ) || isset( $q['name'] ) || isset( $q['tribe_events'] ) ) {
			return;
		}
		$wp_query->is_404 = false;
		status_header( 200 );
		add_filter(
			'wp_robots',
			static function ( array $robots ) {
				$robots['noindex'] = true;
				return $robots;
			}
		);
	},
	20
);

/** The request as PioDesign understands it. */
function piodesign_cal_state( $slug, $context ) {
	$now  = piodesign_now();
	$view = (string) $slug;
	if ( 'default' === $view ) {
		$view = function_exists( 'tribe_get_option' ) ? (string) tribe_get_option( 'viewOption', 'list' ) : 'list';
	}
	// Views we don't draw (week, photo, map… from add-ons) fall back to the list.
	if ( ! in_array( $view, [ 'list', 'month', 'day' ], true ) ) {
		$view = 'list';
	}

	$term = null;
	$cat  = $context->get( 'event_category', null );
	if ( is_array( $cat ) ) {
		$cat = reset( $cat );
	}
	if ( $cat ) {
		$term = get_term_by( is_numeric( $cat ) ? 'id' : 'slug', $cat, 'tribe_events_cat' ) ?: null;
	}

	$date = (string) $context->get( 'event_date', '' );
	$day  = null;
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $date ) ) {
		$day = DateTimeImmutable::createFromFormat( '!Y-m-d', substr( $date, 0, 10 ), wp_timezone() ) ?: null;
	} elseif ( preg_match( '/^\d{4}-\d{2}$/', $date ) ) {
		$day = DateTimeImmutable::createFromFormat( '!Y-m-d', $date . '-01', wp_timezone() ) ?: null;
	}

	return [
		'view'    => $view,
		'past'    => 'list' === $view && 'past' === $context->get( 'event_display_mode', '' ),
		'term'    => $term,
		'keyword' => trim( (string) $context->get( 'keyword', '' ) ),
		'page'    => max( 1, (int) $context->get( 'paged', 1 ) ),
		'day'     => $day,
		'now'     => $now,
	];
}

/* ---------------------------------------------------------------------------
 * URLs
 * ------------------------------------------------------------------------ */

/**
 * Link to a calendar view, keeping the category and (optionally) the search.
 *
 * @param string $view list | past | month | day.
 * @param array  $s    piodesign_cal_state().
 * @param array  $args date (Y-m or Y-m-d), page, term (WP_Term|null|false = drop), keyword (string|false).
 */
function piodesign_cal_url( $view, array $s, array $args = [] ) {
	$term    = array_key_exists( 'term', $args ) ? $args['term'] : $s['term'];
	$keyword = array_key_exists( 'keyword', $args ) ? $args['keyword'] : $s['keyword'];
	$date    = $args['date'] ?? false;
	$tec     = 'past' === $view ? 'list' : $view;
	$term_id = $term ? (int) $term->term_id : null;

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$url = Tribe__Events__Main::instance()->getLink( $tec, 'list' === $tec ? false : ( $date ?: false ), $term_id );
	} else {
		$url = home_url( '/' );
	}
	if ( 'list' === $view && $date ) {
		$url = add_query_arg( 'tribe-bar-date', $date, $url );
	}
	if ( ! empty( $args['page'] ) && (int) $args['page'] > 1 ) {
		$url = trailingslashit( $url ) . 'page/' . (int) $args['page'] . '/';
	}
	if ( 'past' === $view ) {
		$url = add_query_arg( 'eventDisplay', 'past', $url );
	}
	if ( $keyword ) {
		$url = add_query_arg( 'tribe-bar-search', rawurlencode( $keyword ), $url );
	}
	return $url;
}

/* ---------------------------------------------------------------------------
 * Queries
 * ------------------------------------------------------------------------ */

/** TEC ORM query with the page's category and search applied. */
function piodesign_cal_query( array $s, array $where, $order = 'ASC', $per_page = -1, $page = 1 ) {
	if ( ! function_exists( 'tribe_events' ) ) {
		return [ [], 0 ];
	}
	$repo = tribe_events()->where( 'status', 'publish' )->order_by( 'event_date', $order );
	foreach ( $where as $k => $v ) {
		$repo = $repo->where( $k, $v );
	}
	if ( $s['term'] ) {
		$repo = $repo->where( 'category', [ (int) $s['term']->term_id ] );
	}
	if ( '' !== $s['keyword'] ) {
		$repo = $repo->search( $s['keyword'] );
	}
	if ( $per_page > 0 ) {
		$repo = $repo->per_page( $per_page )->page( max( 1, $page ) );
	} else {
		$repo = $repo->per_page( 400 );
	}
	$posts = (array) $repo->all();
	$total = $per_page > 0 ? (int) $repo->found() : count( $posts );
	return [ $posts, $total ];
}

/** Category colour for events: stable per slug, overridable. */
function piodesign_event_cat_color( $slug ) {
	static $palette = [ '#c14e00', '#2f6f8f', '#6a4c93', '#3f7a4f', '#b0413e', '#8a6d1d', '#1f6f6b', '#a23b72' ];
	$color = $slug ? $palette[ abs( crc32( (string) $slug ) ) % count( $palette ) ] : '#c14e00';
	return (string) apply_filters( 'piodesign_event_category_color', $color, $slug );
}

/* ---------------------------------------------------------------------------
 * Page
 * ------------------------------------------------------------------------ */

function piodesign_calendar_html( array $s ) {
	$now   = $s['now'];
	$today = $now->setTime( 0, 0 );
	$body  = '';
	$count = null;

	if ( 'month' === $s['view'] ) {
		$body = piodesign_cal_month_html( $s, $count );
	} elseif ( 'day' === $s['view'] ) {
		$body = piodesign_cal_day_html( $s, $count );
	} else {
		$body = piodesign_cal_list_html( $s, $count );
	}

	// Category chips: every category that has events, current one marked.
	$cats  = [];
	$terms = get_terms(
		[
			'taxonomy'   => 'tribe_events_cat',
			'hide_empty' => true,
		]
	);
	if ( ! is_wp_error( $terms ) ) {
		$chip_view = 'day' === $s['view'] ? 'list' : ( $s['past'] ? 'past' : $s['view'] );
		$chip_date = 'month' === $s['view'] && $s['day'] ? $s['day']->format( 'Y-m' ) : false;
		foreach ( $terms as $t ) {
			$cats[] = [
				'name'   => $t->name,
				'url'    => piodesign_cal_url( $chip_view, $s, [ 'term' => $t, 'date' => $chip_date ] ),
				'color'  => piodesign_event_cat_color( $t->slug ),
				'active' => $s['term'] && (int) $s['term']->term_id === (int) $t->term_id,
			];
		}
	}

	$titles = [
		'list'  => $s['past'] ? 'Minione wydarzenia' : 'Nadchodzące wydarzenia',
		'month' => 'Nadchodzące wydarzenia',
		'day'   => 'Wydarzenia dnia',
	];
	$show_timeline = 'list' === $s['view'] && ! $s['past'] && ! $s['term'] && '' === $s['keyword'] && 1 === $s['page'] && ! $s['day'];

	return piodesign_render(
		'calendar',
		[
			's'        => $s,
			'body'     => $body,
			'title'    => $titles[ $s['view'] ],
			'kicker'   => $s['term'] ? 'Kalendarz · ' . $s['term']->name : 'Kalendarz',
			'desc'     => $s['term'] ? term_description( $s['term'] ) : '',
			'day'      => piodesign_wp_day( $now ),
			'timeline' => $show_timeline ? piodesign_get_timeline( (int) piodesign_option( 'events_weeks' ) ) : null,
			'cats'     => $cats,
			'all_url'  => piodesign_cal_url( 'day' === $s['view'] ? 'list' : ( $s['past'] ? 'past' : $s['view'] ), $s, [ 'term' => false, 'date' => 'month' === $s['view'] && $s['day'] ? $s['day']->format( 'Y-m' ) : false ] ),
			'views'    => [
				'list'  => piodesign_cal_url( 'list', $s ),
				'month' => piodesign_cal_url( 'month', $s ),
			],
			'search'   => [
				'action' => piodesign_cal_url( 'month' === $s['view'] ? 'month' : 'list', $s, [ 'keyword' => false ] ),
				'value'  => $s['keyword'],
				'clear'  => piodesign_cal_url( 'day' === $s['view'] ? 'list' : ( $s['past'] ? 'past' : $s['view'] ), $s, [ 'keyword' => false ] ),
			],
			'count'    => $count,
			'subscribe' => piodesign_option( 'tec_subscribe' ) ? piodesign_cal_subscribe_links() : [],
		]
	);
}

/** "Subscribe" links (Google, iCal, .ics), only when switched on in the settings. */
function piodesign_cal_subscribe_links() {
	if ( ! class_exists( 'Tribe__Events__Main' ) ) {
		return [];
	}
	$feed   = add_query_arg( 'ical', 1, Tribe__Events__Main::instance()->getLink( 'home' ) );
	$webcal = preg_replace( '#^https?://#', 'webcal://', $feed );
	return [
		[ 'Kalendarz Google', 'https://www.google.com/calendar/render?cid=' . rawurlencode( $webcal ) ],
		[ 'iPhone, Mac, Outlook', $webcal ],
		[ 'Pobierz plik .ics', $feed ],
	];
}

/* ---------------------------------------------------------------------------
 * List (upcoming / past)
 * ------------------------------------------------------------------------ */

function piodesign_cal_list_html( array $s, &$count ) {
	$now      = $s['now'];
	$per_page = max( 4, (int) piodesign_option( 'cal_per_page' ) );
	if ( $s['past'] ) {
		$where = [ 'ends_before' => $now->format( 'Y-m-d H:i:s' ) ];
		$order = 'DESC';
	} else {
		$from  = $s['day'] ? $s['day']->format( 'Y-m-d 00:00:00' ) : $now->format( 'Y-m-d H:i:s' );
		$where = [ 'ends_after' => $from ];
		$order = 'ASC';
	}
	[ $posts, $total ] = piodesign_cal_query( $s, $where, $order, $per_page, $s['page'] );
	$count  = $total;
	$events = array_map( static fn( $p ) => piodesign_wp_event( $p, $now ), $posts );
	$pages  = (int) ceil( $total / $per_page );

	$view  = $s['past'] ? 'past' : 'list';
	$pager = piodesign_pager( $s['page'], $pages, static fn( $n ) => piodesign_cal_url( $view, $s, [ 'page' => $n, 'date' => $s['day'] ? $s['day']->format( 'Y-m-d' ) : false ] ) );

	// Month groups (by start date, or by the requested date for events already running).
	$groups = [];
	foreach ( $events as $e ) {
		$ts  = $s['past'] ? $e['start_ts'] : max( $e['start_ts'], $s['day'] ? $s['day']->getTimestamp() : $now->setTime( 0, 0 )->getTimestamp() );
		$key = wp_date( 'Y-m', $ts );
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = [
				'month'  => piodesign_months( 'nom' )[ (int) wp_date( 'n', $ts ) ],
				'year'   => wp_date( 'Y', $ts ),
				'events' => [],
			];
		}
		$groups[ $key ]['events'][] = $e;
	}

	return piodesign_render(
		'cal-list',
		[
			's'        => $s,
			'groups'   => $groups,
			'total'    => $total,
			'pager'    => $pager,
			'past_url' => piodesign_cal_url( 'past', $s, [] ),
			'list_url' => piodesign_cal_url( 'list', $s, [] ),
			'month_url' => piodesign_cal_url( 'month', $s, [] ),
			'from'     => $s['day'],
		]
	);
}

/* ---------------------------------------------------------------------------
 * Day
 * ------------------------------------------------------------------------ */

function piodesign_cal_day_html( array $s, &$count ) {
	$now = $s['now'];
	$day = $s['day'] ?: $now->setTime( 0, 0 );
	[ $posts ] = piodesign_cal_query(
		$s,
		[
			'ends_after'    => $day->format( 'Y-m-d 00:00:00' ),
			'starts_before' => $day->format( 'Y-m-d 23:59:59' ),
		]
	);
	$count  = count( $posts );
	$events = array_map( static fn( $p ) => piodesign_wp_event( $p, $now ), $posts );

	$next = [];
	if ( ! $events ) {
		[ $more ] = piodesign_cal_query( $s, [ 'starts_after' => $day->format( 'Y-m-d 23:59:59' ) ], 'ASC', 3 );
		$next     = array_map( static fn( $p ) => piodesign_wp_event( $p, $now ), $more );
	}

	$lit = piodesign_cal_liturgy( $day, $day, $now );

	return piodesign_render(
		'cal-day',
		[
			's'         => $s,
			'day'       => $day,
			'events'    => $events,
			'next'      => $next,
			'lit'       => $lit[ $day->format( 'Y-m-d' ) ] ?? null,
			'prev_url'  => piodesign_cal_url( 'day', $s, [ 'date' => $day->modify( '-1 day' )->format( 'Y-m-d' ) ] ),
			'next_url'  => piodesign_cal_url( 'day', $s, [ 'date' => $day->modify( '+1 day' )->format( 'Y-m-d' ) ] ),
			'month_url' => piodesign_cal_url( 'month', $s, [ 'date' => $day->format( 'Y-m' ) ] ),
			'is_today'  => $day->format( 'Y-m-d' ) === $now->format( 'Y-m-d' ),
		]
	);
}

/* ---------------------------------------------------------------------------
 * Month
 * ------------------------------------------------------------------------ */

/**
 * Liturgical days for a date range (only when "Parafia: Kalendarz
 * liturgiczny" is active; otherwise an empty array – the computed season is
 * not worth a dot on every day).
 */
function piodesign_cal_liturgy( DateTimeImmutable $from, DateTimeImmutable $to, DateTimeImmutable $now ) {
	if ( ! piodesign_option( 'cal_lit' ) || ! has_filter( 'kalendarz_liturgiczny_day' ) && ! has_filter( 'kalendarz_liturgiczny_days' ) ) {
		return [];
	}
	$range = apply_filters( 'kalendarz_liturgiczny_days', [], $from->format( 'Y-m-d' ), $to->format( 'Y-m-d' ) );
	$out   = [];
	for ( $d = $from; $d <= $to; $d = $d->modify( '+1 day' ) ) {
		$key = $d->format( 'Y-m-d' );
		$ext = is_array( $range ) && isset( $range[ $key ] ) ? $range[ $key ] : apply_filters( 'kalendarz_liturgiczny_day', null, $key );
		if ( is_array( $ext ) && ! empty( $ext['title'] ) ) {
			$out[ $key ] = piodesign_liturgy_day( $d, $ext, $now );
		}
	}
	return $out;
}

/** Feast worth naming in a month cell: anything above an ordinary weekday. */
function piodesign_cal_feast( $lit ) {
	if ( ! $lit ) {
		return '';
	}
	$rank = mb_strtolower( (string) $lit['rank'] );
	if ( '' === $rank || false !== strpos( $rank, 'powszedni' ) || ( $lit['sunday'] && false !== strpos( $rank, 'niedziel' ) ) ) {
		return $lit['sunday'] ? $lit['title'] : '';
	}
	return $lit['title'];
}

function piodesign_cal_month_html( array $s, &$count ) {
	$now   = $s['now'];
	$range = piodesign_cal_month_range( $s );
	[ $posts ] = piodesign_cal_query(
		$s,
		[
			'ends_after'    => $range['start']->format( 'Y-m-d 00:00:00' ),
			'starts_before' => $range['end']->format( 'Y-m-d 23:59:59' ),
		]
	);
	$events = array_map( static fn( $p ) => piodesign_wp_event( $p, $now ), $posts );
	$after  = static function () use ( $s, $range, $now ) {
		[ $more ] = piodesign_cal_query( $s, [ 'starts_after' => $range['last']->format( 'Y-m-d 23:59:59' ) ], 'ASC', 1 );
		return $more ? piodesign_wp_event( $more[0], $now ) : null;
	};
	return piodesign_cal_month_render( $s, $events, $after, $count );
}

/** First / last day of the requested month and of the weeks around it. */
function piodesign_cal_month_range( array $s ) {
	$now   = $s['now'];
	$month = $s['day'] ? $s['day']->modify( 'first day of this month' ) : $now->setTime( 0, 0 )->modify( 'first day of this month' );
	$month = $month->setTime( 0, 0 );
	$last  = $month->modify( 'last day of this month' );
	$sow   = (int) get_option( 'start_of_week', 1 ); // 0 = Sunday … 6.
	$lead  = ( (int) $month->format( 'w' ) - $sow + 7 ) % 7;
	$trail = ( $sow + 6 - (int) $last->format( 'w' ) + 7 ) % 7;
	return [
		'month' => $month,
		'last'  => $last,
		'start' => $month->modify( '-' . $lead . ' days' ),
		'end'   => $last->modify( '+' . $trail . ' days' ),
		'sow'   => $sow,
	];
}

/**
 * Month grid from normalised events (no queries here, so the offline
 * preview renders it too).
 *
 * @param callable $after Returns the first event after the month (or null); called only for an empty month.
 */
function piodesign_cal_month_render( array $s, array $events, callable $after, &$count = null ) {
	$now   = $s['now'];
	$tz    = $now->getTimezone();
	$range = piodesign_cal_month_range( $s );
	$month = $range['month'];
	$last  = $range['last'];
	$start = $range['start'];
	$end   = $range['end'];
	$sow   = $range['sow'];

	// Each event's first and last calendar day (an end at midnight belongs to the day before).
	$spans = [];
	foreach ( $events as $e ) {
		$d0 = new DateTimeImmutable( $e['ymd'], $tz );
		$d1 = new DateTimeImmutable( $e['end_ymd'], $tz );
		if ( ! $e['all_day'] && '00:00' === ( new DateTimeImmutable( $e['end_iso'] ) )->format( 'H:i' ) && $d1 > $d0 ) {
			$d1 = $d1->modify( '-1 day' );
		}
		$spans[] = [ $e, $d0, $d1 ];
	}
	usort(
		$spans,
		static function ( $a, $b ) {
			// Longer bars first, then by start, so lanes stay tidy.
			$la = $a[2]->getTimestamp() - $a[1]->getTimestamp();
			$lb = $b[2]->getTimestamp() - $b[1]->getTimestamp();
			return [ $a[1], -$la, $a[0]['start_ts'] ] <=> [ $b[1], -$lb, $b[0]['start_ts'] ];
		}
	);

	$max_lanes = 2;
	$max_items = max( 1, (int) piodesign_option( 'cal_month_max' ) );
	$lit       = piodesign_cal_liturgy( $start, $end, $now );
	$by_day    = []; // Every event touching a day (dots, agenda).
	$in_month  = [];
	$weeks     = [];

	for ( $w0 = $start; $w0 <= $end; $w0 = $w0->modify( '+7 days' ) ) {
		$w1   = $w0->modify( '+6 days' );
		$days = [];
		for ( $c = 0; $c < 7; $c++ ) {
			$d      = $w0->modify( '+' . $c . ' days' );
			$key    = $d->format( 'Y-m-d' );
			$days[] = [
				'date'   => $d,
				'key'    => $key,
				'col'    => $c + 1,
				'out'    => $d->format( 'Y-m' ) !== $month->format( 'Y-m' ),
				'today'  => $key === $now->format( 'Y-m-d' ),
				'past'   => $key < $now->format( 'Y-m-d' ),
				'sunday' => 7 === (int) $d->format( 'N' ),
				'lit'    => $lit[ $key ] ?? null,
				'url'    => piodesign_cal_url( 'day', $s, [ 'date' => $key ] ),
				'items'  => [],
				'more'   => 0,
			];
		}

		$bars  = [];
		$lanes = []; // lane => last occupied column.
		foreach ( $spans as [ $e, $d0, $d1 ] ) {
			if ( $d1 < $w0 || $d0 > $w1 ) {
				continue;
			}
			$c0 = (int) ( max( $d0, $w0 )->diff( $w0 )->days ) + 1;
			$c1 = (int) ( min( $d1, $w1 )->diff( $w0 )->days ) + 1;
			for ( $c = $c0; $c <= $c1; $c++ ) {
				$by_day[ $days[ $c - 1 ]['key'] ][] = $e;
				if ( ! $days[ $c - 1 ]['out'] ) {
					$in_month[ $e['id'] ] = true;
				}
			}
			if ( $d1 > $d0 ) {
				$lane = null;
				foreach ( $lanes as $l => $end_col ) {
					if ( $end_col < $c0 ) {
						$lane = $l;
						break;
					}
				}
				if ( null === $lane ) {
					$lane = count( $lanes );
				}
				if ( $lane < $max_lanes ) {
					$lanes[ $lane ] = $c1;
					$bars[]         = [
						'e'     => $e,
						'c0'    => $c0,
						'c1'    => $c1,
						'lane'  => $lane,
						'left'  => $d0 < $w0,
						'right' => $d1 > $w1,
					];
				} else {
					for ( $c = $c0; $c <= $c1; $c++ ) {
						$days[ $c - 1 ]['more']++;
					}
				}
			} else {
				$days[ $c0 - 1 ]['items'][] = $e;
			}
		}
		$used = count( $lanes );
		foreach ( $days as &$day ) {
			usort( $day['items'], static fn( $a, $b ) => [ $a['all_day'] ? 0 : 1, $a['start_ts'] ] <=> [ $b['all_day'] ? 0 : 1, $b['start_ts'] ] );
			// Bars take slots; at least one event stays visible next to "+N więcej".
			$room = max( 1, $max_items - $used );
			if ( count( $day['items'] ) > $room ) {
				$keep          = max( 1, $room - 1 );
				$day['more']  += count( $day['items'] ) - $keep;
				$day['items']  = array_slice( $day['items'], 0, $keep );
			}
			$day['all'] = $by_day[ $day['key'] ] ?? [];
		}
		unset( $day );
		$weeks[] = [
			'days'  => $days,
			'bars'  => $bars,
			'lanes' => $used,
		];
	}
	$count = count( $in_month );

	// Week-day header in the site's order.
	$heads = [];
	for ( $k = 0; $k < 7; $k++ ) {
		$n       = ( ( $sow + $k ) % 7 ) ?: 7; // PHP 'N'.
		$heads[] = [ piodesign_weekdays( true )[ $n ], piodesign_weekdays()[ $n ], 7 === $n ];
	}

	$next_event = $count ? null : $after();

	$prev = $month->modify( '-1 month' );
	$next = $month->modify( '+1 month' );
	$cur  = $month->format( 'Y-m' ) === $now->format( 'Y-m' );

	return piodesign_render(
		'cal-month',
		[
			's'          => $s,
			'month'      => $month,
			'weeks'      => $weeks,
			'heads'      => $heads,
			'count'      => $count,
			'agenda'     => piodesign_cal_agenda( $by_day, $month ),
			'prev'       => [ piodesign_months( 'nom' )[ (int) $prev->format( 'n' ) ], piodesign_cal_url( 'month', $s, [ 'date' => $prev->format( 'Y-m' ) ] ) ],
			'next'       => [ piodesign_months( 'nom' )[ (int) $next->format( 'n' ) ], piodesign_cal_url( 'month', $s, [ 'date' => $next->format( 'Y-m' ) ] ) ],
			'today_url'  => $cur ? '' : piodesign_cal_url( 'month', $s, [] ),
			'next_event' => $next_event,
			'next_month_url' => $next_event ? piodesign_cal_url( 'month', $s, [ 'date' => substr( $next_event['ymd'], 0, 7 ) ] ) : '',
			'list_url'   => piodesign_cal_url( 'list', $s, [ 'date' => $month->format( 'Y-m-d' ) > $now->format( 'Y-m-d' ) ? $month->format( 'Y-m-d' ) : false ] ),
		]
	);
}

/**
 * Phone agenda for the month: days with something on, each event listed once
 * (multi-day events on their first day in this month).
 */
function piodesign_cal_agenda( array $by_day, DateTimeImmutable $month ) {
	ksort( $by_day );
	$seen = [];
	$out  = [];
	foreach ( $by_day as $key => $list ) {
		if ( substr( $key, 0, 7 ) !== $month->format( 'Y-m' ) ) {
			continue;
		}
		$items = [];
		foreach ( $list as $e ) {
			if ( isset( $seen[ $e['id'] ] ) ) {
				continue;
			}
			$seen[ $e['id'] ] = true;
			$items[]          = $e;
		}
		if ( $items ) {
			usort( $items, static fn( $a, $b ) => [ $a['multi'] || $a['all_day'] ? 0 : 1, $a['start_ts'] ] <=> [ $b['multi'] || $b['all_day'] ? 0 : 1, $b['start_ts'] ] );
			$out[ $key ] = $items;
		}
	}
	return $out;
}
