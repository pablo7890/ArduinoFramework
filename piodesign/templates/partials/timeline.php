<?php
/**
 * Five-week strip. Multi-day events are bars, single days are dots.
 *
 * @var array $tl piodesign_timeline().
 */

$cols     = count( $tl['days'] );
$dots_row = $tl['lanes'] + 2;
?>
<div class="pio-tl" role="group" aria-label="Oś wydarzeń: <?php echo esc_attr( $tl['label'] ); ?>">
	<div class="pio-tl__scroll" tabindex="0">
		<div class="pio-tl__grid" style="--cols: <?php echo (int) $cols; ?>; --lanes: <?php echo (int) $tl['lanes']; ?>;">
			<?php foreach ( $tl['days'] as $n => $d ) : ?>
				<div class="pio-tl__day<?php echo $d['today'] ? ' is-today' : ''; ?><?php echo $d['past'] ? ' is-past' : ''; ?><?php echo $d['sunday'] ? ' is-sunday' : ''; ?>"
					style="grid-column: <?php echo (int) $n + 1; ?>; grid-row: 1 / span <?php echo (int) $dots_row; ?>;"
					data-day="<?php echo esc_attr( $d['ymd'] ); ?>">
					<?php if ( $d['month'] ) : ?><span class="pio-tl__month"><?php echo esc_html( $d['month'] ); ?></span><?php endif; ?>
					<span class="pio-tl__wd"><?php echo esc_html( $d['wd'] ); ?></span>
					<span class="pio-tl__num"><?php echo esc_html( $d['day'] ); ?></span>
				</div>
			<?php endforeach; ?>

			<?php foreach ( $tl['bars'] as $k => $b ) : ?>
				<a class="pio-tl__bar<?php echo 'ongoing' === $b['ev']['status'] ? ' is-ongoing' : ''; ?><?php echo $b['cut_l'] ? ' cut-l' : ''; ?><?php echo $b['cut_r'] ? ' cut-r' : ''; ?>"
					href="#pio-ev-<?php echo (int) $b['ev']['id']; ?>" data-href="<?php echo esc_url( $b['ev']['url'] ); ?>"
					style="grid-column: <?php echo (int) $b['from']; ?> / <?php echo (int) $b['to']; ?>; grid-row: <?php echo (int) $b['lane'] + 2; ?>; --k: <?php echo (int) $k; ?>;">
					<span><?php echo esc_html( $b['ev']['title'] ); ?></span>
				</a>
			<?php endforeach; ?>

			<?php foreach ( $tl['days'] as $n => $d ) : ?>
				<?php if ( $d['dots'] ) : ?>
					<div class="pio-tl__dots" style="grid-column: <?php echo (int) $n + 1; ?>; grid-row: <?php echo (int) $dots_row; ?>;">
						<?php foreach ( $d['dots'] as $ev ) : ?>
							<a class="pio-tl__dot<?php echo 'past' === $ev['status'] ? ' is-past' : ''; ?>" href="#pio-ev-<?php echo (int) $ev['id']; ?>" data-href="<?php echo esc_url( $ev['url'] ); ?>" data-tip="<?php echo esc_attr( $ev['time'] . ' · ' . $ev['title'] ); ?>">
								<span class="screen-reader-text"><?php echo esc_html( $ev['when'] . ', ' . $ev['time'] . ': ' . $ev['title'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<p class="pio-tl__legend">
		<span><i class="pio-tl__key pio-tl__key--dot"></i>wydarzenie</span>
		<span><i class="pio-tl__key pio-tl__key--bar"></i>kilka dni</span>
		<span><i class="pio-tl__key pio-tl__key--today"></i>dziś</span>
	</p>
</div>
