<?php
/**
 * Dateline: weekday and date, liturgical day with its colour, parish feast.
 *
 * @var array $day pio_gazeta_day().
 */

$lit_label = 'kalendarz' === $day['source'] ? 'Dzień liturgiczny' : 'Okres liturgiczny';
$lit_note  = implode( ' · ', array_filter( [ $day['rank'], $day['color_label'] ? 'kolor ' . $day['color_label'] : '' ] ) );
?>
<div class="pio-day" style="--lit: <?php echo esc_attr( $day['color'] ); ?>;">
	<p class="pio-day__field pio-day__field--date">
		<span class="pio-day__label"><?php echo esc_html( $day['weekday'] ); ?></span>
		<time class="pio-day__value" datetime="<?php echo esc_attr( $day['iso'] ); ?>"><?php echo esc_html( $day['date'] ); ?></time>
	</p>
	<p class="pio-day__field pio-day__field--lit">
		<span class="pio-day__label"><?php echo esc_html( $lit_label ); ?></span>
		<span class="pio-day__value">
			<i class="pio-lit__dot" role="img" aria-label="<?php echo esc_attr( $day['color_label'] ? 'Kolor liturgiczny: ' . $day['color_label'] : 'Kolor liturgiczny' ); ?>"></i>
			<span><?php echo esc_html( $day['title'] ); ?><?php if ( $lit_note ) : ?> <small><?php echo esc_html( $lit_note ); ?></small><?php endif; ?></span>
		</span>
	</p>
	<?php if ( $day['parish'] ) : ?>
		<p class="pio-day__field pio-day__field--parish">
			<span class="pio-day__label">Święto parafialne</span>
			<span class="pio-day__value"><?php echo esc_html( $day['parish'] ); ?></span>
		</p>
	<?php endif; ?>
</div>
