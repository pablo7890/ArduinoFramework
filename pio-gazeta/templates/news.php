<?php
/**
 * Homepage newsroom: lead story, two seconds, a grid and a column of briefs.
 *
 * @var array  $posts       Normalised posts.
 * @var array  $liturgy     pio_gazeta_liturgy().
 * @var string $today       "wtorek, 29 września 2026".
 * @var string $archive_url
 * @var string $title
 * @var string $kicker
 */

if ( ! $posts ) {
	return;
}

$cats = [];
foreach ( $posts as $p ) {
	$slug = $p['category']['slug'];
	if ( ! isset( $cats[ $slug ] ) ) {
		$cats[ $slug ] = $p['category'] + [ 'count' => 0 ];
	}
	$cats[ $slug ]['count']++;
}
uasort( $cats, static fn( $a, $b ) => $b['count'] <=> $a['count'] );

$uid = 'pio-news-' . substr( md5( serialize( array_column( $posts, 'id' ) ) ), 0, 6 );
?>
<section class="pio pio-news" id="<?php echo esc_attr( $uid ); ?>" style="--lit: <?php echo esc_attr( $liturgy['color'] ); ?>;" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h">
	<header class="pio-mast">
		<div class="pio-mast__row">
			<p class="pio-dateline">
				<span><?php echo esc_html( $today ); ?></span>
				<span class="pio-lit" title="Kolor liturgiczny: <?php echo esc_attr( $liturgy['color_name'] ); ?>">
					<i class="pio-lit__dot" aria-hidden="true"></i>
					<?php echo esc_html( trim( $liturgy['label'] . ( $liturgy['week'] ? ', ' . $liturgy['week'] : '' ) ) ); ?>
				</span>
			</p>
			<a class="pio-btn pio-btn--ghost pio-mast__all" href="<?php echo esc_url( $archive_url ); ?>">Wszystkie wpisy <?php echo pio_gazeta_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<h2 class="pio-mast__title" id="<?php echo esc_attr( $uid ); ?>-h">
			<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
			<span class="pio-mast__word"><?php echo esc_html( $title ); ?></span>
		</h2>
		<?php if ( count( $cats ) > 1 ) : ?>
			<div class="pio-chips" role="toolbar" aria-label="Filtruj według kategorii" aria-controls="<?php echo esc_attr( $uid ); ?>-grid">
				<button type="button" class="pio-chip is-active" data-filter="*" aria-pressed="true">Wszystko <b><?php echo count( $posts ); ?></b></button>
				<?php foreach ( $cats as $slug => $c ) : ?>
					<button type="button" class="pio-chip" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false" style="--cat: <?php echo esc_attr( $c['color'] ); ?>;">
						<?php echo esc_html( $c['name'] ); ?> <b><?php echo (int) $c['count']; ?></b>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>

	<div class="pio-newsroom" id="<?php echo esc_attr( $uid ); ?>-grid" aria-live="polite">
		<?php
		foreach ( $posts as $i => $p ) {
			if ( 0 === $i ) {
				$variant = 'lead';
			} elseif ( $i < 3 ) {
				$variant = 'side';
			} elseif ( $i < 9 ) {
				$variant = 'card';
			} else {
				$variant = 'brief';
			}
			if ( 9 === $i ) {
				echo '<h3 class="pio-briefs-h"><span>Z ostatnich tygodni</span></h3>';
			}
			echo pio_gazeta_render( 'partials/post-card', compact( 'p', 'variant', 'i' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</div>

	<footer class="pio-news__foot">
		<a class="pio-btn" href="<?php echo esc_url( $archive_url ); ?>">Starsze aktualności <?php echo pio_gazeta_icon( 'arrow' ); // phpcs:ignore ?></a>
	</footer>
</section>
