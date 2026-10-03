<?php
/**
 * [pio_liturgia]: today's liturgical day as a card edged with a stole in
 * the day's colour, and a strip of the coming days that switches the card.
 *
 * @var array[] $days   piodesign_liturgy_day() for today and the next days.
 * @var string  $title
 * @var string  $kicker
 * @var array|null $podcast piodesign_podcast(): today's Gospel reflection.
 */

$podcast = $podcast ?? null;

if ( ! $days ) {
	return;
}
$uid = 'pio-lit-' . substr( md5( $days[0]['iso'] ), 0, 6 );
?>
<section class="pio pio-liturgy" id="<?php echo esc_attr( $uid ); ?>" data-pio-liturgy aria-labelledby="<?php echo esc_attr( $uid ); ?>-h" style="--lit: <?php echo esc_attr( $days[0]['color'] ); ?>;">
	<header class="pio-sec-head">
		<h2 class="pio-sec-head__title" id="<?php echo esc_attr( $uid ); ?>-h">
			<span class="pio-mast__kicker"><?php echo esc_html( $kicker ); ?></span>
			<span class="pio-sec-head__word"><?php echo esc_html( $title ); ?></span>
		</h2>
	</header>

	<div class="pio-liturgy__card">
		<div class="pio-liturgy__stole" aria-hidden="true"></div>
		<div class="pio-liturgy__panels">
			<?php foreach ( $days as $k => $d ) : ?>
				<?php $light = piodesign_is_light( $d['color'] ); ?>
				<article class="pio-liturgy__panel<?php echo 0 === $k ? ' is-active' : ''; ?><?php echo 0 === $k && $podcast ? ' has-pod' : ''; ?>" id="<?php echo esc_attr( $uid . '-p' . $k ); ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $uid . '-t' . $k ); ?>" data-color="<?php echo esc_attr( $d['color'] ); ?>"<?php echo 0 === $k ? '' : ' hidden'; ?>>
					<?php
					$has_pod = 0 === $k && $podcast;
					$notes   = [];
					foreach ( [ 'parish' => 'Święto parafialne', 'optional' => 'Wspomnienie dowolne', 'occasional' => 'Okolicznościowe' ] as $key => $label ) {
						foreach ( $d[ $key ] as $x ) {
							$notes[] = [ $key, $label, $x ];
						}
					}
					$gospel = '';
					foreach ( $d['readings'] as $r ) {
						if ( 'Ewangelia' === $r['label'] ) {
							$gospel = $r['sigla'];
						}
					}
					?>
					<div class="pio-liturgy__main">
						<div class="pio-liturgy__top">
							<p class="pio-liturgy__date">
								<span class="pio-liturgy__wd"><?php echo esc_html( $d['today'] ? 'Dziś, ' . $d['weekday'] : $d['weekday'] ); ?></span>
								<time datetime="<?php echo esc_attr( $d['iso'] ); ?>"><?php echo esc_html( $d['date'] ); ?></time>
							</p>
							<h3 class="pio-liturgy__title"><?php echo esc_html( $d['title'] ); ?></h3>
							<p class="pio-liturgy__meta">
								<?php if ( $d['rank'] ) : ?><span class="pio-liturgy__rank"><?php echo esc_html( $d['rank'] ); ?></span><?php endif; ?>
								<span class="pio-liturgy__color<?php echo $light ? ' is-light' : ''; ?>" style="--c: <?php echo esc_attr( $d['color'] ); ?>;"><i aria-hidden="true"></i>kolor <?php echo esc_html( $d['color_name'] ?: 'liturgiczny' ); ?></span>
							</p>
							<?php if ( $notes ) : ?>
								<ul class="pio-liturgy__notes">
									<?php foreach ( $notes as $n ) : ?>
										<li class="is-<?php echo esc_attr( $n[0] ); ?>"><span><?php echo esc_html( $n[1] ); ?></span><?php echo esc_html( $n[2] ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
						<?php if ( $d['readings'] ) : ?>
							<dl class="pio-readings">
								<?php foreach ( $d['readings'] as $r ) : ?>
									<div class="pio-readings__item<?php echo 'Ewangelia' === $r['label'] ? ' is-gospel' : ''; ?>">
										<dt><?php echo esc_html( $r['label'] ); ?></dt>
										<dd><?php echo esc_html( $r['sigla'] ); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						<?php elseif ( 'fallback' === $d['source'] ) : ?>
							<p class="pio-liturgy__empty">Szczegóły dnia pojawią się po zatwierdzeniu kalendarza liturgicznego na ten rok.</p>
						<?php endif; ?>
					</div>
					<?php if ( $has_pod ) : ?>
						<aside class="pio-pod" aria-label="<?php echo esc_attr( $podcast['label'] ?: 'Ewangelia na dziś' ); ?>">
							<div class="pio-pod__head">
								<p class="pio-pod__label"><?php echo piodesign_icon( 'headphones' ); // phpcs:ignore ?><?php echo esc_html( $podcast['label'] ?: 'Ewangelia na dziś' ); ?></p>
								<?php if ( $gospel ) : ?>
									<p class="pio-pod__sigla"><?php echo esc_html( $gospel ); ?></p>
								<?php endif; ?>
								<p class="pio-pod__sub">Posłuchaj krótkiego rozważania czytań z dzisiejszej Mszy świętej.</p>
							</div>
							<?php if ( ! empty( $podcast['embed'] ) ) : ?>
								<div class="pio-pod__player">
									<iframe src="<?php echo esc_url( $podcast['embed'] ); ?>" title="<?php echo esc_attr( 'Odtwarzacz: ' . ( $podcast['title'] ?: $podcast['label'] ) ); ?>" height="152" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
								</div>
							<?php else : ?>
								<a class="pio-pod__link" href="<?php echo esc_url( $podcast['url'] ); ?>" target="_blank" rel="noopener">
									<span class="pio-pod__play" aria-hidden="true"></span>
									<span class="pio-pod__ep"><?php echo esc_html( $podcast['title'] ?: 'Rozważanie Ewangelii' ); ?><small>Posłuchaj w Spotify</small></span>
								</a>
							<?php endif; ?>
						</aside>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( count( $days ) > 1 ) : ?>
		<div class="pio-liturgy__week" role="tablist" aria-label="Kolejne dni">
			<?php foreach ( $days as $k => $d ) : ?>
				<button type="button" role="tab" class="pio-lday<?php echo 0 === $k ? ' is-active' : ''; ?><?php echo $d['sunday'] ? ' is-sunday' : ''; ?>" id="<?php echo esc_attr( $uid . '-t' . $k ); ?>" aria-controls="<?php echo esc_attr( $uid . '-p' . $k ); ?>" aria-selected="<?php echo 0 === $k ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $k ? '0' : '-1'; ?>" data-lday="<?php echo (int) $k; ?>" style="--c: <?php echo esc_attr( $d['color'] ); ?>;">
					<span class="pio-lday__wd"><?php echo esc_html( $d['today'] ? 'dziś' : $d['weekday_s'] ); ?></span>
					<span class="pio-lday__num"><?php echo esc_html( $d['day'] ); ?></span>
					<i class="pio-lday__dot" aria-hidden="true"></i>
					<span class="pio-lday__t"><?php echo esc_html( $d['title'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
