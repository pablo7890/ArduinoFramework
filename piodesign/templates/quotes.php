<?php
/**
 * [pio_cytaty]: one quote at a time, revealed word by word.
 *
 * @var string[] $quotes
 * @var string   $title
 * @var string   $author
 * @var string   $photo    Image URL or ''.
 * @var int      $autoplay Seconds per quote, 0 = off.
 */

if ( ! $quotes ) {
	return;
}
$uid = 'pio-q-' . substr( md5( implode( '', $quotes ) ), 0, 6 );
$n   = count( $quotes );
?>
<section class="pio pio-quotes" id="<?php echo esc_attr( $uid ); ?>" data-pio-carousel data-autoplay="<?php echo (int) $autoplay; ?>" aria-roledescription="karuzela" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h" style="--dur: <?php echo (int) max( 1, $autoplay ); ?>s;">
	<div class="pio-quotes__glow" aria-hidden="true"></div>
	<header class="pio-quotes__head">
		<h2 class="pio-quotes__kicker" id="<?php echo esc_attr( $uid ); ?>-h"><?php echo esc_html( $title ); ?></h2>
		<span class="pio-quotes__mark" aria-hidden="true">„</span>
	</header>

	<div class="pio-quotes__stage" data-car-stage aria-live="polite">
		<?php foreach ( $quotes as $k => $q ) : ?>
			<figure class="pio-quotes__slide<?php echo 0 === $k ? ' is-active' : ''; ?>" data-car-slide<?php echo 0 === $k ? '' : ' aria-hidden="true"'; ?>>
				<blockquote class="pio-quotes__text">
					<p>
						<?php
						$words = preg_split( '/\s+/u', '„' . $q . '”' );
						foreach ( $words as $w => $word ) {
							printf( '<span class="pio-w" style="--w:%d">%s</span> ', (int) $w, esc_html( $word ) );
						}
						?>
					</p>
				</blockquote>
			</figure>
		<?php endforeach; ?>
	</div>

	<footer class="pio-quotes__foot">
		<p class="pio-quotes__author">
			<?php if ( $photo ) : ?><img src="<?php echo esc_url( $photo ); ?>" alt="" width="44" height="44" loading="lazy" decoding="async"><?php endif; ?>
			<span><?php echo esc_html( $author ); ?></span>
		</p>
		<?php if ( $n > 1 ) : ?>
			<div class="pio-car-nav">
				<span class="pio-quotes__dots" aria-hidden="true">
					<?php for ( $k = 0; $k < $n; $k++ ) : ?><i class="<?php echo 0 === $k ? 'is-active' : ''; ?>" data-car-dot="<?php echo (int) $k; ?>"></i><?php endfor; ?>
				</span>
				<button type="button" class="pio-car-btn" data-car-prev aria-label="Poprzedni cytat"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?></button>
				<button type="button" class="pio-car-btn pio-car-btn--ring" data-car-next aria-label="Następny cytat">
					<svg class="pio-ring" viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="22"/></svg>
					<?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?>
				</button>
			</div>
		<?php endif; ?>
	</footer>
</section>
