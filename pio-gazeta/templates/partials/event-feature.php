<?php
/**
 * "Next up" card with a live countdown.
 *
 * @var array $e
 */
?>
<article class="pio-next" id="pio-ev-<?php echo (int) $e['id']; ?>" data-day="<?php echo esc_attr( $e['ymd'] ); ?>">
	<a class="pio-next__link" href="<?php echo esc_url( $e['url'] ); ?>">
		<?php
		echo pio_gazeta_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'partials/frame',
			[
				'img'   => $e['image'],
				'sizes' => '(max-width: 900px) 100vw, 40vw',
				'class' => 'pio-next__media',
			]
		);
		?>
		<div class="pio-next__body">
			<p class="pio-next__label">Najbliżej <span class="pio-rel pio-rel--upcoming"><?php echo esc_html( $e['relative'] ); ?></span></p>
			<h3 class="pio-next__title"><span><?php echo esc_html( $e['title'] ); ?></span></h3>
			<p class="pio-ev__facts">
				<span><?php echo pio_gazeta_icon( 'calendar' ); // phpcs:ignore ?><?php echo esc_html( $e['when'] ); ?></span>
				<span><?php echo pio_gazeta_icon( 'clock' ); // phpcs:ignore ?><?php echo esc_html( $e['time'] ); ?></span>
				<?php if ( $e['venue']['name'] ) : ?>
					<span><?php echo pio_gazeta_icon( 'pin' ); // phpcs:ignore ?><?php echo esc_html( $e['venue']['name'] ); ?></span>
				<?php endif; ?>
			</p>
			<?php echo pio_gazeta_render( 'partials/countdown', [ 'e' => $e ] ); // phpcs:ignore ?>
		</div>
	</a>
</article>
