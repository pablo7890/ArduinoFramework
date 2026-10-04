<?php
/**
 * Calendar: month grid. Multi-day events run as bars across the week;
 * single-day events are listed in their day. On narrow screens the grid
 * becomes a compact month with dots and an agenda below it.
 *
 * @var array             $s
 * @var DateTimeImmutable $month       First day of the month.
 * @var array[]           $weeks       [ days[], bars[], lanes ].
 * @var array[]           $heads       [ short, full, is_sunday ].
 * @var int               $count       Events touching this month.
 * @var array[]           $agenda      'Y-m-d' => events[].
 * @var array             $prev        [ name, url ].
 * @var array             $next        [ name, url ].
 * @var string            $today_url   '' when this is the current month.
 * @var array|null        $next_event  First event after an empty month.
 * @var string            $next_month_url
 * @var string            $list_url
 */

$name = piodesign_months( 'nom' )[ (int) $month->format( 'n' ) ];
$uid  = 'pio-cal-' . $month->format( 'Ym' );

/** Small hover / focus card for an event. */
$pop = static function ( array $e, $right ) {
	ob_start();
	?>
	<span class="pio-cal__pop<?php echo $right ? ' is-right' : ''; ?>" aria-hidden="true">
		<?php if ( ! empty( $e['image']['src'] ) ) : ?>
			<span class="pio-cal__pop-img" style="background-image: url('<?php echo esc_url( $e['image']['src'] ); ?>');"></span>
		<?php endif; ?>
		<?php if ( ! empty( $e['category']['name'] ) ) : ?><span class="pio-cal__pop-cat"><?php echo esc_html( $e['category']['name'] ); ?></span><?php endif; ?>
		<span class="pio-cal__pop-t"><?php echo esc_html( $e['title'] ); ?></span>
		<span class="pio-cal__pop-when"><?php echo esc_html( piodesign_ucfirst( $e['when'] ) . ' · ' . $e['time'] ); ?></span>
		<?php if ( $e['venue']['name'] ) : ?><span class="pio-cal__pop-where"><?php echo esc_html( $e['venue']['name'] ); ?></span><?php endif; ?>
	</span>
	<?php
	return ob_get_clean();
};
?>
<section class="pio-cal" id="<?php echo esc_attr( $uid ); ?>" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h">
	<header class="pio-cal__head">
		<h2 class="pio-cal__title" id="<?php echo esc_attr( $uid ); ?>-h">
			<span class="pio-cal__name"><?php echo esc_html( $name ); ?></span>
			<span class="pio-month__year"><?php echo esc_html( $month->format( 'Y' ) ); ?></span>
		</h2>
		<p class="pio-cal__sum"><?php echo $count ? esc_html( $count . ' ' . piodesign_plural( $count, 'wydarzenie', 'wydarzenia', 'wydarzeń' ) ) : 'bez wydarzeń'; ?></p>
		<nav class="pio-cal__nav" aria-label="Miesiące">
			<a class="pio-cal__step" href="<?php echo esc_url( $prev[1] ); ?>" rel="prev"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?><span><?php echo esc_html( $prev[0] ); ?></span></a>
			<?php if ( $today_url ) : ?>
				<a class="pio-cal__step is-today" href="<?php echo esc_url( $today_url ); ?>">Bieżący miesiąc</a>
			<?php endif; ?>
			<a class="pio-cal__step" href="<?php echo esc_url( $next[1] ); ?>" rel="next"><span><?php echo esc_html( $next[0] ); ?></span><?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></a>
		</nav>
	</header>

	<?php if ( ! $count ) : ?>
		<div class="pio-cal-empty pio-cal-empty--inline">
			<span class="pio-cal-empty__icon" aria-hidden="true"><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?></span>
			<p class="pio-cal-empty__title">
				<?php echo '' !== $s['keyword'] ? esc_html( 'W tym miesiącu nic nie pasuje do „' . $s['keyword'] . '”.' ) : 'W tym miesiącu nie ma jeszcze wydarzeń.'; ?>
			</p>
			<?php if ( $next_event ) : ?>
				<p class="pio-cal-empty__text">Najbliższe: <a href="<?php echo esc_url( $next_event['url'] ); ?>"><?php echo esc_html( $next_event['title'] ); ?></a>, <?php echo esc_html( $next_event['when'] ); ?> · <a href="<?php echo esc_url( $next_month_url ); ?>">przejdź do miesiąca</a></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="pio-cal__grid" role="table" aria-label="<?php echo esc_attr( $name . ' ' . $month->format( 'Y' ) ); ?>">
		<div class="pio-cal__wds" role="row">
			<?php foreach ( $heads as $h ) : ?>
				<span role="columnheader" class="<?php echo $h[2] ? 'is-sunday' : ''; ?>"><abbr title="<?php echo esc_attr( $h[1] ); ?>"><?php echo esc_html( $h[0] ); ?></abbr></span>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $weeks as $w ) : ?>
			<div class="pio-cal__week" role="row" style="grid-template-rows: auto <?php echo esc_attr( str_repeat( 'var(--pio-cal-bar) ', (int) $w['lanes'] ) ); ?>minmax(var(--pio-cal-min), 1fr);">
				<?php foreach ( $w['days'] as $d ) : ?>
					<?php
					$cls = [ 'pio-cal__cell', 'is-c' . (int) $d['col'] ];
					foreach ( [ 'out', 'today', 'past', 'sunday' ] as $flag ) {
						if ( $d[ $flag ] ) {
							$cls[] = 'is-' . $flag;
						}
					}
					if ( $d['all'] ) {
						$cls[] = 'has-events';
					}
					$n     = count( $d['all'] );
					$feast = piodesign_cal_feast( $d['lit'] );
					$label = piodesign_date( $d['date'], false, true ) . ( $n ? ', ' . $n . ' ' . piodesign_plural( $n, 'wydarzenie', 'wydarzenia', 'wydarzeń' ) : '' );
					?>
					<div class="<?php echo esc_attr( implode( ' ', $cls ) ); ?>" role="cell" style="grid-column: <?php echo (int) $d['col']; ?>;" aria-label="<?php echo esc_attr( $label ); ?>"></div>
					<div class="pio-cal__dhead<?php echo $d['today'] ? ' is-today' : ''; ?><?php echo $d['out'] ? ' is-out' : ''; ?><?php echo $d['sunday'] ? ' is-sunday' : ''; ?>" style="grid-column: <?php echo (int) $d['col']; ?>;">
						<?php if ( $n ) : ?>
							<a class="pio-cal__num" href="<?php echo esc_url( $d['url'] ); ?>" title="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $d['date']->format( 'j' ) ); ?></a>
						<?php else : ?>
							<span class="pio-cal__num"><?php echo esc_html( $d['date']->format( 'j' ) ); ?></span>
						<?php endif; ?>
						<?php if ( $d['lit'] ) : ?>
							<i class="pio-cal__lit<?php echo piodesign_is_light( $d['lit']['color'] ) ? ' is-light' : ''; ?>" style="--c: <?php echo esc_attr( $d['lit']['color'] ); ?>;" title="<?php echo esc_attr( $d['lit']['title'] ); ?>"></i>
						<?php endif; ?>
						<?php if ( $feast ) : ?>
							<span class="pio-cal__feast" title="<?php echo esc_attr( $feast ); ?>"><?php echo esc_html( $feast ); ?></span>
						<?php endif; ?>
						<?php if ( $n ) : ?>
							<span class="pio-cal__dots" aria-hidden="true">
								<?php foreach ( array_slice( $d['all'], 0, 3 ) as $e ) : ?><i style="--cat: <?php echo esc_attr( piodesign_event_cat_color( $e['category']['slug'] ?? '' ) ); ?>;"></i><?php endforeach; ?>
							</span>
							<?php if ( ! $d['out'] ) : ?>
								<a class="pio-cal__tap" href="#<?php echo esc_attr( $uid . '-' . $d['key'] ); ?>" tabindex="-1" aria-hidden="true"></a>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<?php if ( $d['items'] || $d['more'] ) : ?>
						<ul class="pio-cal__items" style="grid-column: <?php echo (int) $d['col']; ?>; grid-row: <?php echo 2 + (int) $w['lanes']; ?>;">
							<?php foreach ( $d['items'] as $e ) : ?>
								<li>
									<a class="pio-cal__ev<?php echo 'past' === $e['status'] ? ' is-past' : ''; ?><?php echo $e['all_day'] ? ' is-allday' : ''; ?>" href="<?php echo esc_url( $e['url'] ); ?>" style="--cat: <?php echo esc_attr( piodesign_event_cat_color( $e['category']['slug'] ?? '' ) ); ?>;">
										<?php if ( ! $e['all_day'] ) : ?><time datetime="<?php echo esc_attr( $e['start_iso'] ); ?>"><?php echo esc_html( ( new DateTimeImmutable( $e['start_iso'] ) )->format( 'G:i' ) ); ?></time><?php endif; ?>
										<span class="pio-cal__t"><?php echo esc_html( $e['title'] ); ?></span>
										<?php echo $pop( $e, $d['col'] >= 5 ); // phpcs:ignore ?>
									</a>
								</li>
							<?php endforeach; ?>
							<?php if ( $d['more'] ) : ?>
								<li><a class="pio-cal__more" href="<?php echo esc_url( $d['url'] ); ?>">+<?php echo (int) $d['more']; ?> więcej</a></li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php foreach ( $w['bars'] as $b ) : ?>
					<?php $e = $b['e']; ?>
					<a class="pio-cal__bar<?php echo $b['left'] ? ' is-left' : ''; ?><?php echo $b['right'] ? ' is-right' : ''; ?><?php echo 'past' === $e['status'] ? ' is-past' : ''; ?>" href="<?php echo esc_url( $e['url'] ); ?>" style="grid-column: <?php echo (int) $b['c0']; ?> / <?php echo (int) $b['c1'] + 1; ?>; grid-row: <?php echo 2 + (int) $b['lane']; ?>; --cat: <?php echo esc_attr( piodesign_event_cat_color( $e['category']['slug'] ?? '' ) ); ?>;">
						<span class="pio-cal__t"><?php echo esc_html( $e['title'] ); ?></span>
						<?php echo $pop( $e, $b['c0'] >= 5 ); // phpcs:ignore ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $agenda ) : ?>
		<div class="pio-cal__agenda">
			<?php foreach ( $agenda as $key => $list ) : ?>
				<?php $d = new DateTimeImmutable( $key, $month->getTimezone() ); ?>
				<div class="pio-cal__aday" id="<?php echo esc_attr( $uid . '-' . $key ); ?>">
					<p class="pio-cal__adate<?php echo 7 === (int) $d->format( 'N' ) ? ' is-sunday' : ''; ?>">
						<b><?php echo esc_html( $d->format( 'j' ) ); ?></b>
						<span><?php echo esc_html( piodesign_weekdays()[ (int) $d->format( 'N' ) ] ); ?></span>
					</p>
					<ul>
						<?php foreach ( $list as $e ) : ?>
							<li>
								<a class="pio-cal__arow<?php echo 'past' === $e['status'] ? ' is-past' : ''; ?>" href="<?php echo esc_url( $e['url'] ); ?>" style="--cat: <?php echo esc_attr( piodesign_event_cat_color( $e['category']['slug'] ?? '' ) ); ?>;">
									<span class="pio-cal__atime"><?php echo esc_html( $e['multi'] ? $e['when'] : $e['time'] ); ?></span>
									<span class="pio-cal__t"><?php echo esc_html( $e['title'] ); ?></span>
									<?php if ( $e['venue']['name'] ) : ?><small><?php echo esc_html( $e['venue']['name'] ); ?></small><?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<nav class="pio-cal-more" aria-label="Więcej">
		<a class="pio-btn" href="<?php echo esc_url( $list_url ); ?>"><?php echo piodesign_icon( 'list' ); // phpcs:ignore ?> Lista wydarzeń</a>
	</nav>
</section>
