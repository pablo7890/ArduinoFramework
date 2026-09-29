<?php
/**
 * Replaces TEC's single-event.php (loaded inside TEC's default template, so
 * the theme header, footer and TEC notices stay in place).
 *
 * @package PioGazeta
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_style( 'pio-gazeta' );
wp_enqueue_script( 'pio-gazeta' );

if ( function_exists( 'tribe_the_notices' ) ) {
	tribe_the_notices();
}

while ( have_posts() ) {
	the_post();
	echo pio_gazeta_single_event_html( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
	do_action( 'tribe_events_single_event_after_the_meta' );
}
