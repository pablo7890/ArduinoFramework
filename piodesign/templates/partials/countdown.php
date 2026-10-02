<?php
/**
 * Live countdown to an event start. Server renders the value at page build
 * time, script keeps it ticking.
 *
 * @var array $e
 */

$left = max( 0, $e['start_ts'] - time() );
$d    = intdiv( $left, 86400 );
$h    = intdiv( $left % 86400, 3600 );
$m    = intdiv( $left % 3600, 60 );
?>
<div class="pio-count" data-countdown="<?php echo (int) $e['start_ts']; ?>" data-end="<?php echo (int) $e['end_ts']; ?>" aria-label="Do rozpoczęcia">
	<span class="pio-count__cell"><b data-u="d"><?php echo (int) $d; ?></b><small data-l="d"><?php echo esc_html( piodesign_plural( $d, 'dzień', 'dni', 'dni' ) ); ?></small></span>
	<span class="pio-count__cell"><b data-u="h"><?php echo esc_html( sprintf( '%02d', $h ) ); ?></b><small>godz.</small></span>
	<span class="pio-count__cell"><b data-u="m"><?php echo esc_html( sprintf( '%02d', $m ) ); ?></b><small>min</small></span>
	<span class="pio-count__cell"><b data-u="s">00</b><small>sek</small></span>
	<span class="pio-count__live" hidden>Trwa teraz</span>
</div>
