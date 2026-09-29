<?php
/**
 * Override: View: List Event (TEC v2).
 *
 * @var WP_Post $event The event post object with TEC properties added.
 *
 * @package PioGazeta
 */

static $pio_gazeta_i = 0;

echo pio_gazeta_render( // phpcs:ignore WordPress.Security.EscapeOutput
	'partials/event-row',
	[
		'e'       => pio_gazeta_wp_event( $event ),
		'context' => 'archive',
		'i'       => $pio_gazeta_i++,
	]
);
