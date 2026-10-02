<?php
/**
 * Homepage calendar: timeline strip, "next up" card, ongoing events and the
 * agenda grouped by week.
 *
 * @var array  $events       Normalised events.
 * @var array  $timeline     pio_gazeta_timeline() over a wider set of events.
 * @var array  $grouped      pio_gazeta_group_events().
 * @var int    $mobile       Events shown on phones (incl. "next up") before the calendar link.
 * @var string $calendar_url
 * @var string $title
 * @var string $kicker
 */

if ( ! $events ) {
	return;
}
$uid = 'pio-events-' . substr( md5( serialize( array_column( $events, 'id' ) ) ), 0, 6 );
$i   = 0;
?>
<section class="pio pio-events" id="<?php echo esc_attr( $uid ); ?>" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h">
	<header class="pio-events__head">
		<h2 class="pio-events__title" id="<?php echo esc_attr( $uid ); ?>-h">
			<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
			<span class="pio-events__word"><?php echo esc_html( $title ); ?></span>
		</h2>
		<a class="pio-btn pio-btn--light" href="<?php echo esc_url( $calendar_url ); ?>"><?php echo pio_gazeta_icon( 'calendar' ); // phpcs:ignore ?> Cały kalendarz</a>
	</header>

	<?php echo pio_gazeta_render( 'partials/timeline', [ 'tl' => $timeline ] ); // phpcs:ignore ?>

	<div class="pio-events__layout">
		<div class="pio-events__lead">
			<?php if ( $grouped['featured'] ) : ?>
				<?php echo pio_gazeta_render( 'partials/event-feature', [ 'e' => $grouped['featured'] ] ); // phpcs:ignore ?>
			<?php endif; ?>

			<?php if ( $grouped['ongoing'] ) : ?>
				<div class="pio-now">
					<h3 class="pio-group-h"><i class="pio-pulse" aria-hidden="true"></i>Trwa teraz</h3>
					<?php foreach ( $grouped['ongoing'] as $e ) : ?>
						<a class="pio-now__item" id="pio-ev-<?php echo (int) $e['id']; ?>" href="<?php echo esc_url( $e['url'] ); ?>">
							<span class="pio-now__title"><?php echo esc_html( $e['title'] ); ?></span>
							<span class="pio-now__when"><?php echo esc_html( $e['when'] ); ?></span>
							<span class="pio-progress" style="--p: <?php echo esc_attr( round( $e['progress'], 3 ) ); ?>;" role="img" aria-label="<?php echo esc_attr( $e['day_of'] ); ?>"><i></i><span><?php echo esc_html( $e['day_of'] ); ?></span></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		$limit = max( 0, $mobile - ( $grouped['featured'] ? 1 : 0 ) );
		foreach ( $grouped['groups'] as $label => $items ) :
			?>
			<div class="pio-agenda__group<?php echo $i >= $limit ? ' pio-mhide' : ''; ?>">
				<h3 class="pio-group-h"><?php echo esc_html( $label ); ?> <b><?php echo count( $items ); ?></b></h3>
				<?php
				foreach ( $items as $e ) {
					echo pio_gazeta_render( 'partials/event-row', [ 'e' => $e, 'context' => 'home', 'i' => $i, 'mhide' => $i >= $limit ] ); // phpcs:ignore
					$i++;
				}
				?>
			</div>
		<?php endforeach; ?>

		<a class="pio-events__more" href="<?php echo esc_url( $calendar_url ); ?>">
			<span class="pio-events__more-t">Pełny kalendarz parafii</span>
			<span class="pio-events__more-s">Wszystkie wydarzenia, wyszukiwarka, widok miesiąca</span>
			<?php echo pio_gazeta_icon( 'arrow' ); // phpcs:ignore ?>
		</a>
	</div>
</section>
