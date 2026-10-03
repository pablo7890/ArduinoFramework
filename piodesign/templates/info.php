<?php
/**
 * [pio_informacje]: the section before the footer. A bento grid: Mass times
 * (with the next Mass, live), parish office (open / closed, live), contact,
 * bank account, click-to-load map and partner sites.
 *
 * @var array  $masses   piodesign_mass_schedule().
 * @var array  $office   piodesign_office_hours().
 * @var array  $o        Settings.
 * @var array  $partners piodesign_partners().
 * @var bool   $advent
 * @var int    $today    1–7.
 * @var array  $slots    Upcoming Masses "Y-m-dTH:i" from the intentions plugin (may be empty).
 * @var string $title
 * @var string $kicker
 */

$now    = piodesign_now();
$slots  = $slots ?? [];
$next   = ( $slots ? piodesign_next_mass_from_slots( $slots, $now ) : null ) ?: piodesign_next_mass( $masses, $now, $advent );
$status = piodesign_office_status( $office, $now, ! empty( $o['kancelaria_i_piatek'] ) );
$cols   = [
	[ 'key' => 'sun', 'name' => 'Niedziele i święta', 'days' => [ 7 ] ],
	[ 'key' => 'wk', 'name' => 'Poniedziałek – piątek', 'days' => [ 1, 2, 3, 4, 5 ] ],
	[ 'key' => 'sat', 'name' => 'Sobota', 'days' => [ 6 ] ],
];
$adres  = array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $o['adres'] ) ) ) );
$phone  = preg_replace( '/[^\d+]/', '', (string) $o['telefon'] );
$route  = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $o['mapa'] );
$embed  = 'https://www.google.com/maps?q=' . rawurlencode( $o['mapa'] ) . '&output=embed';
$wd     = piodesign_weekdays( true );
$data   = [
	'masses'      => $masses,
	'office'      => $office,
	'advent'      => (bool) $advent,
	'firstFriday' => ! empty( $o['kancelaria_i_piatek'] ),
	'slots'       => array_values( $slots ),
];
?>
<section class="pio pio-info" aria-labelledby="pio-info-h" data-pio-info="<?php echo esc_attr( wp_json_encode( $data ) ); ?>">
	<header class="pio-info__head">
		<h2 class="pio-sec-head__title" id="pio-info-h">
			<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
			<span class="pio-sec-head__word"><?php echo esc_html( $title ); ?></span>
		</h2>
		<?php if ( $next ) : ?>
			<p class="pio-next-mass" data-next-mass>
				<i class="pio-pulse" aria-hidden="true"></i>
				<span class="pio-next-mass__label">Najbliższa Msza święta</span>
				<strong><span data-nm-day><?php echo esc_html( $next['label'] ); ?></span> <span data-nm-time><?php echo esc_html( piodesign_time_label( $next['time'] ) ); ?></span></strong>
				<span class="pio-next-mass__in" data-nm-in><?php echo esc_html( $next['in'] ); ?></span>
			</p>
		<?php endif; ?>
	</header>

	<div class="pio-info__grid">
		<article class="pio-tile pio-tile--masses" aria-labelledby="pio-info-masses">
			<h3 class="pio-tile__h" id="pio-info-masses"><?php echo piodesign_icon( 'clock' ); // phpcs:ignore ?>Porządek Mszy świętych</h3>
			<div class="pio-masses">
				<?php foreach ( $cols as $c ) : ?>
					<?php $is_today = in_array( $today, $c['days'], true ); ?>
					<div class="pio-masses__col<?php echo $is_today ? ' is-today' : ''; ?>" data-mass-col="<?php echo esc_attr( $c['key'] ); ?>">
						<p class="pio-masses__name"><?php echo esc_html( $c['name'] ); ?><?php if ( $is_today ) : ?> <span class="pio-masses__today">dziś</span><?php endif; ?></p>
						<ul class="pio-masses__list">
							<?php foreach ( $masses[ $c['key'] ] as $m ) : ?>
								<?php $is_next = $next && $is_today && $next['time'] === ( $advent && $m['advent'] && 'sun' !== $c['key'] ? $m['advent'] : $m['time'] ) && 'dziś' === $next['label']; ?>
								<li class="<?php echo $is_next ? 'is-next' : ''; ?>" data-mass-time="<?php echo esc_attr( $m['time'] ); ?>"<?php if ( $m['advent'] ) : ?> data-mass-advent="<?php echo esc_attr( $m['advent'] ); ?>"<?php endif; ?>>
									<b><?php echo esc_html( $m['label'] ); ?></b>
									<?php if ( $m['note'] ) : ?><small><?php echo esc_html( $m['note'] ); ?></small><?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
			$adv = array_unique( array_filter( array_column( array_merge( $masses['wk'], $masses['sat'] ), 'advent' ) ) );
			if ( $advent && $adv ) :
				?>
				<p class="pio-tile__note pio-tile__note--season">Trwa Adwent – w dni powszednie i w soboty Msza poranna o <?php echo esc_html( implode( ', ', array_map( 'piodesign_time_label', $adv ) ) ); ?>.</p>
			<?php endif; ?>
		</article>

		<article class="pio-tile pio-tile--office" aria-labelledby="pio-info-office">
			<h3 class="pio-tile__h" id="pio-info-office"><?php echo piodesign_icon( 'book' ); // phpcs:ignore ?>Kancelaria parafialna</h3>
			<p class="pio-status<?php echo $status[0] ? ' is-open' : ''; ?>" data-office-status><i aria-hidden="true"></i><span><?php echo esc_html( $status[1] ); ?></span></p>
			<ol class="pio-week">
				<?php for ( $d = 1; $d <= 7; $d++ ) : ?>
					<?php
					$slots = array_values( array_filter( $office, static fn( $h ) => $h['day'] === $d ) );
					?>
					<li class="<?php echo $d === $today ? 'is-today' : ''; ?><?php echo $slots ? ' has-hours' : ''; ?>" data-office-day="<?php echo (int) $d; ?>">
						<span class="pio-week__d"><?php echo esc_html( $wd[ $d ] ); ?></span>
						<span class="pio-week__h">
							<?php
							echo $slots
								? esc_html( implode( ', ', array_map( static fn( $h ) => piodesign_time_label( $h['from'] ) . '–' . piodesign_time_label( $h['to'] ), $slots ) ) )
								: '<span aria-hidden="true">—</span><span class="screen-reader-text">nieczynne</span>';
							?>
						</span>
					</li>
				<?php endfor; ?>
			</ol>
			<?php if ( $o['kancelaria_uwagi'] ) : ?><p class="pio-tile__note"><?php echo esc_html( $o['kancelaria_uwagi'] ); ?></p><?php endif; ?>
		</article>

		<article class="pio-tile pio-tile--contact" aria-labelledby="pio-info-contact">
			<h3 class="pio-tile__h" id="pio-info-contact"><?php echo piodesign_icon( 'pin' ); // phpcs:ignore ?>Kontakt</h3>
			<address class="pio-contact">
				<span class="pio-contact__name"><?php echo esc_html( $o['nazwa'] ); ?></span>
				<?php foreach ( $adres as $line ) : ?><span><?php echo esc_html( $line ); ?></span><?php endforeach; ?>
			</address>
			<ul class="pio-contact__links">
				<?php if ( $o['telefon'] ) : ?>
					<li><a class="pio-contact__big" href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $o['telefon'] ); ?></a><button type="button" class="pio-copy pio-copy--icon" data-copy="<?php echo esc_attr( $o['telefon'] ); ?>" data-done="Skopiowano numer" aria-label="Kopiuj numer telefonu"><?php echo piodesign_icon( 'copy' ); // phpcs:ignore ?><span class="screen-reader-text">Kopiuj</span></button></li>
				<?php endif; ?>
				<?php if ( $o['email'] ) : ?>
					<li><a class="pio-contact__mail" href="mailto:<?php echo esc_attr( $o['email'] ); ?>"><?php echo esc_html( $o['email'] ); ?></a><button type="button" class="pio-copy pio-copy--icon" data-copy="<?php echo esc_attr( $o['email'] ); ?>" data-done="Skopiowano adres" aria-label="Kopiuj adres e-mail"><?php echo piodesign_icon( 'copy' ); // phpcs:ignore ?><span class="screen-reader-text">Kopiuj</span></button></li>
				<?php endif; ?>
			</ul>
			<a class="pio-tile__link" href="<?php echo esc_url( $route ); ?>" target="_blank" rel="noopener">Wyznacz trasę <?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></a>
		</article>

		<article class="pio-tile pio-tile--map" aria-label="Mapa dojazdu">
			<div class="pio-map" data-map="<?php echo esc_url( $embed ); ?>">
				<div class="pio-map__art" aria-hidden="true">
					<i class="pio-map__river"></i><i class="pio-map__park"></i>
					<span class="pio-map__pin"><i></i></span>
				</div>
				<div class="pio-map__label">
					<b><?php echo esc_html( $adres[0] ?? $o['mapa'] ); ?></b>
					<span><?php echo esc_html( $adres[1] ?? '' ); ?></span>
				</div>
				<div class="pio-map__actions">
					<button type="button" class="pio-btn pio-btn--solid" data-map-load><?php echo piodesign_icon( 'pin' ); // phpcs:ignore ?> Pokaż mapę</button>
					<a class="pio-btn pio-btn--glass" href="<?php echo esc_url( $route ); ?>" target="_blank" rel="noopener">Trasa <?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></a>
				</div>
				<p class="pio-map__hint">Mapa Google wczyta się po kliknięciu.</p>
			</div>
		</article>

		<?php if ( $o['konto'] ) : ?>
			<article class="pio-tile pio-tile--bank" aria-labelledby="pio-info-bank">
				<h3 class="pio-tile__h" id="pio-info-bank"><?php echo piodesign_icon( 'heart' ); // phpcs:ignore ?>Wesprzyj parafię</h3>
				<p class="pio-bank__label">Numer konta</p>
				<p class="pio-bank__num">
					<?php foreach ( explode( ' ', trim( preg_replace( '/\s+/', ' ', $o['konto'] ) ) ) as $grp ) : ?><span><?php echo esc_html( $grp ); ?></span><?php endforeach; ?>
				</p>
				<p class="pio-bank__to"><?php echo esc_html( $o['nazwa'] ); ?></p>
				<button type="button" class="pio-copy pio-copy--btn" data-copy="<?php echo esc_attr( piodesign_account_digits( $o['konto'] ) ); ?>" data-done="Skopiowano numer konta"><?php echo piodesign_icon( 'copy' ); // phpcs:ignore ?> <span>Kopiuj numer konta</span></button>
			</article>
		<?php endif; ?>

		<?php if ( $partners || $o['facebook'] || $o['youtube'] ) : ?>
			<article class="pio-tile pio-tile--links" aria-labelledby="pio-info-links">
				<h3 class="pio-tile__h" id="pio-info-links"><?php echo piodesign_icon( 'link' ); // phpcs:ignore ?>Zajrzyj także</h3>
				<ul class="pio-partners">
					<?php foreach ( $partners as $p ) : ?>
						<li>
							<a class="pio-partner" href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener">
								<span class="pio-partner__mono" aria-hidden="true"><?php echo esc_html( piodesign_initials( $p['name'] ) ); ?></span>
								<span class="pio-partner__body">
									<b><?php echo esc_html( $p['name'] ); ?></b>
									<?php if ( $p['note'] ) : ?><small><?php echo esc_html( $p['note'] ); ?></small><?php endif; ?>
									<em><?php echo esc_html( $p['host'] ); ?></em>
								</span>
								<?php echo piodesign_icon( 'external' ); // phpcs:ignore ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="pio-social">
					<?php if ( $o['facebook'] ) : ?><a href="<?php echo esc_url( $o['facebook'] ); ?>" target="_blank" rel="noopener"><?php echo piodesign_icon( 'facebook' ); // phpcs:ignore ?> Facebook</a><?php endif; ?>
					<?php if ( $o['youtube'] ) : ?><a href="<?php echo esc_url( $o['youtube'] ); ?>" target="_blank" rel="noopener"><?php echo piodesign_icon( 'youtube' ); // phpcs:ignore ?> YouTube</a><?php endif; ?>
				</p>
			</article>
		<?php endif; ?>
	</div>
</section>
