<?php
/**
 * Calendar list: upcoming (or past) events grouped by month.
 *
 * @var array   $s
 * @var array[] $groups    [ month, year, events[] ].
 * @var int     $total
 * @var array[] $pager     piodesign_pager().
 * @var string  $past_url
 * @var string  $list_url
 * @var string  $month_url
 * @var DateTimeImmutable|null $from Start date (?tribe-bar-date) or null.
 */

$i = 0;
?>
<section class="pio-cal-list" aria-label="<?php echo esc_attr( $s['past'] ? 'Minione wydarzenia' : 'Nadchodzące wydarzenia' ); ?>">
	<?php if ( $from && ! $s['past'] ) : ?>
		<p class="pio-cal-note">Wydarzenia od <?php echo esc_html( piodesign_date( $from, true, true ) ); ?> · <a href="<?php echo esc_url( piodesign_cal_url( 'list', $s, [] ) ); ?>">od dziś</a></p>
	<?php endif; ?>

	<?php if ( ! $groups ) : ?>
		<div class="pio-cal-empty">
			<span class="pio-cal-empty__icon" aria-hidden="true"><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?></span>
			<p class="pio-cal-empty__title">
				<?php
				if ( '' !== $s['keyword'] ) {
					echo esc_html( 'Nic nie pasuje do „' . $s['keyword'] . '”.' );
				} elseif ( $s['past'] ) {
					echo 'Brak minionych wydarzeń.';
				} else {
					echo 'Na razie nie ma zaplanowanych wydarzeń.';
				}
				?>
			</p>
			<p class="pio-cal-empty__text">
				<?php if ( '' !== $s['keyword'] ) : ?>
					Spróbuj innego słowa albo <a href="<?php echo esc_url( piodesign_cal_url( $s['past'] ? 'past' : 'list', $s, [ 'keyword' => false ] ) ); ?>">pokaż wszystkie wydarzenia</a>.
				<?php elseif ( $s['term'] ) : ?>
					W tej kategorii nic się teraz nie dzieje. <a href="<?php echo esc_url( piodesign_cal_url( $s['past'] ? 'past' : 'list', $s, [ 'term' => false ] ) ); ?>">Zobacz wszystkie kategorie</a>.
				<?php elseif ( ! $s['past'] ) : ?>
					Zajrzyj do <a href="<?php echo esc_url( $past_url ); ?>">minionych wydarzeń</a> albo wróć wkrótce.
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php foreach ( $groups as $g ) : ?>
		<?php echo piodesign_render( 'partials/month-head', [ 'month' => $g['month'], 'year' => $g['year'] ] ); // phpcs:ignore ?>
		<div class="pio-cal-list__rows">
			<?php foreach ( $g['events'] as $e ) : ?>
				<?php echo piodesign_render( 'partials/event-row', [ 'e' => $e, 'context' => 'archive', 'i' => $i++ ] ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<?php if ( $pager ) : ?>
		<nav class="pio-pager" aria-label="Strony kalendarza">
			<?php foreach ( $pager as $it ) : ?>
				<?php if ( 'gap' === $it['type'] ) : ?>
					<span class="pio-pager__gap" aria-hidden="true">…</span>
				<?php elseif ( 'prev' === $it['type'] || 'next' === $it['type'] ) : ?>
					<?php
					$label = 'prev' === $it['type'] ? ( $s['past'] ? 'Nowsze' : 'Wcześniejsze' ) : ( $s['past'] ? 'Starsze' : 'Kolejne' );
					$icon  = piodesign_icon( 'prev' === $it['type'] ? 'arrow-l' : 'arrow' );
					$inner = 'prev' === $it['type'] ? $icon . '<span>' . $label . '</span>' : '<span>' . $label . '</span>' . $icon;
					?>
					<?php if ( $it['url'] ) : ?>
						<a class="pio-pager__step pio-pager__step--<?php echo esc_attr( $it['type'] ); ?>" href="<?php echo esc_url( $it['url'] ); ?>" rel="<?php echo 'prev' === $it['type'] ? 'prev' : 'next'; ?>"><?php echo $inner; // phpcs:ignore ?></a>
					<?php else : ?>
						<span class="pio-pager__step pio-pager__step--<?php echo esc_attr( $it['type'] ); ?> is-disabled" aria-hidden="true"><?php echo $inner; // phpcs:ignore ?></span>
					<?php endif; ?>
				<?php elseif ( $it['current'] ) : ?>
					<span class="pio-pager__num is-current" aria-current="page"><?php echo (int) $it['page']; ?></span>
				<?php else : ?>
					<a class="pio-pager__num" href="<?php echo esc_url( $it['url'] ); ?>"><?php echo (int) $it['page']; ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<nav class="pio-cal-more" aria-label="Więcej">
		<?php if ( $s['past'] ) : ?>
			<a class="pio-btn" href="<?php echo esc_url( $list_url ); ?>"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?> Nadchodzące wydarzenia</a>
		<?php else : ?>
			<a class="pio-btn" href="<?php echo esc_url( $past_url ); ?>"><?php echo piodesign_icon( 'clock' ); // phpcs:ignore ?> Minione wydarzenia</a>
		<?php endif; ?>
		<a class="pio-btn" href="<?php echo esc_url( $month_url ); ?>"><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?> Widok miesiąca</a>
	</nav>
</section>
