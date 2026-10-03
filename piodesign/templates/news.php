<?php
/**
 * Homepage newsroom: lead story, two seconds, a grid and a column of briefs.
 *
 * @var array  $posts       Normalised posts.
 * @var array  $day         piodesign_day().
 * @var int    $mobile      Stories shown on phones before the archive link.
 * @var array  $opts        Display options (excerpt lengths, badges…), see piodesign_news_opts().
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
<section class="pio pio-news" id="<?php echo esc_attr( $uid ); ?>" aria-labelledby="<?php echo esc_attr( $uid ); ?>-h">
	<header class="pio-mast">
		<div class="pio-mast__top">
			<h2 class="pio-mast__title" id="<?php echo esc_attr( $uid ); ?>-h">
				<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
				<span class="pio-mast__word"><?php echo esc_html( $title ); ?></span>
			</h2>
			<?php if ( ! empty( $day ) && ! empty( $opts['show_day'] ) ) : ?>
				<div class="pio-mast__day"><?php echo piodesign_render( 'partials/dayline', [ 'day' => $day ] ); // phpcs:ignore ?></div>
			<?php endif; ?>
		</div>
		<?php if ( count( $cats ) > 1 && ! empty( $opts['show_chips'] ) ) : ?>
			<div class="pio-mast__bar">
				<div class="pio-chips" role="toolbar" aria-label="Filtruj według kategorii" aria-controls="<?php echo esc_attr( $uid ); ?>-grid">
					<button type="button" class="pio-chip is-active" data-filter="*" aria-pressed="true">Wszystko <b><?php echo count( $posts ); ?></b></button>
					<?php foreach ( $cats as $slug => $c ) : ?>
						<button type="button" class="pio-chip" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false" style="--cat: <?php echo esc_attr( $c['color'] ); ?>;">
							<?php echo esc_html( $c['name'] ); ?> <b><?php echo (int) $c['count']; ?></b>
						</button>
					<?php endforeach; ?>
				</div>
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
			$mhide = $i >= $mobile;
			if ( 9 === $i ) {
				echo '<h3 class="pio-briefs-h' . ( $mhide ? ' pio-mhide' : '' ) . '"><span>Z ostatnich tygodni</span></h3>';
			}
			echo piodesign_render( 'partials/post-card', compact( 'p', 'variant', 'i', 'mhide', 'opts' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</div>

	<footer class="pio-news__foot">
		<a class="pio-btn pio-news__more" href="<?php echo esc_url( $archive_url ); ?>">
			<span class="pio-wide-only">Starsze aktualności</span>
			<span class="pio-narrow-only">Wszystkie aktualności</span>
			<?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?>
		</a>
	</footer>
</section>
