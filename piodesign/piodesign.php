<?php
/**
 * Plugin Name:       Parafia: PioDesign
 * Description:       Nowoczesny wygląd parafii na Avadzie: aktualności i ich archiwum, oś wydarzeń The Events Calendar, sakramenty, cytaty, liturgia dnia i informacje parafialne. Ustawienia → PioDesign.
 * Version:           1.1
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            cruzLabs
 * Text Domain:       piodesign
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

define( 'PIODESIGN_VERSION', '1.1' );
define( 'PIODESIGN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PIODESIGN_URL', plugin_dir_url( __FILE__ ) );

require_once PIODESIGN_DIR . 'includes/core.php';
require_once PIODESIGN_DIR . 'includes/sections-core.php';
require_once PIODESIGN_DIR . 'includes/wp-data.php';
require_once PIODESIGN_DIR . 'includes/tec.php';
require_once PIODESIGN_DIR . 'includes/settings.php';
require_once PIODESIGN_DIR . 'includes/archive.php';
require_once PIODESIGN_DIR . 'includes/sections.php';

/* ---------------------------------------------------------------------------
 * Assets
 * ------------------------------------------------------------------------ */

add_action(
	'wp_enqueue_scripts',
	static function () {
		// Typefaces come from Avada's Global Options; the plugin loads none.
		$deps = [];
		// File times in the version string bust browser and CDN caches on every update.
		$ver = static fn( $f ) => PIODESIGN_VERSION . '.' . (int) @filemtime( PIODESIGN_DIR . $f );
		wp_register_style( 'piodesign', PIODESIGN_URL . 'assets/piodesign.css', $deps, $ver( 'assets/piodesign.css' ) );
		wp_register_script( 'piodesign', PIODESIGN_URL . 'assets/piodesign.js', [], $ver( 'assets/piodesign.js' ), [ 'in_footer' => true, 'strategy' => 'defer' ] );

		if ( piodesign_should_enqueue() ) {
			wp_enqueue_style( 'piodesign' );
			wp_enqueue_script( 'piodesign' );
		}
	}
);

function piodesign_should_enqueue() {
	if ( is_front_page() || piodesign_is_news_archive() || ( function_exists( 'tribe_is_event_query' ) && tribe_is_event_query() ) ) {
		return true;
	}
	$post = get_post();
	if ( ! $post ) {
		return false;
	}
	foreach ( [ 'pio_aktualnosci', 'pio_wydarzenia', 'pio_sakramenty', 'pio_cytaty', 'pio_informacje', 'pio_liturgia', 'pio_archiwum' ] as $tag ) {
		if ( has_shortcode( $post->post_content, $tag ) ) {
			return true;
		}
	}
	return false;
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
	if ( in_array( get_post_type( $post_id ), [ 'post', 'page', 'tribe_events', 'tribe_venue', 'tribe_organizer', 'attachment', 'foogallery' ], true ) ) {
		update_option( 'piodesign_cache_v', (int) get_option( 'piodesign_cache_v', 1 ) + 1, false );
	}
};
add_action( 'save_post', $piodesign_bump );
add_action( 'deleted_post', $piodesign_bump );
add_action( 'update_option_piodesign_settings', static fn() => update_option( 'piodesign_cache_v', (int) get_option( 'piodesign_cache_v', 1 ) + 1, false ) );

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
				$archive_url = $atts['archive_url']
					?: ( $atts['category'] ? get_category_link( get_category_by_slug( $atts['category'] ) ) : piodesign_archive_url() );

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
