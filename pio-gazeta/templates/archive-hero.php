<?php
/**
 * Hero above the TEC list view.
 *
 * @var array  $liturgy
 * @var string $today
 * @var array  $timeline Optional pio_gazeta_timeline().
 */
?>
<div class="pio pio-archive-hero" style="--lit: <?php echo esc_attr( $liturgy['color'] ); ?>;">
	<p class="pio-dateline">
		<span><?php echo esc_html( $today ); ?></span>
		<span class="pio-lit" title="Kolor liturgiczny: <?php echo esc_attr( $liturgy['color_name'] ); ?>">
			<i class="pio-lit__dot" aria-hidden="true"></i>
			<?php echo esc_html( trim( $liturgy['label'] . ( $liturgy['week'] ? ', ' . $liturgy['week'] : '' ) ) ); ?>
		</span>
	</p>
	<h1 class="pio-archive-hero__title">
		<span class="pio-mast__kicker">Kalendarz</span>
		<span class="pio-archive-hero__word">parafii</span>
	</h1>
	<p class="pio-archive-hero__lede">Msze, nabożeństwa, spotkania wspólnot, warsztaty i wyjazdy. Kliknij dzień na osi, żeby przejść do wydarzenia, albo dodaj je do swojego kalendarza jednym przyciskiem.</p>
	<?php if ( ! empty( $timeline ) ) : ?>
		<?php echo pio_gazeta_render( 'partials/timeline', [ 'tl' => $timeline ] ); // phpcs:ignore ?>
	<?php endif; ?>
</div>
