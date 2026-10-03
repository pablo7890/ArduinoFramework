<?php
/**
 * Shortcodes: [pio_sakramenty], [pio_cytaty], [pio_informacje], [pio_liturgia].
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Sacraments
 * ------------------------------------------------------------------------ */

/** Image for an uploads-relative path ("2025/11/x.jpg"), if that file is in the media library. */
function piodesign_wp_upload_image( $path ) {
	if ( ! $path ) {
		return [];
	}
	$url = trailingslashit( wp_get_upload_dir()['baseurl'] ) . ltrim( $path, '/' );
	$id  = attachment_url_to_postid( $url );
	return $id ? piodesign_wp_image( $id, 'large' ) : [];
}

/** First paragraph of real text (skips headings and short lines). */
function piodesign_first_paragraph( $html, $max = 190 ) {
	if ( preg_match_all( '#<p[^>]*>(.*?)</p>#is', (string) $html, $m ) ) {
		foreach ( $m[1] as $p ) {
			$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $p ), ENT_QUOTES, 'UTF-8' ) ) );
			if ( mb_strlen( $text ) >= 60 ) {
				return mb_strlen( $text ) > $max ? preg_replace( '/\s+\S*$/u', '', mb_substr( $text, 0, $max ) ) . '…' : $text;
			}
		}
	}
	return '';
}

/**
 * Slides from the child pages of a parent page (menu order). Text: the page
 * excerpt, else the homepage slider text, else the first paragraph. Image:
 * featured image, else the slider image, else the first image in the page.
 */
function piodesign_get_sacraments( array $atts ) {
	if ( $atts['ids'] ) {
		$pages = get_posts(
			[
				'post_type'      => 'page',
				'post__in'       => array_map( 'intval', explode( ',', $atts['ids'] ) ),
				'orderby'        => 'post__in',
				'posts_per_page' => 20,
			]
		);
	} else {
		$parent = get_page_by_path( $atts['parent'] );
		if ( ! $parent ) {
			return [];
		}
		$pages = get_pages(
			[
				'parent'      => $parent->ID,
				'sort_column' => 'menu_order,post_title',
			]
		);
	}
	$exclude  = array_filter( array_map( 'trim', explode( ',', $atts['exclude'] ) ) );
	$defaults = piodesign_sacrament_defaults();
	$slides   = [];
	foreach ( $pages as $page ) {
		if ( in_array( $page->post_name, $exclude, true ) ) {
			continue;
		}
		$def   = $defaults[ $page->post_name ] ?? [ '', '', '' ];
		$image = piodesign_wp_image( get_post_thumbnail_id( $page ), 'large' );
		if ( ! $image ) {
			$image = piodesign_wp_upload_image( $def[1] );
		}
		if ( ! $image && preg_match( '/wp-image-(\d+)/', $page->post_content, $m ) ) {
			$image = piodesign_wp_image( (int) $m[1], 'large' );
		}
		$text = trim( $page->post_excerpt ) ?: ( $def[0] ?: piodesign_first_paragraph( apply_filters( 'the_content', $page->post_content ) ) );

		$slides[] = [
			'id'    => $page->ID,
			'slug'  => $page->post_name,
			'title' => $def[2] ?: html_entity_decode( get_the_title( $page ), ENT_QUOTES, 'UTF-8' ),
			'text'  => $text,
			'url'   => get_permalink( $page ),
			'image' => $image ? piodesign_frame( $image, 'post' ) : null,
		];
	}
	return $slides;
}

add_shortcode(
	'pio_sakramenty',
	static function ( $atts ) {
		$atts = shortcode_atts(
			[
				'parent'   => piodesign_option( 'sac_parent' ),
				'ids'      => '',
				'exclude'  => piodesign_option( 'sac_exclude' ),
				'title'    => piodesign_option( 'sac_title' ),
				'kicker'   => piodesign_option( 'sac_kicker' ),
				'autoplay' => piodesign_option( 'sac_autoplay' ),
				'button'   => piodesign_option( 'sac_button' ),
			],
			$atts,
			'pio_sakramenty'
		);
		piodesign_enqueue_late();
		return piodesign_cached(
			'sacraments',
			$atts,
			static fn() => piodesign_render(
				'sacraments',
				[
					'slides'   => piodesign_get_sacraments( $atts ),
					'title'    => $atts['title'],
					'kicker'   => $atts['kicker'],
					'autoplay' => max( 0, (int) $atts['autoplay'] ),
					'button'   => $atts['button'],
				]
			)
		);
	}
);

/* ---------------------------------------------------------------------------
 * Quotes
 * ------------------------------------------------------------------------ */

