<?php
/**
 * The "ear" beside a section title: date, liturgical day with its colour,
 * parish feast. Compact, two columns of label / value.
 *
 * @var array $day piodesign_day().
 */

$lit_label = 'kalendarz' === $day['source'] ? 'Dzień liturgiczny' : 'Okres liturgiczny';
$lit_note  = implode( ' · ', array_filter( [ $day['rank'], $day['color_label'] ? 'kolor ' . $day['color_label'] : '' ] ) );
?>
<div class="pio-ear" style="--lit: <?php echo esc_attr( $day['color'] ); ?>;">
	<p class="pio-ear__date">
		<time datetime="<?php echo esc_attr( $day['iso'] ); ?>"><b><?php echo esc_html( $day['weekday'] ); ?></b>, <?php echo esc_html( $day['date'] ); ?></time>
	</p>
	<dl class="pio-ear__list">
		<div class="pio-ear__row">
			<dt><?php echo esc_html( $lit_label ); ?></dt>
			<dd>
				<span class="pio-ear__lit">
					<i class="pio-lit__dot" role="img" aria-label="<?php echo esc_attr( $day['color_label'] ? 'Kolor liturgiczny: ' . $day['color_label'] : 'Kolor liturgiczny' ); ?>"></i>
					<span><?php echo esc_html( $day['title'] ); ?></span>
				</span>
				<?php if ( $lit_note ) : ?><small><?php echo esc_html( $lit_note ); ?></small><?php endif; ?>
			</dd>
		</div>
		<?php if ( $day['parish'] ) : ?>
			<div class="pio-ear__row">
				<dt>Święto parafialne</dt>
				<dd><?php echo esc_html( $day['parish'] ); ?></dd>
			</div>
		<?php endif; ?>
	</dl>
</div>
