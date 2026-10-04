<?php
/**
 * Calendar page frame (list, month, day): heading with the day "ear", our
 * search, view switch, category chips, then the view itself.
 *
 * @var array        $s         piodesign_cal_state().
 * @var string       $body      Rendered view (trusted HTML).
 * @var string       $title
 * @var string       $kicker
 * @var string       $desc      Category description (trusted HTML) or ''.
 * @var array        $day       piodesign_day() for the ear.
 * @var array|null   $timeline
 * @var array[]      $cats      [ name, url, color, active ].
 * @var string       $all_url
 * @var array        $views     [ list, month ] URLs.
 * @var array        $search    [ action, value, clear ].
 * @var int|null     $count
 * @var array[]      $subscribe [ label, url ].
 */

$active = 'month' === $s['view'] ? 'month' : 'list';
?>
<div class="pio pio-cal-page pio-cal-page--<?php echo esc_attr( $s['view'] ); ?>">
	<header class="pio-archive-hero">
		<div class="pio-mast__top">
			<h1 class="pio-archive-hero__title">
				<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
				<span class="pio-archive-hero__word"><?php echo esc_html( $title ); ?></span>
			</h1>
			<div class="pio-mast__day"><?php echo piodesign_render( 'partials/dayline', [ 'day' => $day ] ); // phpcs:ignore ?></div>
		</div>

		<div class="pio-calbar">
			<form class="pio-search pio-calbar__search" role="search" method="get" action="<?php echo esc_url( $search['action'] ); ?>">
				<label for="pio-cal-q" class="screen-reader-text">Szukaj wydarzeń</label>
				<input id="pio-cal-q" type="search" name="tribe-bar-search" value="<?php echo esc_attr( $search['value'] ); ?>" placeholder="Szukaj wydarzeń, np. różaniec, kurs, koncert">
				<button type="submit" aria-label="Szukaj"><svg class="pio-i" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg></button>
			</form>
			<nav class="pio-calbar__views" aria-label="Widok kalendarza">
				<a class="<?php echo 'list' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $views['list'] ); ?>"<?php echo 'list' === $active ? ' aria-current="page"' : ''; ?>><?php echo piodesign_icon( 'list' ); // phpcs:ignore ?><span>Lista</span></a>
				<a class="<?php echo 'month' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $views['month'] ); ?>"<?php echo 'month' === $active ? ' aria-current="page"' : ''; ?>><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?><span>Miesiąc</span></a>
			</nav>
			<?php if ( $subscribe ) : ?>
				<details class="pio-calbar__sub">
					<summary><?php echo piodesign_icon( 'plus' ); // phpcs:ignore ?><span>Subskrybuj</span></summary>
					<ul>
						<?php foreach ( $subscribe as $l ) : ?>
							<li><a href="<?php echo esc_url( $l[1] ); ?>"<?php echo 0 === strpos( $l[1], 'http' ) && false === strpos( $l[1], 'ical=1' ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $l[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</details>
			<?php endif; ?>
			<?php if ( '' !== $search['value'] ) : ?>
				<p class="pio-calbar__result">
					Wyniki dla „<?php echo esc_html( $search['value'] ); ?>”<?php echo null !== $count ? ' · ' . esc_html( $count . ' ' . piodesign_plural( (int) $count, 'wydarzenie', 'wydarzenia', 'wydarzeń' ) ) : ''; ?>
					· <a href="<?php echo esc_url( $search['clear'] ); ?>">wyczyść wyszukiwanie</a>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $cats ) : ?>
			<nav class="pio-mast__bar pio-calbar__cats" aria-label="Kategorie wydarzeń">
				<div class="pio-chips">
					<a class="pio-chip<?php echo $s['term'] ? '' : ' is-active'; ?>" href="<?php echo esc_url( $all_url ); ?>"<?php echo $s['term'] ? '' : ' aria-current="page"'; ?>>Wszystkie</a>
					<?php foreach ( $cats as $c ) : ?>
						<a class="pio-chip<?php echo $c['active'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $c['url'] ); ?>" style="--cat: <?php echo esc_attr( $c['color'] ); ?>;"<?php echo $c['active'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $c['name'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</nav>
		<?php endif; ?>

		<?php if ( $desc ) : ?>
			<div class="pio-archive__desc"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $timeline ) ) : ?>
			<p class="pio-archive-hero__lede">Msze, nabożeństwa, spotkania wspólnot, warsztaty i wyjazdy. Kliknij dzień na osi, żeby przejść do wydarzenia, albo dodaj je do swojego kalendarza jednym przyciskiem.</p>
			<?php echo piodesign_render( 'partials/timeline', [ 'tl' => $timeline ] ); // phpcs:ignore ?>
		<?php endif; ?>
	</header>

	<?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