add_shortcode(
	'pio_cytaty',
	static function ( $atts, $content = '' ) {
		$atts = shortcode_atts(
			[
				'title'    => piodesign_option( 'q_title' ),
				'autor'    => piodesign_option( 'q_author' ),
				'zdjecie'  => piodesign_option( 'q_photo' ),
				'autoplay' => piodesign_option( 'q_autoplay' ),
			],
			$atts,
			'pio_cytaty'
		);
		piodesign_enqueue_late();

		$source = trim( wp_strip_all_tags( (string) $content ) ) ?: piodesign_option( 'cytaty' );
		$photo  = '';
		if ( is_numeric( $atts['zdjecie'] ) ) {
			$photo = (string) wp_get_attachment_image_url( (int) $atts['zdjecie'], 'thumbnail' );
		} elseif ( $atts['zdjecie'] ) {
			$photo = $atts['zdjecie'];
		}
		return piodesign_render(
			'quotes',
			[
				'quotes'   => piodesign_quotes( $source ),
				'title'    => $atts['title'],
				'author'   => $atts['autor'],
				'photo'    => $photo,
				'autoplay' => max( 0, (int) $atts['autoplay'] ),
			]
		);
	}
);

/* ---------------------------------------------------------------------------
 * Parish information (before the footer)
 * ------------------------------------------------------------------------ */

function piodesign_info_data( DateTimeImmutable $now, array $o ) {
	$lit = piodesign_liturgy( $now );
	return [
		'masses'   => piodesign_mass_schedule( $o ),
		'office'   => piodesign_office_hours( $o['kancelaria'] ),
		'o'        => $o,
		'partners' => piodesign_partners( $o['partnerzy'] ),
		'advent'   => 'advent' === $lit['slug'],
		'today'    => (int) $now->format( 'N' ),
	];
}

add_shortcode(
	'pio_informacje',
	static function ( $atts ) {
		$atts = shortcode_atts(
			[
				'title'  => piodesign_option( 'info_title' ),
				'kicker' => piodesign_option( 'info_kicker' ),
			],
			$atts,
			'pio_informacje'
		);
		piodesign_enqueue_late();
		$now = piodesign_now();
		return piodesign_render( 'info', piodesign_info_data( $now, piodesign_options() ) + [ 'slots' => piodesign_mass_slots( $now ) ] + $atts );
	}
);

/* ---------------------------------------------------------------------------
 * Liturgy of the day
 * ------------------------------------------------------------------------ */

function piodesign_wp_liturgy_days( $count ) {
	$now   = piodesign_now();
	$start = $now->setTime( 0, 0 );
	$end   = $start->modify( '+' . ( $count - 1 ) . ' days' );
	$week  = apply_filters( 'kalendarz_liturgiczny_days', [], $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );
	$days  = [];
	for ( $i = 0; $i < $count; $i++ ) {
		$d   = $start->modify( '+' . $i . ' days' );
		$key = $d->format( 'Y-m-d' );
		$ext = is_array( $week ) && isset( $week[ $key ] ) ? $week[ $key ] : apply_filters( 'kalendarz_liturgiczny_day', null, $key );
		$days[] = piodesign_liturgy_day( $d, $ext, $now );
	}
	return $days;
}

add_shortcode(
	'pio_liturgia',
	static function ( $atts ) {
		$atts = shortcode_atts(
			[
				'dni'     => piodesign_option( 'lit_days' ),
				'title'   => piodesign_option( 'lit_title' ),
				'kicker'  => piodesign_option( 'lit_kicker' ),
				'podcast' => piodesign_option( 'lit_podcast_url' ),
			],
			$atts,
			'pio_liturgia'
		);
		piodesign_enqueue_late();
		return piodesign_cached(
			'liturgy',
			$atts,
			static fn() => piodesign_render(
				'liturgy',
				[
					'days'    => piodesign_wp_liturgy_days( max( 1, min( 14, (int) $atts['dni'] ) ) ),
					'title'   => $atts['title'],
					'kicker'  => $atts['kicker'],
					'podcast' => piodesign_podcast( $atts['podcast'] ),
				]
			)
		);
	}
);

/* ---------------------------------------------------------------------------
 * Gospel reflection podcast (Spotify) for [pio_liturgia]
 * ------------------------------------------------------------------------ */

/**
 * Spotify show/episode → embed URL and today's episode title (oEmbed, cached).
 *
 * @return array|null [ embed, title, label, url ]
 */
