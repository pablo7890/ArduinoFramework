<?php
/**
 * Hero above the TEC list view.
 *
 * @var array  $day      piodesign_day().
 * @var array  $timeline Optional piodesign_timeline().
 */
?>
<div class="pio pio-archive-hero">
	<div class="pio-mast__top">
		<h1 class="pio-archive-hero__title">
			<span class="pio-mast__kicker">Kalendarz</span>
			<span class="pio-archive-hero__word">Nadchodzące wydarzenia</span>
		</h1>
		<div class="pio-mast__day"><?php echo piodesign_render( 'partials/dayline', [ 'day' => $day ] ); // phpcs:ignore ?></div>
	</div>
	<p class="pio-archive-hero__lede">Msze, nabożeństwa, spotkania wspólnot, warsztaty i wyjazdy. Kliknij dzień na osi, żeby przejść do wydarzenia, albo dodaj je do swojego kalendarza jednym przyciskiem.</p>
	<?php if ( ! empty( $timeline ) ) : ?>
		<?php echo piodesign_render( 'partials/timeline', [ 'tl' => $timeline ] ); // phpcs:ignore ?>
	<?php endif; ?>
</div>
