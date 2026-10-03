<?php
/**
 * WordPress data layer: turns WP_Posts and TEC events into the raw arrays that
 * core.php normalises.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

function piodesign_now() {
	return new DateTimeImmutable( 'now', wp_timezone() );
}

/** Today for the dateline, from "Parafia: Kalendarz liturgiczny" when active. */
function piodesign_wp_day( $now = null ) {
	$now = $now ?: piodesign_now();
	$ext = apply_filters( 'kalendarz_liturgiczny_day', null, $now->format( 'Y-m-d' ) );
	return piodesign_day( $now, $ext );
}

/** Image data for an attachment, with the full-size ratio for the frame. */
function piodesign_wp_image( $attachment_id, $size = 'large' ) {
	$attachment_id = (int) $attachment_id;
	if ( ! $attachment_id ) {
		return [];
	}
	$src = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! $src ) {
		return [];
	}
	$meta = wp_get_attachment_metadata( $attachment_id );
	return [
		'src'    => $src[0],
		'srcset' => (string) wp_get_attachment_image_srcset( $attachment_id, $size ),
		'w'      => (int) ( $meta['width'] ?? $src[1] ),
		'h'      => (int) ( $meta['height'] ?? $src[2] ),
		'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	];
}

/**
 * All image attachment IDs shown in a post: FooGallery galleries, core
 * galleries and inline images. Cached per post revision.
 */
function piodesign_wp_gallery_ids( WP_Post $post ) {
	$key    = 'piodesign_gal_' . $post->ID;
	$cached = get_transient( $key );
	if ( is_array( $cached ) && ( $cached['m'] ?? '' ) === $post->post_modified_gmt ) {
		return $cached['ids'];
	}

	$content = (string) $post->post_content;
	$ids     = [];

	// FooGallery: [foogallery id="123"] and the fooplugins/foogallery block.
	if ( preg_match_all( '/\[foogallery[^\]]*\bid=["\']?(\d+)/', $content, $m ) ) {
		$gallery_ids = $m[1];
	} else {
		$gallery_ids = [];
	}
	if ( preg_match_all( '/<!--\s*wp:fooplugins\/foogallery\s+\{[^}]*"id":\s*(\d+)/', $content, $m ) ) {
		$gallery_ids = array_merge( $gallery_ids, $m[1] );
	}
	foreach ( array_unique( $gallery_ids ) as $gid ) {
		$att = get_post_meta( (int) $gid, 'foogallery_attachments', true );
		if ( is_array( $att ) ) {
			$ids = array_merge( $ids, $att );
		}
	}

	// Core [gallery ids="1,2,3"].
	if ( preg_match_all( '/\[gallery[^\]]*\bids=["\']([\d,\s]+)["\']/', $content, $m ) ) {
		foreach ( $m[1] as $list ) {
			$ids = array_merge( $ids, array_map( 'intval', explode( ',', $list ) ) );
		}
	}
	// Inline images and gallery blocks.
	if ( preg_match_all( '/wp-image-(\d+)/', $content, $m ) ) {
		$ids = array_merge( $ids, $m[1] );
	}
	// Avada gallery element: [fusion_gallery_image image_id="123|full"].
	if ( preg_match_all( '/image_id=["\'](\d+)/', $content, $m ) ) {
		$ids = array_merge( $ids, $m[1] );
	}

	$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
	$ids = apply_filters( 'piodesign_post_gallery_ids', $ids, $post );
	set_transient( $key, [ 'm' => $post->post_modified_gmt, 'ids' => $ids ], WEEK_IN_SECONDS );
	return $ids;
}

/** Category shown on the card: Yoast primary, else the most specific one. */
function piodesign_wp_category( $post_id ) {
	$terms = get_the_category( $post_id );
	if ( ! $terms ) {
		return null;
	}
	$default = (int) get_option( 'default_category' );
	$generic = apply_filters( 'piodesign_generic_categories', [ 'aktualnosci', 'bez-kategorii', 'uncategorized' ] );

	$primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
	foreach ( $terms as $t ) {
		if ( $primary && $t->term_id === $primary ) {
			return [ 'name' => $t->name, 'slug' => $t->slug ];
		}
	}
	$specific = array_filter( $terms, static fn( $t ) => $t->term_id !== $default && ! in_array( $t->slug, $generic, true ) );
	$pick     = $specific ? reset( $specific ) : reset( $terms );
	if ( $pick->term_id === $default ) {
		return [ 'name' => 'Aktualności', 'slug' => 'aktualnosci' ];
	}
	return [ 'name' => $pick->name, 'slug' => $pick->slug ];
}

/**
 * @return array[] Normalised posts.
 */
function piodesign_get_posts( array $args ) {
	$query = new WP_Query(
		apply_filters(
			'piodesign_posts_query',
			[
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => (int) $args['count'],
				'category_name'       => $args['category'],
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			],
			$args
		)
	);

	return piodesign_wp_posts( $query->posts );
}

/**
 * Normalise WP_Posts for the templates (card data, gallery preview, category).
 *
 * @param WP_Post[] $wp_posts
 * @return array[]
 */