function piodesign_podcast( $url ) {
	$url = trim( (string) $url );
	if ( ! preg_match( '#open\.spotify\.com/(?:embed(?:-podcast)?/)?(show|episode)/([A-Za-z0-9]+)#', $url, $m ) ) {
		return null;
	}
	$page  = 'https://open.spotify.com/' . $m[1] . '/' . $m[2];
	$key   = 'piodesign_pod_' . md5( $page ) . '_' . wp_date( 'Ymd' );
	$title = get_transient( $key );
	if ( false === $title ) {
		$title = '';
		$res   = wp_remote_get( 'https://open.spotify.com/oembed?url=' . rawurlencode( $page ), [ 'timeout' => 4 ] );
		if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
			$data  = json_decode( wp_remote_retrieve_body( $res ), true );
			$title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		}
		set_transient( $key, $title, HOUR_IN_SECONDS );
	}
	return [
		'embed' => 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2] . '?utm_source=generator&theme=0',
		'title' => $title,
		'label' => (string) piodesign_option( 'lit_podcast_title' ),
		'url'   => $page,
	];
}

/* ---------------------------------------------------------------------------
 * Mass times from the parish's Mass intentions plugin
 * ------------------------------------------------------------------------ */

/**
 * Upcoming Mass times for the live "next Mass" counter, as "Y-m-dTH:i"
 * strings, from today for about two weeks.
 *
 * Order of sources:
 *  1. filter `piodesign_mass_slots` (any plugin can hand over the list),
 *  2. the intentions page (Settings → PioDesign → Informacje): the
 *     intentions plugin's shortcode is rendered for this week and the next
 *     and the times are read from its markup (.ki-dzien-item / .ki-godzina),
 *  3. empty – the counter then uses the weekly schedule from the settings.
 */
function piodesign_mass_slots( DateTimeImmutable $now ) {
	$slots = apply_filters( 'piodesign_mass_slots', null, $now );
	if ( is_array( $slots ) ) {
		return array_values( $slots );
	}
	if ( 'auto' !== piodesign_option( 'mass_source' ) ) {
		return [];
	}
	$page = piodesign_page_option( 'intencje_page', 'intencje-mszalne' );
	if ( ! $page ) {
		return [];
	}
	$key    = 'piodesign_slots_' . $page . '_' . (int) get_option( 'piodesign_cache_v', 1 ) . '_' . $now->format( 'YmdH' );
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$content = (string) get_post_field( 'post_content', $page );
	$code    = piodesign_intentions_shortcode( $content );
	if ( '' === $code ) {
		$code = $content; // Unknown tag name: render the whole page (guarded against recursion below).
	}

	// The intentions plugin shows the week chosen by ?tydzien=Y-m-d (weeks start on Sunday).
	$today    = $now->setTime( 0, 0 );
	$n        = (int) $today->format( 'N' );
	$sunday   = 7 === $n ? $today : $today->modify( '-' . $n . ' days' );
	$weeks    = [ $sunday, $sunday->modify( '+7 days' ) ];
	$saved    = $_GET['tydzien'] ?? null; // phpcs:ignore WordPress.Security.NonceVerification
	$saved_r  = $_REQUEST['tydzien'] ?? null; // phpcs:ignore WordPress.Security.NonceVerification
	$times    = [];
	static $running = false;
	if ( $running ) {
		return [];
	}
	$running = true;
	foreach ( $weeks as $w ) {
		$_GET['tydzien']     = $w->format( 'Y-m-d' );
		$_REQUEST['tydzien'] = $_GET['tydzien'];
		$times               = array_merge( $times, piodesign_parse_intentions( do_shortcode( $code ) ) );
	}
	$running = false;
	if ( null === $saved ) {
		unset( $_GET['tydzien'] );
	} else {
		$_GET['tydzien'] = $saved;
	}
	if ( null === $saved_r ) {
		unset( $_REQUEST['tydzien'] );
	} else {
		$_REQUEST['tydzien'] = $saved_r;
	}

	$times = array_values( array_unique( array_filter( $times, static fn( $t ) => $t >= $today->format( 'Y-m-d' ) ) ) );
	sort( $times );
	set_transient( $key, $times, HOUR_IN_SECONDS );
	return $times;
}

/** The intentions plugin's shortcode on the page (a tag containing "intenc"). */
function piodesign_intentions_shortcode( $content ) {
	global $shortcode_tags;
	foreach ( array_keys( (array) $shortcode_tags ) as $tag ) {
		if ( false === stripos( $tag, 'intenc' ) ) {
			continue;
		}
		if ( preg_match( '/\[' . preg_quote( $tag, '/' ) . '\b[^\]]*\]/', $content, $m ) ) {
			return $m[0];
		}
	}
	return '';
}
