<?php
/**
 * 16:9 smart frame. Cover-cropped photos; posters shown whole on a blurred
 * copy of themselves. Optional gallery preview cycles on hover.
 *
 * @var array|null $img
 * @var string     $sizes
 * @var bool       $eager
 * @var array      $gallery
 * @var string     $badge   Trusted HTML.
 * @var string     $class
 */

$sizes   = $sizes ?? '(max-width: 700px) 100vw, 33vw';
$eager   = ! empty( $eager );
$gallery = $gallery ?? [];
$class   = trim( 'pio-frame ' . ( $class ?? '' ) );

if ( empty( $img ) ) : ?>
	<div class="<?php echo esc_attr( $class ); ?> pio-frame--empty" aria-hidden="true">
		<svg viewBox="0 0 64 64" width="44" height="44" fill="none" stroke="currentColor" stroke-width="2"><path d="M32 8v48M18 22h28"/></svg>
		<?php echo $badge ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<?php
	return;
endif;

$class .= ' pio-frame--' . $img['fit'];
?>
<div class="<?php echo esc_attr( $class ); ?>"<?php if ( $gallery ) : ?> data-gallery="<?php echo esc_attr( wp_json_encode( array_values( $gallery ) ) ); ?>"<?php endif; ?>>
	<?php if ( 'contain' === $img['fit'] ) : ?>
		<img class="pio-frame__bg" src="<?php echo esc_url( $img['src'] ); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
	<?php endif; ?>
	<img class="pio-frame__img"
		src="<?php echo esc_url( $img['src'] ); ?>"
		<?php if ( ! empty( $img['srcset'] ) ) : ?>srcset="<?php echo esc_attr( $img['srcset'] ); ?>" sizes="<?php echo esc_attr( $sizes ); ?>"<?php endif; ?>
		width="<?php echo (int) $img['w']; ?>" height="<?php echo (int) $img['h']; ?>"
		alt="<?php echo esc_attr( $img['alt'] ); ?>"
		<?php echo $eager ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async">
	<?php echo $badge ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