function piodesign_wp_posts( array $wp_posts ) {
	$now   = piodesign_now();
	$posts = [];
	foreach ( $wp_posts as $post ) {
		$post    = get_post( $post );
		$thumb   = get_post_thumbnail_id( $post );
		$gallery = piodesign_wp_gallery_ids( $post );
		$preview = array_slice( array_values( array_diff( $gallery, [ $thumb ] ) ), 0, 3 );

		$excerpt = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$excerpt = html_entity_decode( wp_trim_words( $excerpt, 110, '' ), ENT_QUOTES, 'UTF-8' );

		$posts[] = piodesign_post(
			[
				'id'          => $post->ID,
				'title'       => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
				'url'         => get_permalink( $post ),
				'date'        => new DateTimeImmutable( $post->post_date, wp_timezone() ),
				'excerpt'     => $excerpt,
				'category'    => piodesign_wp_category( $post->ID ),
				'image'       => piodesign_wp_image( $thumb ?: ( $gallery[0] ?? 0 ) ),
				'gallery'     => array_filter( array_map( static fn( $id ) => wp_get_attachment_image_url( $id, 'medium_large' ), $preview ) ),
				'photo_count' => count( $gallery ),
				'words'       => str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ),
			],
			$now
		);
	}
	return $posts;
}

/** Raw event array from a TEC event post. */
function piodesign_wp_event_raw( $event ) {
	$post = get_post( $event );
	$id   = $post->ID;
	$tz   = wp_timezone();
	if ( class_exists( 'Tribe__Events__Timezones' ) ) {
		$tz = Tribe__Events__Timezones::build_timezone_object( Tribe__Events__Timezones::get_event_timezone_string( $id ) );
	}

	$venue_id = function_exists( 'tribe_get_venue_id' ) ? tribe_get_venue_id( $id ) : 0;
	$venue    = $venue_id ? [
		'name'    => html_entity_decode( tribe_get_venue( $id ), ENT_QUOTES, 'UTF-8' ),
		'address' => (string) tribe_get_address( $id ),
		'city'    => (string) tribe_get_city( $id ),
	] : [];

	$org_id    = function_exists( 'tribe_get_organizer_id' ) ? tribe_get_organizer_id( $id ) : 0;
	$organizer = $org_id ? [
		'name' => html_entity_decode( tribe_get_organizer( $org_id ), ENT_QUOTES, 'UTF-8' ),
		'url'  => (string) tribe_get_organizer_website_url( $org_id ),
	] : [];

	$cats     = get_the_terms( $id, 'tribe_events_cat' );
	$category = ( $cats && ! is_wp_error( $cats ) ) ? [ 'name' => $cats[0]->name, 'slug' => $cats[0]->slug ] : null;

	$excerpt = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );

	return [
		'id'        => $id,
		'title'     => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
		'url'       => get_permalink( $post ),
		'start'     => new DateTimeImmutable( get_post_meta( $id, '_EventStartDate', true ), $tz ),
		'end'       => new DateTimeImmutable( get_post_meta( $id, '_EventEndDate', true ), $tz ),
		'all_day'   => function_exists( 'tribe_event_is_all_day' ) && tribe_event_is_all_day( $id ),
		'excerpt'     => html_entity_decode( wp_trim_words( $excerpt, 80, '' ), ENT_QUOTES, 'UTF-8' ),
		'excerpt_len' => (int) piodesign_option( 'events_excerpt' ),
		'image'     => piodesign_wp_image( get_post_thumbnail_id( $post ) ),
		'venue'     => $venue,
		'organizer' => $organizer,
		'category'  => $category,
		'featured'  => (bool) get_post_meta( $id, '_tribe_featured', true ),
		'cost'      => function_exists( 'tribe_get_cost' ) ? (string) tribe_get_cost( $id, true ) : '',
		'ics_url'   => add_query_arg( 'ical', 1, get_permalink( $post ) ),
	];
}

function piodesign_wp_event( $event, $now = null ) {
	return piodesign_event( piodesign_wp_event_raw( $event ), $now ?: piodesign_now() );
}

/**
 * Upcoming and ongoing events, soonest first.
 *
 * @param array $where Extra ORM where clauses, e.g. [ 'starts_after' => '...' ].
 */
function piodesign_query_events( $count, array $where = [], $order = 'ASC' ) {
	if ( ! function_exists( 'tribe_events' ) ) {
		return [];
	}
	$repo = tribe_events()->per_page( (int) $count )->order_by( 'event_date', $order );
	foreach ( $where + [ 'status' => 'publish' ] as $key => $value ) {
		$repo = $repo->where( $key, $value );
	}
	return (array) $repo->all();
}

function piodesign_get_events( $count, $category = '' ) {
	$where = [ 'ends_after' => 'now' ];
	if ( $category ) {
		$where['category'] = $category;
	}
	$now = piodesign_now();
	return array_map( static fn( $p ) => piodesign_wp_event( $p, $now ), piodesign_query_events( $count, $where ) );
}

/** Events touching the timeline window (this Monday + 5 weeks). */
function piodesign_get_timeline( $weeks = 5 ) {
	$now   = piodesign_now();
	$today = $now->setTime( 0, 0 );
	$start = $today->modify( '-' . ( (int) $today->format( 'N' ) - 1 ) . ' days' );
	$end   = $start->modify( '+' . ( $weeks * 7 ) . ' days' );
	$posts = piodesign_query_events(
		80,
		[
			'ends_after'    => $start->format( 'Y-m-d H:i:s' ),
			'starts_before' => $end->format( 'Y-m-d H:i:s' ),
		]
	);
	$events = array_map( static fn( $p ) => piodesign_wp_event( $p, $now ), $posts );
	return piodesign_timeline( $events, $now, $weeks );
}
