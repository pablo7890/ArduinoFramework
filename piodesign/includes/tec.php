<?php
/**
 * The Events Calendar integration:
 *  - list view (v2): our partials for list/event and list/month-separator,
 *    plus a hero with the timeline above the events bar;
 *  - single event: our template instead of TEC's single-event.php, full
 *    width (Avada's event sidebar / meta box is switched off).
 *
 * Everything can be switched off with the `piodesign_tec_list` and
 * `piodesign_tec_single` filters.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

/*
 * List view rows. TEC resolves every v2 template through `tribe_template_file`;
 * we swap only the event row and the month separator, so TEC's own container,
 * search bar, AJAX and pagination keep working.
 */
add_filter(
	'tribe_template_file',
	static function ( $file, $name, $template ) {
		if ( ! apply_filters( 'piodesign_tec_list', true ) || ! is_string( $file ) ) {
			return $file;
		}
		$map = [
			'/v2/list/event.php'             => 'tec/list-event.php',
			'/v2/list/month-separator.php'   => 'tec/list-month-separator.php',
		];
		$normalized = str_replace( '\\', '/', $file );
		foreach ( $map as $suffix => $ours ) {
			if ( substr( $normalized, -strlen( $suffix ) ) === $suffix ) {
				return PIODESIGN_DIR . $ours;
			}
		}
		return $file;
	},
	20,
	3
);

/* Hero + timeline above the list view's search bar. */
add_action(
	'tribe_template_after_include:events/v2/components/before',
	static function ( $file, $name, $template ) {
		if ( ! apply_filters( 'piodesign_tec_list', true ) || ! is_object( $template ) || ! method_exists( $template, 'get_view' ) ) {
			return;
		}
		$view = $template->get_view();
		if ( ! $view || 'list' !== $view->get_slug() ) {
			return;
		}
		$now = piodesign_now();
		echo piodesign_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'archive-hero',
			[
				'day'      => piodesign_wp_day( $now ),
				'timeline' => piodesign_get_timeline(),
			]
		);
	},
	10,
	3
);

/* Single event template. */
add_filter(
	'tribe_events_template',
	static function ( $file, $template ) {
		if ( ! apply_filters( 'piodesign_tec_single', true ) ) {
			return $file;
		}
		if ( 'single-event' === basename( (string) $template, '.php' ) ) {
			return PIODESIGN_DIR . 'tec/single-event.php';
		}
		return $file;
	},
	20,
	2
);

add_filter(
	'body_class',
	static function ( $classes ) {
		if ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) {
			$classes[] = 'pio-tec';
			if ( is_singular( 'tribe_events' ) && apply_filters( 'piodesign_tec_single', true ) ) {
				$classes   = array_diff( $classes, [ 'has-sidebar', 'double-sidebars', 'avada-ec-meta-layout-sidebar' ] );
				$classes[] = 'pio-tec-single';
			}
		}
		return $classes;
	},
	99
);

/*
 * Avada prints TEC's event meta ("Szczegóły") in its own sidebar next to the
 * single event. Our ticket already shows all of it, so turn that off. The CSS
 * hides #sidebar as well, in case the option comes from a page-level setting.
 */
add_filter(
	'avada_setting_get_ec_meta_layout',
	static function ( $value ) {
		return ( apply_filters( 'piodesign_tec_single', true ) && is_singular( 'tribe_events' ) ) ? 'disabled' : $value;
	}
);

/**
 * Full HTML for a single event page.
 */
function piodesign_single_event_html( $post_id ) {
	$now   = piodesign_now();
	$e     = piodesign_wp_event( $post_id, $now );
	$start = wp_date( 'Y-m-d H:i:s', $e['start_ts'] );

	$others = static function ( array $posts ) use ( $post_id ) {
		return array_values( array_filter( $posts, static fn( $p ) => (int) ( is_object( $p ) ? $p->ID : $p ) !== (int) $post_id ) );
	};

	$prev    = $others( piodesign_query_events( 2, [ 'starts_before' => $start ], 'DESC' ) );
	$next    = $others( piodesign_query_events( 2, [ 'starts_after' => $start ] ) );
	$related = array_map(
		static fn( $p ) => piodesign_wp_event( $p, $now ),
		array_slice( $others( piodesign_query_events( 4, [ 'ends_after' => 'now' ] ) ), 0, 3 )
	);

	return piodesign_render(
		'single-event',
		[
			'e'            => $e,
			'content'      => apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ),
			'related'      => $related,
			'prev'         => $prev ? piodesign_wp_event( $prev[0], $now ) : null,
			'next'         => $next ? piodesign_wp_event( $next[0], $now ) : null,
			'calendar_url' => function_exists( 'tribe_get_events_link' ) ? tribe_get_events_link() : home_url( '/' ),
		]
	);
}
