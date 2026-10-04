<?php
/**
 * Single news post: headline block, 16:9 hero, a sticky rail (date, reading
 * time, photos, share) beside the text, then prev / next and related stories.
 *
 * @var array      $p        Normalised post (piodesign_post()).
 * @var string     $content  Filtered post content (trusted HTML).
 * @var string     $lede     Hand-written excerpt or ''.
 * @var string     $cat_url
 * @var array|null $prev
 * @var array|null $next
 * @var array[]    $related  Normalised posts.
 * @var array[]    $tags     [ name, url ].
 * @var string     $back_url
 * @var bool       $share
 * @var array      $image    Full-size image data or [].
 */

$date = new DateTimeImmutable( $p['iso'] );
$url  = $p['url'];
$hero = $image ? piodesign_frame( $image, 'post' ) : null;
?>
<article class="pio pio-post<?php echo 'site' === piodesign_option( 'single_width' ) ? ' pio-w-site' : ''; ?>" style="--cat: <?php echo esc_attr( $p['category']['color'] ); ?>;">
	<nav class="pio-crumbs" aria-label="Okruszki">
		<a href="<?php echo esc_url( $back_url ); ?>"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?> Aktualności</a>
		<?php if ( $cat_url ) : ?>
			<span aria-hidden="true">/</span>
			<a class="pio-post__cat" href="<?php echo esc_url( $cat_url ); ?>"><?php echo esc_html( $p['category']['name'] ); ?></a>
		<?php endif; ?>
	</nav>

	<header class="pio-post__head">
		<p class="pio-meta">
			<span class="pio-cat"><?php echo esc_html( $p['category']['name'] ); ?></span>
			<time datetime="<?php echo esc_attr( $p['iso'] ); ?>"><?php echo esc_html( $p['date_long'] ); ?></time>
			<?php if ( $p['relative'] ) : ?><span><?php echo esc_html( $p['relative'] ); ?></span><?php endif; ?>
		</p>
		<h1 class="pio-post__title"><?php echo esc_html( $p['title'] ); ?></h1>
		<?php if ( $lede ) : ?><p class="pio-post__lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
	</header>

	<?php if ( $hero ) : ?>
		<?php
		echo piodesign_render( // phpcs:ignore WordPress.Security.EscapeOutput
			'partials/frame',
			[
				'img'   => $hero,
				'sizes' => '(max-width: 1200px) 100vw, 1200px',
				'eager' => true,
				'class' => 'pio-post__hero',
				'badge' => $p['photo_label'] ? '<span class="pio-photos">' . piodesign_icon( 'camera' ) . esc_html( $p['photo_label'] ) . '</span>' : '',
			]
		);
		?>
	<?php endif; ?>

	<div class="pio-post__grid">
		<aside class="pio-post__rail" aria-label="O wpisie">
			<div class="pio-post__datetile" aria-hidden="true">
				<span class="pio-post__day"><?php echo esc_html( $date->format( 'j' ) ); ?></span>
				<span class="pio-post__month"><?php echo esc_html( piodesign_months()[ (int) $date->format( 'n' ) ] . ' ' . $date->format( 'Y' ) ); ?></span>
			</div>
			<ul class="pio-post__facts">
				<li><?php echo piodesign_icon( 'clock' ); // phpcs:ignore ?><?php echo esc_html( $p['read_min'] . ' min czytania' ); ?></li>
				<?php if ( $p['photo_label'] ) : ?><li><?php echo piodesign_icon( 'camera' ); // phpcs:ignore ?><?php echo esc_html( $p['photo_label'] ); ?></li><?php endif; ?>
			</ul>
			<?php if ( $share ) : ?>
				<div class="pio-post__share">
					<p class="pio-post__label">Udostępnij</p>
					<a class="pio-share" href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ); ?>" target="_blank" rel="noopener"><?php echo piodesign_icon( 'facebook' ); // phpcs:ignore ?><span>Facebook</span></a>
					<button type="button" class="pio-share pio-copy" data-copy="<?php echo esc_url( $url ); ?>" data-done="Skopiowano link"><?php echo piodesign_icon( 'link' ); // phpcs:ignore ?><span>Kopiuj link</span></button>
				</div>
			<?php endif; ?>
		</aside>

		<div class="pio-prose pio-post__body">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $tags ) : ?>
				<p class="pio-post__tags">
					<?php foreach ( $tags as $t ) : ?><a href="<?php echo esc_url( $t['url'] ); ?>">#<?php echo esc_html( $t['name'] ); ?></a><?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $prev || $next ) : ?>
		<nav class="pio-pn" aria-label="Sąsiednie wpisy">
			<?php if ( $prev ) : ?>
				<a class="pio-pn__prev" href="<?php echo esc_url( $prev['url'] ); ?>"><small><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?> Starszy wpis · <?php echo esc_html( $prev['date'] ); ?></small><span><?php echo esc_html( $prev['title'] ); ?></span></a>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<a class="pio-pn__next" href="<?php echo esc_url( $next['url'] ); ?>"><small>Nowszy wpis · <?php echo esc_html( $next['date'] ); ?> <?php echo piodesign_icon( 'arrow' ); // phpcs:ignore ?></small><span><?php echo esc_html( $next['title'] ); ?></span></a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

	<?php if ( $related ) : ?>
		<section class="pio-post__related" aria-labelledby="pio-related-h">
			<h2 class="pio-group-h" id="pio-related-h">Czytaj także</h2>
			<div class="pio-newsroom pio-archive__grid">
				<?php
				foreach ( $related as $k => $r ) {
					echo piodesign_render( 'partials/post-card', [ 'p' => $r, 'variant' => 'card', 'i' => $k, 'opts' => [ 'excerpt_card' => 120 ] + piodesign_news_opts_defaults() ] ); // phpcs:ignore
				}
				?>
			</div>
		</section>
	<?php endif; ?>
</article>
