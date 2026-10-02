<?php
/**
 * [pio_sakramenty]: names on the left (tabs with an autoplay progress line),
 * a large image stage on the right. On narrow containers the names become a
 * scrollable row and the text sits under the image.
 *
 * @var array[] $slides   [ id, slug, title, text, url, image ].
 * @var string  $title
 * @var string  $kicker
 * @var int     $autoplay Seconds per slide, 0 = off.
 * @var string  $more_url
 */

if ( ! $slides ) {
	return;
}
$uid = 'pio-sac-' . substr( md5( serialize( array_column( $slides, 'id' ) ) ), 0, 6 );
$n   = count( $slides );
?>
<section class="pio pio-sac" id="<?php echo esc_attr( $uid ); ?>" data-pio-carousel data-autoplay="<?php echo (int) $autoplay; ?>" aria-roledescription="karuzela" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h" style="--dur: <?php echo (int) max( 1, $autoplay ); ?>s;">
	<header class="pio-sec-head">
		<h2 class="pio-sec-head__title" id="<?php echo esc_attr( $uid ); ?>-h">
			<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
			<span class="pio-sec-head__word"><?php echo esc_html( $title ); ?></span>
		</h2>
		<div class="pio-car-nav">
			<span class="pio-car-count" aria-hidden="true"><b data-car-current>1</b> / <?php echo (int) $n; ?></span>
			<button type="button" class="pio-car-btn" data-car-prev aria-label="Poprzedni sakrament"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?></button>
			<button type="button" class="pio-car-btn" data-car-next aria-label="Następny sakrament"><?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></button>
		</div>
	</header>

	<div class="pio-sac__body">
		<div class="pio-sac__tabs" role="tablist" aria-label="Sakramenty">
			<?php foreach ( $slides as $k => $s ) : ?>
				<button type="button" class="pio-sac__tab<?php echo 0 === $k ? ' is-active' : ''; ?>" role="tab" id="<?php echo esc_attr( $uid . '-t' . $k ); ?>" aria-controls="<?php echo esc_attr( $uid . '-p' . $k ); ?>" aria-selected="<?php echo 0 === $k ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $k ? '0' : '-1'; ?>" data-car-tab="<?php echo (int) $k; ?>">
					<span class="pio-sac__name"><?php echo esc_html( $s['title'] ); ?></span>
					<i class="pio-sac__bar" aria-hidden="true"><i></i></i>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="pio-sac__stage" data-car-stage>
			<?php foreach ( $slides as $k => $s ) : ?>
				<article class="pio-sac__slide<?php echo 0 === $k ? ' is-active' : ''; ?>" role="tabpanel" id="<?php echo esc_attr( $uid . '-p' . $k ); ?>" aria-labelledby="<?php echo esc_attr( $uid . '-t' . $k ); ?>" data-car-slide<?php echo 0 === $k ? '' : ' aria-hidden="true" inert'; ?>>
					<div class="pio-sac__media">
						<?php if ( $s['image'] ) : ?>
							<img src="<?php echo esc_url( $s['image']['src'] ); ?>"<?php if ( ! empty( $s['image']['srcset'] ) ) : ?> srcset="<?php echo esc_attr( $s['image']['srcset'] ); ?>" sizes="(max-width: 760px) 100vw, 60vw"<?php endif; ?> alt="" loading="<?php echo 0 === $k ? 'eager' : 'lazy'; ?>" decoding="async">
						<?php else : ?>
							<span class="pio-sac__mono" aria-hidden="true"><?php echo esc_html( mb_substr( $s['title'], 0, 1 ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="pio-sac__card">
						<h3 class="pio-sac__title"><?php echo esc_html( $s['title'] ); ?></h3>
						<?php if ( $s['text'] ) : ?><p class="pio-sac__text"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
						<a class="pio-sac__more" href="<?php echo esc_url( $s['url'] ); ?>">Dowiedz się więcej <span class="screen-reader-text">– <?php echo esc_html( $s['title'] ); ?></span><?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
