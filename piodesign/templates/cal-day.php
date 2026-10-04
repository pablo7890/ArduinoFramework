<?php
/**
 * Calendar: one day.
 *
 * @var array             $s
 * @var DateTimeImmutable $day
 * @var array[]           $events
 * @var array[]           $next      Upcoming events when the day is empty.
 * @var array|null        $lit       piodesign_liturgy_day() or null.
 * @var string            $prev_url
 * @var string            $next_url
 * @var string            $month_url
 * @var bool              $is_today
 */

$m = piodesign_months( 'nom' )[ (int) $day->format( 'n' ) ];
?>
<section class="pio-cal-day" aria-labelledby="pio-cal-day-h">
	<header class="pio-cal__head">
		<h2 class="pio-cal__title" id="pio-cal-day-h">
			<span class="pio-cal__wd"><?php echo esc_html( ( $is_today ? 'Dziś, ' : '' ) . piodesign_weekdays()[ (int) $day->format( 'N' ) ] ); ?></span>
			<span class="pio-cal__name"><?php echo esc_html( $day->format( 'j' ) . ' ' . piodesign_months()[ (int) $day->format( 'n' ) ] ); ?></span>
			<span class="pio-month__year"><?php echo esc_html( $day->format( 'Y' ) ); ?></span>
		</h2>
		<nav class="pio-cal__nav" aria-label="Sąsiednie dni">
			<a class="pio-cal__step" href="<?php echo esc_url( $prev_url ); ?>" rel="nofollow"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?><span>Poprzedni dzień</span></a>
			<a class="pio-cal__step" href="<?php echo esc_url( $month_url ); ?>"><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?><span><?php echo esc_html( $m ); ?></span></a>
			<a class="pio-cal__step" href="<?php echo esc_url( $next_url ); ?>" rel="nofollow"><span>Następny dzień</span><?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></a>
		</nav>
	</header>

	<?php if ( $lit ) : ?>
		<p class="pio-cal-lit" style="--c: <?php echo esc_attr( $lit['color'] ); ?>;">
			<i aria-hidden="true"></i>
			<span><?php echo esc_html( $lit['title'] ); ?><?php echo $lit['rank'] ? ' · ' . esc_html( $lit['rank'] ) : ''; ?></span>
		</p>
	<?php endif; ?>

	<?php if ( $events ) : ?>
		<div class="pio-cal-list__rows">
			<?php foreach ( $events as $k => $e ) : ?>
				<?php echo piodesign_render( 'partials/event-row', [ 'e' => $e, 'context' => 'archive', 'i' => $k ] ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="pio-cal-empty">
			<span class="pio-cal-empty__icon" aria-hidden="true"><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?></span>
			<p class="pio-cal-empty__title"><?php echo '' !== $s['keyword'] ? esc_html( 'Tego dnia nic nie pasuje do „' . $s['keyword'] . '”.' ) : 'Tego dnia nie ma wydarzeń w kalendarzu.'; ?></p>
			<?php if ( $next ) : ?><p class="pio-cal-empty__text">Najbliżej:</p><?php endif; ?>
		</div>
		<?php if ( $next ) : ?>
			<div class="pio-cal-list__rows">
				<?php foreach ( $next as $k => $e ) : ?>
					<?php echo piodesign_render( 'partials/event-row', [ 'e' => $e, 'context' => 'archive', 'i' => $k ] ); // phpcs:ignore ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</section>
