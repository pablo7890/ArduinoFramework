<?php
/**
 * News archive (posts page, category, tag, month, search in posts).
 * Page 1 opens with the newsroom lead; after that stories are grouped by
 * month under large month headings, three to a row.
 *
 * @var array[] $posts       Normalised posts for this page.
 * @var string  $title
 * @var string  $kicker
 * @var string  $description Trusted HTML (term description) or ''.
 * @var array[] $cats        [ name, url, count, color, active ].
 * @var string  $all_url
 * @var bool    $all_active
 * @var int     $page
 * @var int     $pages
 * @var int     $total
 * @var array[] $pager       piodesign_pager().
 * @var array   $search      [ action, value, cat ].
 * @var array|null $term     Category / tag header: type, name, color, count, since, latest, back.
 * @var array   $opts        Card options (excerpt lengths…).
 */

$opts = ( $opts ?? [] ) + piodesign_news_opts_defaults();

$lead   = [];
$rest   = $posts;
if ( 1 === (int) $page && count( $posts ) >= 3 ) {
	$lead = array_slice( $posts, 0, 3 );
	$rest = array_slice( $posts, 3 );
}
$groups = piodesign_group_by_month( $rest );
$i      = 0;
?>
<?php $term = $term ?? null; ?>
<section class="pio pio-news pio-archive<?php echo $term ? ' pio-archive--term' : ''; ?>" aria-labelledby="pio-archive-h"<?php echo $term ? ' style="--cat: ' . esc_attr( $term['color'] ) . ';"' : ''; ?>>
	<?php if ( $term ) : ?>
		<header class="pio-term">
			<nav class="pio-crumbs" aria-label="Okruszki">
				<a href="<?php echo esc_url( $term['back'] ); ?>"><?php echo piodesign_icon( 'arrow-l' ); // phpcs:ignore ?> Aktualności</a>
				<span aria-hidden="true">/</span>
				<span><?php echo esc_html( $term['type'] ); ?></span>
			</nav>
			<div class="pio-term__top">
				<h1 class="pio-term__title" id="pio-archive-h">
					<span class="pio-term__kicker"><i aria-hidden="true"></i><?php echo esc_html( $term['type'] ); ?></span>
					<span class="pio-term__name"><?php echo esc_html( $term['name'] ); ?></span>
				</h1>
				<p class="pio-term__stat">
					<b><?php echo (int) $term['count']; ?></b>
					<span>
						<?php echo esc_html( piodesign_plural( (int) $term['count'], 'wpis', 'wpisy', 'wpisów' ) ); ?>
						<?php if ( $term['since'] ) : ?><small>od <?php echo esc_html( $term['since'] ); ?></small><?php endif; ?>
						<?php if ( $term['latest'] ) : ?><small>ostatni <?php echo esc_html( $term['latest'] ); ?></small><?php endif; ?>
					</span>
				</p>
			</div>
			<?php if ( $description ) : ?>
				<div class="pio-term__desc"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
			<div class="pio-term__tools">
				<?php if ( $cats ) : ?>
					<nav class="pio-mast__bar" aria-label="Kategorie">
						<div class="pio-chips">
							<a class="pio-chip" data-filter="*" href="<?php echo esc_url( $all_url ); ?>">Wszystko</a>
							<?php foreach ( $cats as $c ) : ?>
								<a class="pio-chip<?php echo $c['active'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $c['url'] ); ?>" style="--cat: <?php echo esc_attr( $c['color'] ); ?>;"<?php echo $c['active'] ? ' aria-current="page"' : ''; ?>>
									<?php echo esc_html( $c['name'] ); ?> <b><?php echo (int) $c['count']; ?></b>
								</a>
							<?php endforeach; ?>
						</div>
					</nav>
				<?php endif; ?>
				<form class="pio-search" role="search" method="get" action="<?php echo esc_url( $search['action'] ); ?>">
					<label for="pio-search-q" class="screen-reader-text">Szukaj w tej kategorii</label>
					<input id="pio-search-q" type="search" name="s" value="<?php echo esc_attr( $search['value'] ); ?>" placeholder="<?php echo esc_attr( 'Szukaj: ' . $term['name'] ); ?>">
					<input type="hidden" name="post_type" value="post">
					<?php if ( ! empty( $search['cat'] ) ) : ?><input type="hidden" name="cat" value="<?php echo (int) $search['cat']; ?>"><?php endif; ?>
					<button type="submit" aria-label="Szukaj"><svg class="pio-i" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg></button>
				</form>
			</div>
		</header>
	<?php else : ?>
	<header class="pio-mast">
		<div class="pio-mast__top">
			<h1 class="pio-mast__title" id="pio-archive-h">
				<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
				<span class="pio-mast__word"><?php echo esc_html( $title ); ?></span>
			</h1>
			<div class="pio-mast__day pio-archive__tools">
				<p class="pio-ear__date">
					<?php echo esc_html( $total . ' ' . piodesign_plural( $total, 'wpis', 'wpisy', 'wpisów' ) ); ?>
					<?php if ( $pages > 1 ) : ?> · strona <?php echo (int) $page; ?> z <?php echo (int) $pages; ?><?php endif; ?>
				</p>
				<form class="pio-search" role="search" method="get" action="<?php echo esc_url( $search['action'] ); ?>">
					<label for="pio-search-q" class="screen-reader-text">Szukaj w aktualnościach</label>
					<input id="pio-search-q" type="search" name="s" value="<?php echo esc_attr( $search['value'] ); ?>" placeholder="Szukaj we wpisach">
					<input type="hidden" name="post_type" value="post">
					<button type="submit" aria-label="Szukaj"><svg class="pio-i" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/></svg></button>
				</form>
			</div>
		</div>
		<?php if ( $cats ) : ?>
			<nav class="pio-mast__bar" aria-label="Kategorie">
				<div class="pio-chips">
					<a class="pio-chip<?php echo $all_active ? ' is-active' : ''; ?>" data-filter="*" href="<?php echo esc_url( $all_url ); ?>"<?php echo $all_active ? ' aria-current="page"' : ''; ?>>Wszystko</a>
					<?php foreach ( $cats as $c ) : ?>
						<a class="pio-chip<?php echo $c['active'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $c['url'] ); ?>" style="--cat: <?php echo esc_attr( $c['color'] ); ?>;"<?php echo $c['active'] ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html( $c['name'] ); ?> <b><?php echo (int) $c['count']; ?></b>
						</a>
					<?php endforeach; ?>
				</div>
			</nav>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<div class="pio-archive__desc"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</header>
	<?php endif; ?>

	<?php if ( ! $posts ) : ?>
		<p class="pio-archive__empty">Nie znaleźliśmy tu żadnych wpisów. <a href="<?php echo esc_url( $all_url ); ?>">Wróć do wszystkich aktualności</a>.</p>
	<?php endif; ?>

	<?php if ( $lead ) : ?>
		<div class="pio-newsroom pio-archive__lead">
			<?php
			foreach ( $lead as $k => $p ) {
				$variant = 0 === $k ? 'lead' : 'side';
				echo piodesign_render( 'partials/post-card', [ 'p' => $p, 'variant' => $variant, 'i' => $i++, 'opts' => $opts ] ); // phpcs:ignore
			}
			?>
		</div>
	<?php endif; ?>

	<?php foreach ( $groups as $g ) : ?>
		<div class="pio-archive__month">
			<?php echo piodesign_render( 'partials/month-head', [ 'month' => $g['month'], 'year' => $g['year'] ] ); // phpcs:ignore ?>
			<div class="pio-newsroom pio-archive__grid">
				<?php
				foreach ( $g['posts'] as $p ) {
					echo piodesign_render( 'partials/post-card', [ 'p' => $p, 'variant' => 'card', 'i' => $i++, 'opts' => $opts ] ); // phpcs:ignore
				}
				?>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ( $pager ) : ?>
		<nav class="pio-pager" aria-label="Strony archiwum">
			<?php foreach ( $pager as $it ) : ?>
				<?php if ( 'gap' === $it['type'] ) : ?>
					<span class="pio-pager__gap" aria-hidden="true">…</span>
				<?php elseif ( 'prev' === $it['type'] || 'next' === $it['type'] ) : ?>
					<?php
					$label = 'prev' === $it['type'] ? 'Nowsze' : 'Starsze';
					$icon  = piodesign_icon( 'prev' === $it['type'] ? 'arrow-l' : 'arrow' );
					$inner = 'prev' === $it['type'] ? $icon . '<span>' . $label . '</span>' : '<span>' . $label . '</span>' . $icon;
					?>
					<?php if ( $it['url'] ) : ?>
						<a class="pio-pager__step pio-pager__step--<?php echo esc_attr( $it['type'] ); ?>" href="<?php echo esc_url( $it['url'] ); ?>" rel="<?php echo 'prev' === $it['type'] ? 'prev' : 'next'; ?>"><?php echo $inner; // phpcs:ignore ?></a>
					<?php else : ?>
						<span class="pio-pager__step pio-pager__step--<?php echo esc_attr( $it['type'] ); ?> is-disabled" aria-hidden="true"><?php echo $inner; // phpcs:ignore ?></span>
					<?php endif; ?>
				<?php elseif ( $it['current'] ) : ?>
					<span class="pio-pager__num is-current" aria-current="page"><?php echo (int) $it['page']; ?></span>
				<?php else : ?>
					<a class="pio-pager__num" href="<?php echo esc_url( $it['url'] ); ?>"><?php echo (int) $it['page']; ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
</section>
