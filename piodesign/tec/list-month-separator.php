<?php
/**
 * Override: View: List View Month separator (TEC v2). Same logic as TEC's
 * own partial, our markup.
 *
 * @var WP_Post $event        The event post object with TEC properties added.
 * @var bool    $is_past      Whether the view shows past events.
 * @var string  $request_date The request date (Y-m-d).
 *
 * @package PioDesign
 */

use Tribe\Events\Views\V2\Utils;

if ( ! class_exists( Utils\Separators::class ) ) {
	return;
}

if ( empty( $is_past ) && ! empty( $request_date ) ) {
	$should_have_month_separator = Utils\Separators::should_have_month( $this->get( 'events' ), $event, $request_date );
} else {
	$should_have_month_separator = Utils\Separators::should_have_month( $this->get( 'events' ), $event );
}

if ( ! $should_have_month_separator ) {
	return;
}

$sep_date = empty( $is_past ) && ! empty( $request_date )
	? max( $event->dates->start_display, $request_date )
	: $event->dates->start_display;

echo piodesign_render( // phpcs:ignore WordPress.Security.EscapeOutput
	'partials/month-head',
	[
		'month' => piodesign_months( 'nom' )[ (int) $sep_date->format( 'n' ) ],
		'year'  => $sep_date->format( 'Y' ),
	]
);
