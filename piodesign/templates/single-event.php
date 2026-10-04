<?php
/**
 * Single event: title block, 16:9 frame, content and a sticky "ticket".
 *
 * @var array  $e
 * @var string $content      Filtered post content (trusted HTML).
 * @var array  $related      Normalised upcoming events.
 * @var array|null $prev
 * @var array|null $next
 * @var string $calendar_url
 */

$year = $e['year'];
?>
<article class="pio pio-single<?php echo 'site' === piodesign_option( 'single_width' ) ? ' pio-w-site' : ''; ?>" id="pio-ev-<?php echo (int) $e['id']; ?>">
	<nav class="pio-crumbs" aria-label="Okruszki">
		<a href="<?php echo esc_url( $calendar_url ); ?>"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?> Kalendarz parafii</a>
		<span aria-hidden="true">/</span>
		<span><?php echo esc_html( $e['month_nom'] . ' ' . $year ); ?></span>
	</nav>

	<header class="pio-single__head">
		<p class="pio-ev__rel">
			<span class="pio-rel pio-rel--<?php echo esc_attr( $e['status'] ); ?>"><?php echo esc_html( $e['relative'] ); ?></span>
			<?php if ( $e['featured'] ) : ?><span class="pio-flag">Polecamy</span><?php endif; ?>
			<?php if ( ! empty( $e['category']['name'] ) ) : ?>
				<?php $cat_link = get_term_link( $e['category']['slug'], 'tribe_events_cat' ); ?>
				<?php if ( is_string( $cat_link ) ) : ?>
					<a class="pio-ev__cat" href="<?php echo esc_url( $cat_link ); ?>"><?php echo esc_html( $e['category']['name'] ); ?></a>
				<?php else : ?>
					<span class="pio-ev__cat"><?php echo esc_html( $e['category']['name'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</p>
		<h1 class="pio-single__title"><?php echo esc_html( $e['title'] ); ?></h1>
		<p class="pio-single__sub"><?php echo esc_html( piodesign_ucfirst( $e['when'] ) ); ?> · <?php echo esc_html( $e['time'] ); ?><?php echo $e['venue']['name'] ? ' · ' . esc_html( $e['venue']['name'] ) : ''; ?></p>
	</header>

	<div class="pio-single__grid">
		<div class="pio-single__main">
			<?php
			echo piodesign_render( // phpcs:ignore WordPress.Security.EscapeOutput
				'partials/frame',
				[
					'img'   => $e['image'],
					'sizes' => '(max-width: 1000px) 100vw, 780px',
					'eager' => true,
					'class' => 'pio-single__media',
				]
			);
			?>
			<div class="pio-prose">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>

		<aside class="pio-ticket" aria-label="Najważniejsze informacje">
			<div class="pio-ticket__top">
				<p class="pio-ticket__wd"><?php echo esc_html( $e['weekday'] ); ?></p>
				<p class="pio-ticket__day">
					<?php echo esc_html( $e['day'] ); ?><?php if ( $e['multi'] ) : ?><span>–<?php echo esc_html( $e['end_day'] ); ?></span><?php endif; ?>
				</p>
				<p class="pio-ticket__month"><?php echo esc_html( $e['month_gen'] . ' ' . $year ); ?></p>
				<p class="pio-ticket__time"><?php echo piodesign_icon( 'clock' ); // phpcs:ignore ?><?php echo esc_html( $e['time'] ); ?></p>
			</div>
			<div class="pio-ticket__perf" aria-hidden="true"></div>
			<div class="pio-ticket__bottom">
				<?php if ( 'upcoming' === $e['status'] ) : ?>
					<?php echo piodesign_render( 'partials/countdown', [ 'e' => $e ] ); // phpcs:ignore ?>
				<?php elseif ( 'ongoing' === $e['status'] ) : ?>
					<p class="pio-ticket__status is-live"><i class="pio-pulse" aria-hidden="true"></i>Trwa teraz<?php echo $e['day_of'] ? ' · ' . esc_html( $e['day_of'] ) : ''; ?></p>
				<?php else : ?>
					<p class="pio-ticket__status">To wydarzenie już się odbyło.</p>
				<?php endif; ?>

				<dl class="pio-ticket__facts">
					<?php if ( $e['venue']['name'] ) : ?>
						<div>
							<dt><?php echo piodesign_icon( 'pin' ); // phpcs:ignore ?>Miejsce</dt>
							<dd>
								<?php echo esc_html( $e['venue']['name'] ); ?>
								<?php if ( $e['venue']['address'] ) : ?><small><?php echo esc_html( $e['venue']['address'] ); ?></small><?php endif; ?>
								<?php if ( $e['venue']['map'] ) : ?><a href="<?php echo esc_url( $e['venue']['map'] ); ?>" target="_blank" rel="noopener">Pokaż na mapie</a><?php endif; ?>
							</dd>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $e['organizer']['name'] ) ) : ?>
						<div>
							<dt><?php echo piodesign_icon( 'user' ); // phpcs:ignore ?>Organizator</dt>
							<dd>
								<?php if ( ! empty( $e['organizer']['url'] ) ) : ?>
									<a href="<?php echo esc_url( $e['organizer']['url'] ); ?>"><?php echo esc_html( $e['organizer']['name'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $e['organizer']['name'] ); ?>
								<?php endif; ?>
							</dd>
						</div>
					<?php endif; ?>
					<?php if ( $e['cost'] ) : ?>
						<div>
							<dt>Koszt</dt>
							<dd><?php echo esc_html( $e['cost'] ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>

				<?php if ( 'past' !== $e['status'] ) : ?>
					<div class="pio-ticket__actions">
						<a class="pio-btn pio-btn--solid" href="<?php echo esc_url( $e['gcal'] ); ?>" target="_blank" rel="noopener"><?php echo piodesign_icon( 'plus' ); // phpcs:ignore ?> Kalendarz Google</a>
						<?php if ( $e['ics'] ) : ?>
							<a class="pio-btn" href="<?php echo esc_url( $e['ics'] ); ?>"><?php echo piodesign_icon( 'download' ); // phpcs:ignore ?> iPhone / Outlook (.ics)</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<button type="button" class="pio-copy" data-copy="<?php echo esc_url( $e['url'] ); ?>"><?php echo piodesign_icon( 'link' ); // phpcs:ignore ?> <span>Kopiuj link do wydarzenia</span></button>
			</div>
		</aside>
	</div>

	<?php if ( $prev || $next ) : ?>
		<nav class="pio-pn" aria-label="Sąsiednie wydarzenia">
			<?php if ( $prev ) : ?>
				<a class="pio-pn__prev" href="<?php echo esc_url( $prev['url'] ); ?>"><small><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?> Wcześniej · <?php echo esc_html( $prev['day'] . ' ' . $prev['month_gen'] ); ?></small><span><?php echo esc_html( $prev['title'] ); ?></span></a>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<a class="pio-pn__next" href="<?php echo esc_url( $next['url'] ); ?>"><small>Później · <?php echo esc_html( $next['day'] . ' ' . $next['month_gen'] ); ?> <?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></small><span><?php echo esc_html( $next['title'] ); ?></span></a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

	<?php if ( $related ) : ?>
		<section class="pio-related" aria-labelledby="pio-related-h">
			<h2 class="pio-group-h" id="pio-related-h">Co jeszcze przed nami</h2>
			<div class="pio-related__list">
				<?php foreach ( $related as $k => $r ) : ?>
					<?php echo piodesign_render( 'partials/event-row', [ 'e' => $r, 'context' => 'home', 'i' => $k ] ); // phpcs:ignore ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</article>
