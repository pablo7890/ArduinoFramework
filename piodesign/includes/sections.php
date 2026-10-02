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
				'parent'   => 'sakramenty-i-sakramentalia',
				'ids'      => '',
				'exclude'  => '',
				'title'    => 'Sakramenty',
				'kicker'   => 'Droga wiary',
				'autoplay' => 7,
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
					'more_url' => ( $p = get_page_by_path( $atts['parent'] ) ) ? get_permalink( $p ) : '',
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
				'title'    => 'Słowa na dziś',
				'autor'    => 'św. Ojciec Pio',
				'zdjecie'  => '',
				'autoplay' => 9,
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
				'title'  => 'Zapraszamy',
				'kicker' => 'Parafia św. Ojca Pio',
			],
			$atts,
			'pio_informacje'
		);
		piodesign_enqueue_late();
		return piodesign_render( 'info', piodesign_info_data( piodesign_now(), piodesign_options() ) + $atts );
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
				'dni'    => 7,
				'title'  => 'Liturgia dnia',
				'kicker' => 'Kalendarz liturgiczny',
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
					'days'   => piodesign_wp_liturgy_days( max( 1, min( 14, (int) $atts['dni'] ) ) ),
					'title'  => $atts['title'],
					'kicker' => $atts['kicker'],
				]
			)
		);
	}
);
