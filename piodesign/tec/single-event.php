<?php
/**
 * Replaces TEC's single-event.php (loaded inside TEC's default template, so
 * the theme header, footer and TEC notices stay in place).
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

wp_enqueue_style( 'piodesign' );
wp_enqueue_script( 'piodesign' );

// TEC's notices ("This event has passed") are not printed: the ticket shows the status.

while ( have_posts() ) {
	the_post();
	echo piodesign_single_event_html( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
	do_action( 'tribe_events_single_event_after_the_meta' );
}
