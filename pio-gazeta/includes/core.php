<?php
/**
 * Pure helpers shared by the WordPress layer and the offline preview build.
 * Nothing in this file may call WordPress APIs directly (only the escaping
 * functions, which the preview build shims).
 *
 * @package PioGazeta
 */

defined( 'PIO_GAZETA_CORE' ) || define( 'PIO_GAZETA_CORE', true );

/* ---------------------------------------------------------------------------
 * Polish calendar vocabulary
 * ------------------------------------------------------------------------ */

function pio_gazeta_months( $form = 'gen' ) {
	$gen = [ 1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia' ];
	$nom = [ 1 => 'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień' ];
	$abbr = [ 1 => 'sty', 'lut', 'mar', 'kwi', 'maj', 'cze', 'lip', 'sie', 'wrz', 'paź', 'lis', 'gru' ];
	return 'nom' === $form ? $nom : ( 'abbr' === $form ? $abbr : $gen );
}

function pio_gazeta_weekdays( $short = false ) {
	// Indexed by PHP 'N' (1 = Monday).
	return $short
		? [ 1 => 'pon', 'wt', 'śr', 'czw', 'pt', 'sob', 'niedz' ]
		: [ 1 => 'poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek', 'sobota', 'niedziela' ];
}

/** "24 września", "24 września 2026", "czwartek, 24 września 2026". */
function pio_gazeta_date( DateTimeInterface $d, $with_year = false, $with_weekday = false ) {
	$out = (int) $d->format( 'j' ) . ' ' . pio_gazeta_months()[ (int) $d->format( 'n' ) ];
	if ( $with_year ) {
		$out .= ' ' . $d->format( 'Y' );
	}
	if ( $with_weekday ) {
		$out = pio_gazeta_weekdays()[ (int) $d->format( 'N' ) ] . ', ' . $out;
	}
	return $out;
}

function pio_gazeta_ucfirst( $s ) {
	return mb_strtoupper( mb_substr( $s, 0, 1 ) ) . mb_substr( $s, 1 );
}

function pio_gazeta_roman( $n ) {
	$map = [ 'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 ];
	$out = '';
	foreach ( $map as $r => $v ) {
		while ( $n >= $v ) {
			$out .= $r;
			$n   -= $v;
		}
	}
	return $out;
}

/** Polish plural: 1 zdjęcie, 2 zdjęcia, 5 zdjęć. */
function pio_gazeta_plural( $n, $one, $few, $many ) {
	$n = abs( (int) $n );
	if ( 1 === $n ) {
		return $one;
	}
	$d = $n % 10;
	$t = $n % 100;
	return ( $d >= 2 && $d <= 4 && ( $t < 12 || $t > 14 ) ) ? $few : $many;
}

/* ---------------------------------------------------------------------------
 * Liturgical year (seasons only, no individual feasts)
 * ------------------------------------------------------------------------ */

function pio_gazeta_easter( $year, DateTimeZone $tz ) {
	// Anonymous Gregorian algorithm.
	$a = $year % 19;
	$b = intdiv( $year, 100 );
	$c = $year % 100;
	$d = intdiv( $b, 4 );
	$e = $b % 4;
	$f = intdiv( $b + 8, 25 );
	$g = intdiv( $b - $f + 1, 3 );
	$h = ( 19 * $a + $b - $d - $g + 15 ) % 30;
	$i = intdiv( $c, 4 );
	$k = $c % 4;
	$l = ( 32 + 2 * $e + 2 * $i - $h - $k ) % 7;
	$m = intdiv( $a + 11 * $h + 22 * $l, 451 );
	$month = intdiv( $h + $l - 7 * $m + 114, 31 );
	$day   = ( ( $h + $l - 7 * $m + 114 ) % 31 ) + 1;
	return new DateTimeImmutable( sprintf( '%04d-%02d-%02d', $year, $month, $day ), $tz );
}

/** Sunday on or before $d. */
function pio_gazeta_sunday_before( DateTimeImmutable $d ) {
	$n = (int) $d->format( 'N' );
	return 7 === $n ? $d : $d->modify( '-' . $n . ' days' );
}

function pio_gazeta_advent1( $year, DateTimeZone $tz ) {
	$xmas = new DateTimeImmutable( $year . '-12-25', $tz );
	$n    = (int) $xmas->format( 'N' );
	$sun  = $xmas->modify( '-' . ( 7 === $n ? 7 : $n ) . ' days' ); // last Sunday strictly before Christmas
	return $sun->modify( '-21 days' );
}

function pio_gazeta_baptism( $year, DateTimeZone $tz ) {
	$epiphany = new DateTimeImmutable( $year . '-01-06', $tz );
	$n        = (int) $epiphany->format( 'N' );
	return $epiphany->modify( '+' . ( 7 - $n + ( 7 === $n ? 7 : 0 ) ) . ' days' );
}

/**
 * Liturgical season for a day.
 *
 * @return array{slug:string,label:string,week:string,color:string,color_name:string}
 */
function pio_gazeta_liturgy( DateTimeImmutable $now ) {
	$tz   = $now->getTimezone();
	$d    = $now->setTime( 0, 0 );
	$y    = (int) $d->format( 'Y' );
	$days = static function ( $a, $b ) {
		return (int) round( ( $b->getTimestamp() - $a->getTimestamp() ) / 86400 );
	};

	$colors = [
		'green'  => [ '#3f7a4f', 'zielony' ],
		'violet' => [ '#6b4a8f', 'fioletowy' ],
		'white'  => [ '#c79a3e', 'biały' ],
		'red'    => [ '#b3261e', 'czerwony' ],
	];
	$make = static function ( $slug, $label, $week, $color ) use ( $colors ) {
		return [
			'slug'       => $slug,
			'label'      => $label,
			'week'       => $week,
			'color'      => $colors[ $color ][0],
			'color_name' => $colors[ $color ][1],
		];
	};

	$advent1   = pio_gazeta_advent1( $y, $tz );
	$christmas = new DateTimeImmutable( $y . '-12-25', $tz );
	$baptism   = pio_gazeta_baptism( $y, $tz );
	$easter    = pio_gazeta_easter( $y, $tz );
	$ash       = $easter->modify( '-46 days' );
	$thursday  = $easter->modify( '-3 days' );
	$pentecost = $easter->modify( '+49 days' );

	if ( $d >= $christmas || $d <= $baptism ) {
		return $make( 'christmas', 'Okres Bożego Narodzenia', '', 'white' );
	}
	if ( $d >= $advent1 ) {
		$w = intdiv( $days( $advent1, $d ), 7 ) + 1;
		return $make( 'advent', 'Adwent', pio_gazeta_roman( $w ) . ' tydzień', 'violet' );
	}
	if ( $d >= $ash && $d < $thursday ) {
		$first = $ash->modify( '+4 days' );
		$week  = $d < $first ? 'po Popielcu' : pio_gazeta_roman( intdiv( $days( $first, $d ), 7 ) + 1 ) . ' tydzień';
		return $make( 'lent', 'Wielki Post', $week, 'violet' );
	}
	if ( $d >= $thursday && $d < $easter ) {
		return $make( 'triduum', 'Triduum Paschalne', '', 'red' );
	}
	if ( $d == $pentecost ) { // phpcs:ignore Universal.Operators.StrictComparisons
		return $make( 'pentecost', 'Zesłanie Ducha Świętego', '', 'red' );
	}
	if ( $d >= $easter && $d < $pentecost ) {
		$w = intdiv( $days( $easter, $d ), 7 ) + 1;
		return $make( 'easter', 'Okres wielkanocny', pio_gazeta_roman( $w ) . ' tydzień', 'white' );
	}
	$sun  = pio_gazeta_sunday_before( $d );
	$week = $d < $ash
		? intdiv( $days( $baptism, $sun ), 7 ) + 1
		: 34 - intdiv( $days( $sun, $advent1 ), 7 ) + 1;
	return $make( 'ordinary', 'Okres zwykły', pio_gazeta_roman( max( 1, $week ) ) . ' tydzień', 'green' );
}

/**
 * Everything the dateline shows about a day: weekday, date, liturgical day,
 * colour and parish feast.
 *
 * @param array|null $ext Day from the "Parafia: Kalendarz liturgiczny" plugin
 *                        (filter kalendarz_liturgiczny_day). When null, the
 *                        season is computed here as a fallback.
 */
function pio_gazeta_day( DateTimeImmutable $now, $ext = null ) {
	$base = [
		'weekday' => pio_gazeta_weekdays()[ (int) $now->format( 'N' ) ],
		'date'    => pio_gazeta_date( $now, true ),
		'iso'     => $now->format( 'Y-m-d' ),
	];
	$list = static function ( $v ) {
		$v = is_array( $v ) ? $v : ( '' === (string) $v ? [] : [ (string) $v ] );
		return implode( ', ', array_filter( array_map( 'trim', $v ) ) );
	};

	if ( is_array( $ext ) && ! empty( $ext['title'] ) ) {
		$color = (string) ( $ext['color_hex'] ?? '' );
		return $base + [
			'title'       => (string) $ext['title'],
			'rank'        => (string) ( $ext['rank_label'] ?? '' ),
			'color'       => preg_match( '/^#[0-9a-f]{3,8}$/i', $color ) ? $color : '#3f7a4f',
			'color_label' => (string) ( $ext['color_label'] ?? '' ),
			'parish'      => $list( $ext['parish'] ?? [] ),
			'source'      => 'kalendarz',
		];
	}

	$l = pio_gazeta_liturgy( $now );
	return $base + [
		'title'       => trim( $l['label'] . ( $l['week'] ? ', ' . $l['week'] : '' ) ),
		'rank'        => '',
		'color'       => $l['color'],
		'color_label' => $l['color_name'],
		'parish'      => '',
		'source'      => 'fallback',
	];
}

/* ---------------------------------------------------------------------------
 * Categories
 * ------------------------------------------------------------------------ */

function pio_gazeta_category_color( $slug ) {
	$palette = [ '#c14e00', '#9a6a1f', '#3f7a4f', '#3d5a80', '#7b3f6e', '#2f6f73' ];
	$fixed   = [ 'aktualnosci' => '#c14e00' ];
	$color   = isset( $fixed[ $slug ] ) ? $fixed[ $slug ] : $palette[ crc32( (string) $slug ) % count( $palette ) ];
	return function_exists( 'apply_filters' ) ? apply_filters( 'pio_gazeta_category_color', $color, $slug ) : $color;
}

/* ---------------------------------------------------------------------------
 * Images: 16:9 "smart frame"
 * ------------------------------------------------------------------------ */

/**
 * Decide how an image fills a 16:9 frame. Photos are cropped (cover); posters,
 * portraits and squares are shown whole on a blurred copy of themselves
 * (contain) so no text or face is cut off.
 *
 * @param array  $img     [src, srcset?, w, h, alt?].
 * @param string $context 'post' | 'event'.
 */
function pio_gazeta_frame( $img, $context = 'post' ) {
	if ( empty( $img['src'] ) ) {
		return null;
	}
	$w     = max( 1, (int) ( $img['w'] ?? 16 ) );
	$h     = max( 1, (int) ( $img['h'] ?? 9 ) );
	$ratio = $w / $h;
	$fit   = 'event' === $context
		? ( ( $ratio < 1.55 || $ratio > 2.0 ) ? 'contain' : 'cover' )
		: ( $ratio < 1.05 ? 'contain' : 'cover' );
	return $img + [
		'srcset' => '',
		'alt'    => '',
		'fit'    => $fit,
	];
}

/* ---------------------------------------------------------------------------
 * Posts
 * ------------------------------------------------------------------------ */

/**
 * @param array $raw id,title,url,date(DateTimeImmutable),excerpt,category[name,slug],image,gallery[],photo_count,words.
 */
function pio_gazeta_post( array $raw, DateTimeImmutable $now ) {
	$date = $raw['date'];
	$age  = $now->getTimestamp() - $date->getTimestamp();
	$cat  = $raw['category'] ?? [ 'name' => 'Aktualności', 'slug' => 'aktualnosci' ];

	$excerpt = trim( preg_replace( '/\s+/u', ' ', $raw['excerpt'] ?? '' ) );
	$excerpt = preg_replace( '/\s*(\[(…|\.\.\.|&hellip;)\]|\[\s*…\s*\]|…)\s*$/u', '', $excerpt );
	if ( mb_strlen( $excerpt ) > 190 ) {
		$excerpt = mb_substr( $excerpt, 0, 190 );
		$excerpt = preg_replace( '/\s+\S*$/u', '', $excerpt );
	}
	$excerpt = rtrim( $excerpt, " ,;:–-" ) . '…';

	$days_ago = (int) floor( ( $now->setTime( 0, 0 )->getTimestamp() - $date->setTime( 0, 0 )->getTimestamp() ) / 86400 );
	if ( 0 === $days_ago ) {
		$rel = 'dziś';
	} elseif ( 1 === $days_ago ) {
		$rel = 'wczoraj';
	} elseif ( $days_ago < 7 ) {
		$rel = $days_ago . ' dni temu';
	} else {
		$rel = '';
	}

	$photos = (int) ( $raw['photo_count'] ?? 0 );

	return [
		'id'          => $raw['id'],
		'title'       => $raw['title'],
		'url'         => $raw['url'],
		'iso'         => $date->format( DATE_ATOM ),
		'date'        => pio_gazeta_date( $date, (int) $date->format( 'Y' ) !== (int) $now->format( 'Y' ) ),
		'date_long'   => pio_gazeta_date( $date, true, true ),
		'relative'    => $rel,
		'is_new'      => $age >= 0 && $age < 3 * 86400,
		'excerpt'     => $excerpt,
		'category'    => [
			'name'  => $cat['name'],
			'slug'  => $cat['slug'],
			'color' => pio_gazeta_category_color( $cat['slug'] ),
		],
		'image'       => pio_gazeta_frame( $raw['image'] ?? [], 'post' ),
		'gallery'     => array_slice( array_values( array_filter( $raw['gallery'] ?? [] ) ), 0, 4 ),
		'photo_count' => $photos,
		'photo_label' => $photos > 1 ? $photos . ' ' . pio_gazeta_plural( $photos, 'zdjęcie', 'zdjęcia', 'zdjęć' ) : '',
		'read_min'    => max( 1, (int) round( ( $raw['words'] ?? 0 ) / 200 ) ),
	];
}

/* ---------------------------------------------------------------------------
 * Events
 * ------------------------------------------------------------------------ */

/**
 * @param array $raw id,title,url,start,end (DateTimeImmutable),all_day,excerpt,content,
 *                   image,venue[name,address,city],organizer,category,ics_url,featured.
 */
function pio_gazeta_event( array $raw, DateTimeImmutable $now ) {
	/** @var DateTimeImmutable $s */
	$s = $raw['start'];
	/** @var DateTimeImmutable $e */
	$e       = $raw['end'];
	$all_day = ! empty( $raw['all_day'] );
	$multi   = $s->format( 'Y-m-d' ) !== $e->format( 'Y-m-d' );
	$m_gen   = pio_gazeta_months();

	// Time label.
	if ( $multi ) {
		$same_month = $s->format( 'Y-m' ) === $e->format( 'Y-m' );
		$when       = $same_month
			? $s->format( 'j' ) . '–' . $e->format( 'j' ) . ' ' . $m_gen[ (int) $e->format( 'n' ) ]
			: pio_gazeta_date( $s ) . ' – ' . pio_gazeta_date( $e );
		$time = $all_day ? 'wydarzenie wielodniowe' : $s->format( 'G:i' ) . ' → ' . $e->format( 'G:i' );
	} else {
		$when = pio_gazeta_date( $s, false, true );
		$time = $all_day ? 'cały dzień' : $s->format( 'G:i' ) . ( $e > $s ? '–' . $e->format( 'G:i' ) : '' );
	}

	// Status & relative label.
	$today     = $now->setTime( 0, 0 );
	$start_day = $s->setTime( 0, 0 );
	$diff_days = (int) round( ( $start_day->getTimestamp() - $today->getTimestamp() ) / 86400 );
	$effective_end = $all_day ? $e->setTime( 23, 59, 59 ) : $e;

	if ( $effective_end < $now ) {
		$status = 'past';
		$rel    = 'zakończone';
	} elseif ( $s <= $now || ( $all_day && $diff_days <= 0 ) ) {
		$status = 'ongoing';
		$rel    = $multi ? 'trwa' : 'dziś';
		if ( ! $multi && ! $all_day ) {
			$rel = 'teraz';
		}
	} else {
		$status = 'upcoming';
		if ( 0 === $diff_days ) {
			$rel = 'dziś';
		} elseif ( 1 === $diff_days ) {
			$rel = 'jutro';
		} elseif ( 2 === $diff_days ) {
			$rel = 'pojutrze';
		} elseif ( $diff_days < 7 ) {
			$rel = 'za ' . $diff_days . ' dni';
		} elseif ( $diff_days < 14 ) {
			$rel = 'za tydzień';
		} elseif ( $diff_days < 32 ) {
			$w   = (int) round( $diff_days / 7 );
			$rel = 'za ' . $w . ' ' . pio_gazeta_plural( $w, 'tydzień', 'tygodnie', 'tygodni' );
		} else {
			$rel = pio_gazeta_date( $s );
		}
	}

	$progress = 0.0;
	$day_of   = '';
	if ( 'ongoing' === $status && $multi ) {
		$span     = max( 1, $effective_end->getTimestamp() - $s->getTimestamp() );
		$progress = min( 1, max( 0, ( $now->getTimestamp() - $s->getTimestamp() ) / $span ) );
		$total    = (int) round( ( $e->setTime( 0, 0 )->getTimestamp() - $start_day->getTimestamp() ) / 86400 ) + 1;
		$cur      = (int) round( ( $today->getTimestamp() - $start_day->getTimestamp() ) / 86400 ) + 1;
		$day_of   = 'dzień ' . $cur . ' z ' . $total;
	}

	$venue = $raw['venue'] ?? [];
	$addr  = trim( implode( ', ', array_filter( [ $venue['address'] ?? '', $venue['city'] ?? '' ] ) ) );
	$map   = $addr ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( ( $venue['name'] ?? '' ) . ', ' . $addr ) ) : '';

	// Google Calendar link (UTC).
	$utc  = new DateTimeZone( 'UTC' );
	$gfmt = $all_day
		? $s->format( 'Ymd' ) . '/' . $e->modify( '+1 day' )->format( 'Ymd' )
		: $s->setTimezone( $utc )->format( 'Ymd\THis\Z' ) . '/' . $e->setTimezone( $utc )->format( 'Ymd\THis\Z' );
	$gcal = 'https://calendar.google.com/calendar/render?' . http_build_query(
		[
			'action'   => 'TEMPLATE',
			'text'     => $raw['title'],
			'dates'    => $gfmt,
			'details'  => $raw['url'],
			'location' => trim( ( $venue['name'] ?? '' ) . ( $addr ? ', ' . $addr : '' ), ', ' ),
		],
		'',
		'&',
		PHP_QUERY_RFC3986
	);

	$excerpt = trim( preg_replace( '/\s+/u', ' ', $raw['excerpt'] ?? '' ) );
	if ( mb_strlen( $excerpt ) > 170 ) {
		$excerpt = preg_replace( '/\s+\S*$/u', '', mb_substr( $excerpt, 0, 170 ) ) . '…';
	}

	return [
		'id'        => $raw['id'],
		'title'     => $raw['title'],
		'url'       => $raw['url'],
		'start_ts'  => $s->getTimestamp(),
		'end_ts'    => $effective_end->getTimestamp(),
		'start_iso' => $s->format( DATE_ATOM ),
		'end_iso'   => $e->format( DATE_ATOM ),
		'ymd'       => $s->format( 'Y-m-d' ),
		'end_ymd'   => $e->format( 'Y-m-d' ),
		'day'       => $s->format( 'j' ),
		'end_day'   => $e->format( 'j' ),
		'month'     => pio_gazeta_months( 'abbr' )[ (int) $s->format( 'n' ) ],
		'month_gen' => $m_gen[ (int) $s->format( 'n' ) ],
		'month_nom' => pio_gazeta_months( 'nom' )[ (int) $s->format( 'n' ) ],
		'year'      => $s->format( 'Y' ),
		'weekday'   => pio_gazeta_weekdays()[ (int) $s->format( 'N' ) ],
		'weekday_s' => pio_gazeta_weekdays( true )[ (int) $s->format( 'N' ) ],
		'when'      => $when,
		'time'      => $time,
		'all_day'   => $all_day,
		'multi'     => $multi,
		'status'    => $status,
		'relative'  => $rel,
		'diff_days' => $diff_days,
		'progress'  => $progress,
		'day_of'    => $day_of,
		'excerpt'   => $excerpt,
		'content'   => $raw['content'] ?? '',
		'image'     => pio_gazeta_frame( $raw['image'] ?? [], 'event' ),
		'venue'     => [
			'name'    => $venue['name'] ?? '',
			'address' => $addr,
			'map'     => $map,
		],
		'organizer' => $raw['organizer'] ?? [],
		'category'  => $raw['category'] ?? null,
		'featured'  => ! empty( $raw['featured'] ),
		'gcal'      => $gcal,
		'ics'       => $raw['ics_url'] ?? '',
		'cost'      => $raw['cost'] ?? '',
	];
}

/**
 * Split events for the homepage: ongoing multi-day events, one featured
 * "next up" event and the rest grouped by relative week.
 */
function pio_gazeta_group_events( array $events, DateTimeImmutable $now ) {
	$ongoing = [];
	$rest    = [];
	foreach ( $events as $ev ) {
		if ( 'past' === $ev['status'] ) {
			continue;
		}
		if ( 'ongoing' === $ev['status'] && $ev['multi'] ) {
			$ongoing[] = $ev;
		} else {
			$rest[] = $ev;
		}
	}
	$featured = array_shift( $rest );

	$today     = $now->setTime( 0, 0 );
	$n         = (int) $today->format( 'N' );
	$week_end  = $today->modify( '+' . ( 7 - $n ) . ' days' )->setTime( 23, 59, 59 );
	$next_end  = $week_end->modify( '+7 days' );
	$groups    = [];
	foreach ( $rest as $ev ) {
		if ( $ev['start_ts'] <= $week_end->getTimestamp() ) {
			$key = 'W tym tygodniu';
		} elseif ( $ev['start_ts'] <= $next_end->getTimestamp() ) {
			$key = 'W przyszłym tygodniu';
		} else {
			$key = 'Później';
		}
		$groups[ $key ][] = $ev;
	}
	return [
		'ongoing'  => $ongoing,
		'featured' => $featured,
		'groups'   => $groups,
	];
}

/**
 * Five-week strip starting on this week's Monday. Multi-day events become
 * bars packed into lanes; single-day events become dots on their day.
 */
function pio_gazeta_timeline( array $events, DateTimeImmutable $now, $weeks = 5 ) {
	$today = $now->setTime( 0, 0 );
	$start = $today->modify( '-' . ( (int) $today->format( 'N' ) - 1 ) . ' days' );
	$len   = $weeks * 7;
	$days  = [];
	$abbr  = pio_gazeta_months( 'abbr' );
	for ( $i = 0; $i < $len; $i++ ) {
		$d      = $start->modify( '+' . $i . ' days' );
		$days[] = [
			'ymd'     => $d->format( 'Y-m-d' ),
			'day'     => $d->format( 'j' ),
			'wd'      => pio_gazeta_weekdays( true )[ (int) $d->format( 'N' ) ],
			'sunday'  => 7 === (int) $d->format( 'N' ),
			'today'   => $d == $today, // phpcs:ignore Universal.Operators.StrictComparisons
			'past'    => $d < $today,
			'month'   => ( 0 === $i || '1' === $d->format( 'j' ) ) ? $abbr[ (int) $d->format( 'n' ) ] : '',
			'dots'    => [],
		];
	}
	$index = array_flip( array_column( $days, 'ymd' ) );
	$bars  = [];
	$lanes = [];
	foreach ( $events as $ev ) {
		if ( $ev['multi'] ) {
			$a = $index[ $ev['ymd'] ] ?? ( $ev['ymd'] < $days[0]['ymd'] ? 0 : null );
			$b = $index[ $ev['end_ymd'] ] ?? ( $ev['end_ymd'] > $days[ $len - 1 ]['ymd'] ? $len - 1 : null );
			if ( null === $a || null === $b || $ev['end_ymd'] < $days[0]['ymd'] ) {
				continue;
			}
			$lane = 0;
			while ( isset( $lanes[ $lane ] ) && $lanes[ $lane ] >= $a ) {
				$lane++;
			}
			$lanes[ $lane ] = $b;
			$bars[]         = [
				'ev'       => $ev,
				'from'     => $a + 1,
				'to'       => $b + 2,
				'lane'     => $lane,
				'cut_l'    => $ev['ymd'] < $days[0]['ymd'],
				'cut_r'    => $ev['end_ymd'] > $days[ $len - 1 ]['ymd'],
			];
		} elseif ( isset( $index[ $ev['ymd'] ] ) ) {
			$days[ $index[ $ev['ymd'] ] ]['dots'][] = $ev;
		}
	}
	return [
		'days'  => $days,
		'bars'  => $bars,
		'lanes' => max( 1, count( $lanes ) ),
		'label' => pio_gazeta_date( $start ) . ' – ' . pio_gazeta_date( $start->modify( '+' . ( $len - 1 ) . ' days' ) ),
	];
}

/* ---------------------------------------------------------------------------
 * Rendering
 * ------------------------------------------------------------------------ */

function pio_gazeta_render( $template, array $vars = [] ) {
	$file = PIO_GAZETA_DIR . 'templates/' . $template . '.php';
	if ( function_exists( 'apply_filters' ) ) {
		$file = apply_filters( 'pio_gazeta_template', $file, $template, $vars );
	}
	if ( ! is_readable( $file ) ) {
		return '';
	}
	extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
	ob_start();
	include $file;
	return ob_get_clean();
}

function pio_gazeta_icon( $name ) {
	$paths = [
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'camera'   => '<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-l'  => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'link'     => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
		'user'     => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.5 4-5 7-5s5.8 1.5 7 5"/>',
		'book'     => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20"/>',
		'download' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
	];
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="pio-i pio-i--' . $name . '" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}
