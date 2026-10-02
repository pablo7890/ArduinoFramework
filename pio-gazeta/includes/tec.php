<?php
/**
 * The Events Calendar integration:
 *  - list view (v2): our partials for list/event and list/month-separator,
 *    plus a hero with the timeline above the events bar;
 *  - single event: our template instead of TEC's single-event.php.
 *
 * Everything can be switched off with the `pio_gazeta_tec_list` and
 * `pio_gazeta_tec_single` filters.
 *
 * @package PioGazeta
 */

defined( 'ABSPATH' ) || exit;

/* Let TEC find template overrides inside this plugin (before the theme). */
add_filter(
	'tribe_template_path_list',
	static function ( $folders, $template ) {
		if ( ! apply_filters( 'pio_gazeta_tec_list', true ) ) {
			return $folders;
		}
		$folders['pio-gazeta'] = [
			'id'        => 'pio-gazeta',
			'namespace' => 'the-events-calendar',
			'priority'  => 5,
			'path'      => PIO_GAZETA_DIR . 'tribe/events',
		];
		return $folders;
	},
	10,
	2
);

/* Hero + timeline above the list view's search bar. */
add_action(
	'tribe_template_after_include:events/v2/components/before',
	static function ( $file, $name, $template ) {
		if ( ! apply_filters( 'pio_gazeta_tec_list', true ) || ! is_object( $template ) || ! method_exists( $template, 'get_view' ) ) {
			return;
		}
		$view = $template->get_view();
		if ( ! $view || 'list' !== $view->get_slug() ) {
			return;
		}
		$now = pio_gazeta_now();
		echo pio_gazeta_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'archive-hero',
			[
				'day'      => pio_gazeta_wp_day( $now ),
				'timeline' => pio_gazeta_get_timeline(),
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
		if ( ! apply_filters( 'pio_gazeta_tec_single', true ) ) {
			return $file;
		}
		if ( 'single-event' === basename( (string) $template, '.php' ) ) {
			return PIO_GAZETA_DIR . 'tec/single-event.php';
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
		}
		return $classes;
	}
);

/**
 * Full HTML for a single event page.
 */
function pio_gazeta_single_event_html( $post_id ) {
	$now   = pio_gazeta_now();
	$e     = pio_gazeta_wp_event( $post_id, $now );
	$start = wp_date( 'Y-m-d H:i:s', $e['start_ts'] );

	$others = static function ( array $posts ) use ( $post_id ) {
		return array_values( array_filter( $posts, static fn( $p ) => (int) ( is_object( $p ) ? $p->ID : $p ) !== (int) $post_id ) );
	};

	$prev    = $others( pio_gazeta_query_events( 2, [ 'starts_before' => $start ], 'DESC' ) );
	$next    = $others( pio_gazeta_query_events( 2, [ 'starts_after' => $start ] ) );
	$related = array_map(
		static fn( $p ) => pio_gazeta_wp_event( $p, $now ),
		array_slice( $others( pio_gazeta_query_events( 4, [ 'ends_after' => 'now' ] ) ), 0, 3 )
	);

	return pio_gazeta_render(
		'single-event',
		[
			'e'            => $e,
			'content'      => apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ),
			'related'      => $related,
			'prev'         => $prev ? pio_gazeta_wp_event( $prev[0], $now ) : null,
			'next'         => $next ? pio_gazeta_wp_event( $next[0], $now ) : null,
			'calendar_url' => function_exists( 'tribe_get_events_link' ) ? tribe_get_events_link() : home_url( '/' ),
		]
	);
}
