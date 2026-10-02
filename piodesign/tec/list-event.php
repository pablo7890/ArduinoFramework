<?php
/**
 * Override: View: List Event (TEC v2).
 *
 * @var WP_Post $event The event post object with TEC properties added.
 *
 * @package PioDesign
 */

$GLOBALS['piodesign_list_i'] = isset( $GLOBALS['piodesign_list_i'] ) ? $GLOBALS['piodesign_list_i'] + 1 : 0;

echo piodesign_render( // phpcs:ignore WordPress.Security.EscapeOutput
	'partials/event-row',
	[
		'e'       => piodesign_wp_event( $event ),
		'context' => 'archive',
		'i'       => $GLOBALS['piodesign_list_i'],
	]
);
