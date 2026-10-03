<?php
/**
 * Hero above the TEC list view.
 *
 * @var array  $day      piodesign_day().
 * @var array  $timeline Optional piodesign_timeline().
 * @var array  $search   [ action, value, month_url ] – our own search, replacing TEC's bar.
 */

$search = $search ?? null;
?>
<div class="pio pio-archive-hero">
	<div class="pio-mast__top">
		<h1 class="pio-archive-hero__title">
			<span class="pio-mast__kicker">Kalendarz</span>
			<span class="pio-archive-hero__word">Nadchodzące wydarzenia</span>
		</h1>
		<div class="pio-mast__day"><?php echo piodesign_render( 'partials/dayline', [ 'day' => $day ] ); // phpcs:ignore ?></div>
	</div>
	<?php if ( $search ) : ?>
		<div class="pio-calbar">
			<form class="pio-search pio-calbar__search" role="search" method="get" action="<?php echo esc_url( $search['action'] ); ?>">
				<label for="pio-cal-q" class="screen-reader-text">Szukaj wydarzeń</label>
				<input id="pio-cal-q" type="search" name="tribe-bar-search" value="<?php echo esc_attr( $search['value'] ); ?>" placeholder="Szukaj wydarzeń, np. różaniec, kurs, koncert">
				<button type="submit" aria-label="Szukaj"><svg class="pio-i" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg></button>
			</form>
			<nav class="pio-calbar__views" aria-label="Widok kalendarza">
				<a class="is-active" href="<?php echo esc_url( $search['action'] ); ?>" aria-current="page"><?php echo piodesign_icon( 'list' ); // phpcs:ignore ?>Lista</a>
				<?php if ( $search['month_url'] ) : ?><a href="<?php echo esc_url( $search['month_url'] ); ?>"><?php echo piodesign_icon( 'calendar' ); // phpcs:ignore ?>Miesiąc</a><?php endif; ?>
			</nav>
			<?php if ( '' !== $search['value'] ) : ?>
				<p class="pio-calbar__result">Wyniki dla „<?php echo esc_html( $search['value'] ); ?>” · <a href="<?php echo esc_url( $search['action'] ); ?>">pokaż wszystkie</a></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<p class="pio-archive-hero__lede">Msze, nabożeństwa, spotkania wspólnot, warsztaty i wyjazdy. Kliknij dzień na osi, żeby przejść do wydarzenia, albo dodaj je do swojego kalendarza jednym przyciskiem.</p>
	<?php if ( ! empty( $timeline ) ) : ?>
		<?php echo piodesign_render( 'partials/timeline', [ 'tl' => $timeline ] ); // phpcs:ignore ?>
	<?php endif; ?>
</div>
