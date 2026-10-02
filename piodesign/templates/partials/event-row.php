<?php
/**
 * Agenda row: date tile, what/when/where, 16:9 thumbnail.
 *
 * @var array  $e       Normalised event (piodesign_event()).
 * @var string $context home | archive.
 * @var int    $i
 * @var bool   $mhide   Hidden on phones.
 */

$context = $context ?? 'home';
$i       = $i ?? 0;
$classes = [ 'pio-ev', 'pio-ev--' . $e['status'], 'pio-ev--' . $context ];
if ( 'archive' === $context ) {
	$classes[] = 'pio'; // TEC markup around us is not inside a .pio wrapper.
}
if ( ! empty( $mhide ) ) {
	$classes[] = 'pio-mhide';
}
if ( $e['featured'] ) {
	$classes[] = 'pio-ev--featured';
}
if ( 0 === $e['diff_days'] && 'past' !== $e['status'] ) {
	$classes[] = 'pio-ev--today';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="pio-ev-<?php echo (int) $e['id']; ?>" data-day="<?php echo esc_attr( $e['ymd'] ); ?>" style="--i: <?php echo (int) $i; ?>;">
	<a class="pio-ev__link" href="<?php echo esc_url( $e['url'] ); ?>">
		<div class="pio-ev__date" aria-hidden="true">
			<span class="pio-ev__wd"><?php echo esc_html( $e['weekday_s'] ); ?></span>
			<span class="pio-ev__day"><?php echo esc_html( $e['day'] ); ?><?php if ( $e['multi'] ) : ?><small>–<?php echo esc_html( $e['end_day'] ); ?></small><?php endif; ?></span>
			<span class="pio-ev__mon"><?php echo esc_html( $e['month'] ); ?></span>
		</div>
		<div class="pio-ev__body">
			<p class="pio-ev__rel">
				<span class="pio-rel pio-rel--<?php echo esc_attr( $e['status'] ); ?>"><?php echo esc_html( $e['relative'] ); ?></span>
				<?php if ( $e['featured'] ) : ?><span class="pio-flag">Polecamy</span><?php endif; ?>
				<?php if ( ! empty( $e['category']['name'] ) ) : ?><span class="pio-ev__cat"><?php echo esc_html( $e['category']['name'] ); ?></span><?php endif; ?>
			</p>
			<h4 class="pio-ev__title"><span><?php echo esc_html( $e['title'] ); ?></span></h4>
			<p class="pio-ev__facts">
				<span class="screen-reader-text"><?php echo esc_html( $e['when'] ); ?>, </span>
				<span><?php echo piodesign_icon( 'clock' ); // phpcs:ignore ?><?php echo esc_html( $e['time'] ); ?></span>
				<?php if ( $e['venue']['name'] ) : ?>
					<span><?php echo piodesign_icon( 'pin' ); // phpcs:ignore ?><?php echo esc_html( $e['venue']['name'] ); ?></span>
				<?php endif; ?>
			</p>
			<?php if ( 'archive' === $context && $e['excerpt'] ) : ?>
				<p class="pio-ev__excerpt"><?php echo esc_html( $e['excerpt'] ); ?></p>
			<?php endif; ?>
			<?php if ( 'ongoing' === $e['status'] && $e['multi'] ) : ?>
				<div class="pio-progress" style="--p: <?php echo esc_attr( round( $e['progress'], 3 ) ); ?>;" role="img" aria-label="<?php echo esc_attr( $e['day_of'] ); ?>"><i></i><span><?php echo esc_html( $e['day_of'] ); ?></span></div>
			<?php endif; ?>
		</div>
		<?php
		echo piodesign_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'partials/frame',
			[
				'img'   => $e['image'],
				'sizes' => 'archive' === $context ? '(max-width: 700px) 100vw, 320px' : '(max-width: 700px) 100vw, 200px',
				'class' => 'pio-ev__media',
			]
		);
		?>
	</a>
</article>
