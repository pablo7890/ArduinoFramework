<?php
/**
 * Plugin Name:       Parafia: PioDesign
 * Description:       Nowa strona główna parafii: redakcyjny układ aktualności, oś wydarzeń The Events Calendar, widok pojedynczego wydarzenia i listy wydarzeń. Shortcode'y: [pio_aktualnosci], [pio_wydarzenia].
 * Version:           1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            cruzLabs
 * Text Domain:       piodesign
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

define( 'PIODESIGN_VERSION', '1.0' );
define( 'PIODESIGN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PIODESIGN_URL', plugin_dir_url( __FILE__ ) );

require_once PIODESIGN_DIR . 'includes/core.php';
require_once PIODESIGN_DIR . 'includes/wp-data.php';
require_once PIODESIGN_DIR . 'includes/tec.php';
require_once PIODESIGN_DIR . 'includes/settings.php';
require_once PIODESIGN_DIR . 'includes/archive.php';

/* ---------------------------------------------------------------------------
 * Assets
 * ------------------------------------------------------------------------ */

add_action(
	'wp_enqueue_scripts',
	static function () {
		$deps = [];
		// Fonts ship with the plugin (no requests to Google). Turn off when
		// the theme already provides Bricolage Grotesque and Newsreader.
		if ( apply_filters( 'piodesign_load_fonts', true ) ) {
			wp_register_style( 'piodesign-fonts', PIODESIGN_URL . 'assets/piodesign-fonts.css', [], PIODESIGN_VERSION );
			$deps[] = 'piodesign-fonts';
		}
		wp_register_style( 'piodesign', PIODESIGN_URL . 'assets/piodesign.css', $deps, PIODESIGN_VERSION );
		wp_register_script( 'piodesign', PIODESIGN_URL . 'assets/piodesign.js', [], PIODESIGN_VERSION, [ 'in_footer' => true, 'strategy' => 'defer' ] );

		if ( piodesign_should_enqueue() ) {
			wp_enqueue_style( 'piodesign' );
			wp_enqueue_script( 'piodesign' );
		}
	}
);

add_action(
	'wp_head',
	static function () {
		if ( ! apply_filters( 'piodesign_load_fonts', true ) || ! ( piodesign_should_enqueue() || piodesign_option( 'site_fonts' ) ) ) {
			return;
		}
		foreach ( [ 'bricolage-grotesque-latin', 'newsreader-latin' ] as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( PIODESIGN_URL . 'assets/fonts/' . $font . '.woff2' ) );
		}
	},
	2
);

function piodesign_should_enqueue() {
	if ( is_front_page() || piodesign_is_news_archive() || ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) ) {
		return true;
	}
	$post = get_post();
	return $post && ( has_shortcode( $post->post_content, 'pio_aktualnosci' ) || has_shortcode( $post->post_content, 'pio_wydarzenia' ) );
}

function piodesign_enqueue_late() {
	wp_enqueue_style( 'piodesign' );
	wp_enqueue_script( 'piodesign' );
}

/* ---------------------------------------------------------------------------
 * Fragment cache (rendered HTML, invalidated on any post/event save)
 * ------------------------------------------------------------------------ */

function piodesign_cached( $name, array $atts, callable $build ) {
	$ttl = (int) apply_filters( 'piodesign_cache_ttl', 10 * MINUTE_IN_SECONDS );
	if ( $ttl <= 0 || is_user_logged_in() ) {
		return $build();
	}
	// Relative labels ("jutro", "za 3 dni") change daily; countdowns tick in JS.
	$key  = 'piodesign_' . md5( $name . wp_json_encode( $atts ) . (int) get_option( 'piodesign_cache_v', 1 ) . wp_date( 'Y-m-d' ) );
	$html = get_transient( $key );
	if ( false === $html ) {
		$html = $build();
		set_transient( $key, $html, $ttl );
	}
	return $html;
}

$piodesign_bump = static function ( $post_id ) {
	if ( in_array( get_post_type( $post_id ), [ 'post', 'tribe_events', 'tribe_venue', 'tribe_organizer', 'attachment', 'foogallery' ], true ) ) {
		update_option( 'piodesign_cache_v', (int) get_option( 'piodesign_cache_v', 1 ) + 1, false );
	}
};
add_action( 'save_post', $piodesign_bump );
add_action( 'deleted_post', $piodesign_bump );

/* ---------------------------------------------------------------------------
 * Shortcodes
 * ------------------------------------------------------------------------ */

add_shortcode(
	'pio_aktualnosci',
	static function ( $atts ) {
		$atts = shortcode_atts(
			[
				'count'       => 15,
				'mobile'      => 5,
				'category'    => '',
				'title'       => 'Aktualności',
				'kicker'      => 'Z życia parafii',
				'archive_url' => '',
			],
			$atts,
			'pio_aktualnosci'
		);
		piodesign_enqueue_late();

		return piodesign_cached(
			'news',
			$atts,
			static function () use ( $atts ) {
				$now         = piodesign_now();
				$posts_page  = (int) get_option( 'page_for_posts' );
				$archive_url = $atts['archive_url']
					?: ( $atts['category'] ? get_category_link( get_category_by_slug( $atts['category'] ) ) : ( $posts_page ? get_permalink( $posts_page ) : home_url( '/aktualnosci/' ) ) );

				return piodesign_render(
					'news',
					[
						'posts'       => piodesign_get_posts( $atts ),
						'day'         => piodesign_wp_day( $now ),
						'mobile'      => max( 1, (int) $atts['mobile'] ),
						'archive_url' => $archive_url,
						'title'       => $atts['title'],
						'kicker'      => $atts['kicker'],
					]
				);
			}
		);
	}
);

add_shortcode(
	'pio_wydarzenia',
	static function ( $atts ) {
		$atts = shortcode_atts(
			[
				'count'        => 10,
				'mobile'       => 5,
				'category'     => '',
				'title'        => 'Wydarzenia',
				'kicker'       => 'Nadchodzące',
				'calendar_url' => '',
				'weeks'        => 5,
			],
			$atts,
			'pio_wydarzenia'
		);
		if ( ! function_exists( 'tribe_events' ) ) {
			return current_user_can( 'activate_plugins' ) ? '<p>[pio_wydarzenia] wymaga wtyczki The Events Calendar.</p>' : '';
		}
		piodesign_enqueue_late();

		return piodesign_cached(
			'events',
			$atts,
			static function () use ( $atts ) {
				$now    = piodesign_now();
				$events = piodesign_get_events( (int) $atts['count'], $atts['category'] );
				return piodesign_render(
					'events',
					[
						'events'       => $events,
						'grouped'      => piodesign_group_events( $events, $now ),
						'mobile'       => max( 1, (int) $atts['mobile'] ),
						'timeline'     => piodesign_get_timeline( max( 2, min( 8, (int) $atts['weeks'] ) ) ),
						'calendar_url' => $atts['calendar_url'] ?: tribe_get_events_link(),
						'title'        => $atts['title'],
						'kicker'       => $atts['kicker'],
					]
				);
			}
		);
	}
);
