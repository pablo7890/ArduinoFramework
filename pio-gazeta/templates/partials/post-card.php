<?php
/**
 * One story in the newsroom grid.
 *
 * @var array  $p       Normalised post (pio_gazeta_post()).
 * @var string $variant lead | side | card | brief.
 * @var int    $i       Position, used for the stagger.
 */

$heading = 'lead' === $variant ? 'h3' : 'h4';
$sizes   = [
	'lead'  => '(max-width: 900px) 100vw, 60vw',
	'side'  => '(max-width: 900px) 100vw, 30vw',
	'card'  => '(max-width: 700px) 100vw, (max-width: 1100px) 50vw, 30vw',
	'brief' => '(max-width: 700px) 40vw, 12vw',
][ $variant ];

$badge = '';
if ( $p['photo_label'] && 'brief' !== $variant ) {
	$badge .= '<span class="pio-photos">' . pio_gazeta_icon( 'camera' ) . esc_html( $p['photo_label'] ) . '</span>';
}
if ( $p['gallery'] && 'brief' !== $variant ) {
	$badge .= '<span class="pio-frame__dots" aria-hidden="true">' . str_repeat( '<i></i>', count( $p['gallery'] ) + 1 ) . '</span>';
}
?>
<article class="pio-story pio-story--<?php echo esc_attr( $variant ); ?>"
	style="--cat: <?php echo esc_attr( $p['category']['color'] ); ?>; --i: <?php echo (int) $i; ?>; view-transition-name: pio-post-<?php echo (int) $p['id']; ?>;"
	data-cat="<?php echo esc_attr( $p['category']['slug'] ); ?>">
	<a class="pio-story__link" href="<?php echo esc_url( $p['url'] ); ?>">
		<?php
		echo pio_gazeta_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'partials/frame',
			[
				'img'     => $p['image'],
				'sizes'   => $sizes,
				'eager'   => 'lead' === $variant,
				'gallery' => 'brief' === $variant ? [] : $p['gallery'],
				'badge'   => $badge,
				'class'   => 'pio-story__media',
			]
		);
		?>
		<div class="pio-story__body">
			<p class="pio-meta">
				<span class="pio-cat"><?php echo esc_html( $p['category']['name'] ); ?></span>
				<time datetime="<?php echo esc_attr( $p['iso'] ); ?>" title="<?php echo esc_attr( $p['date_long'] ); ?>"><?php echo esc_html( 'lead' === $variant ? $p['date_long'] : $p['date'] ); ?></time>
				<?php if ( $p['is_new'] ) : ?><span class="pio-new">Nowe</span><?php endif; ?>
			</p>
			<<?php echo $heading; // phpcs:ignore ?> class="pio-story__title"><span><?php echo esc_html( $p['title'] ); ?></span></<?php echo $heading; // phpcs:ignore ?>>
			<?php if ( 'brief' !== $variant ) : ?>
				<p class="pio-story__excerpt"><?php echo esc_html( $p['excerpt'] ); ?></p>
			<?php endif; ?>
			<?php if ( 'lead' === $variant ) : ?>
				<span class="pio-more">Czytaj dalej <?php echo pio_gazeta_icon( 'arrow' ); // phpcs:ignore ?></span>
			<?php endif; ?>
		</div>
	</a>
</article>
