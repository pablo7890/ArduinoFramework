<?php
/**
 * Settings → PioDesign: news archive, parish information, quotes.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

function piodesign_settings_defaults() {
	return [
		'news_archive'     => 1,
		'archive_page'     => 0, // 0 = page with the slug "aktualnosci", if any.
		'archive_per_page' => 18,
	] + piodesign_info_defaults();
}

function piodesign_option( $key ) {
	static $opts = null;
	if ( null === $opts ) {
		$opts = wp_parse_args( (array) get_option( 'piodesign_settings', [] ), piodesign_settings_defaults() );
	}
	return $opts[ $key ] ?? null;
}

/** All settings merged with defaults (for the sections). */
function piodesign_options() {
	return wp_parse_args( (array) get_option( 'piodesign_settings', [] ), piodesign_settings_defaults() );
}

/** The page that shows the news archive (setting, else the "aktualnosci" page). */
function piodesign_archive_page_id() {
	$id = (int) piodesign_option( 'archive_page' );
	if ( $id && 'page' === get_post_type( $id ) ) {
		return $id;
	}
	$page = get_page_by_path( 'aktualnosci' );
	return $page ? (int) $page->ID : 0;
}

function piodesign_archive_url() {
	$page = piodesign_archive_page_id();
	if ( $page ) {
		return get_permalink( $page );
	}
	$posts_page = (int) get_option( 'page_for_posts' );
	return $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
}

add_action(
	'admin_menu',
	static function () {
		add_options_page( 'PioDesign', 'PioDesign', 'manage_options', 'piodesign', 'piodesign_settings_page' );
	}
);

add_action(
	'admin_init',
	static function () {
		register_setting(
			'piodesign',
			'piodesign_settings',
			[
				'type'              => 'array',
				'sanitize_callback' => 'piodesign_sanitize_settings',
			]
		);
	}
);

function piodesign_sanitize_settings( $in ) {
	$in  = (array) $in;
	$def = piodesign_settings_defaults();
	$out = [
		'news_archive'        => empty( $in['news_archive'] ) ? 0 : 1,
		'archive_page'        => max( 0, (int) ( $in['archive_page'] ?? 0 ) ),
		'archive_per_page'    => max( 6, min( 48, (int) ( $in['archive_per_page'] ?? 18 ) ) ),
		'kancelaria_i_piatek' => empty( $in['kancelaria_i_piatek'] ) ? 0 : 1,
	];
	foreach ( [ 'msze_niedziela', 'msze_powszednie', 'msze_sobota', 'kancelaria', 'adres', 'partnerzy', 'cytaty' ] as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? sanitize_textarea_field( $in[ $k ] ) : $def[ $k ];
	}
	foreach ( [ 'kancelaria_uwagi', 'nazwa', 'telefon', 'konto', 'mapa' ] as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : $def[ $k ];
	}
	$out['email']    = isset( $in['email'] ) ? sanitize_email( $in['email'] ) : $def['email'];
	$out['facebook'] = isset( $in['facebook'] ) ? esc_url_raw( $in['facebook'] ) : $def['facebook'];
	$out['youtube']  = isset( $in['youtube'] ) ? esc_url_raw( $in['youtube'] ) : $def['youtube'];
	return $out;
}

add_filter(
	'plugin_action_links_' . plugin_basename( PIODESIGN_DIR . 'piodesign.php' ),
	static function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=piodesign' ) ) . '">Ustawienia</a>' );
		return $links;
	}
);

/* Pages get an excerpt box, used as the text of [pio_sakramenty] slides. */
add_action(
	'init',
	static function () {
		add_post_type_support( 'page', 'excerpt' );
	}
);

function piodesign_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o    = piodesign_options();
	$name = static fn( $k ) => 'piodesign_settings[' . $k . ']';
	$ta   = static function ( $k, $rows, $help ) use ( $o, $name ) {
		printf(
			'<textarea id="pd-%1$s" name="%2$s" rows="%3$d" class="large-text code">%4$s</textarea><p class="description">%5$s</p>',
			esc_attr( $k ),
			esc_attr( $name( $k ) ),
			(int) $rows,
			esc_textarea( $o[ $k ] ),
			wp_kses( $help, [ 'code' => [], 'br' => [], 'strong' => [] ] )
		);
	};
	$tx = static function ( $k, $help = '', $type = 'text' ) use ( $o, $name ) {
		printf(
			'<input id="pd-%1$s" type="%2$s" name="%3$s" value="%4$s" class="regular-text">%5$s',
			esc_attr( $k ),
			esc_attr( $type ),
			esc_attr( $name( $k ) ),
			esc_attr( $o[ $k ] ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
		);
	};
	?>
	<div class="wrap">
		<h1>Parafia: PioDesign</h1>
		<p>Shortcode'y: <code>[pio_aktualnosci]</code> <code>[pio_wydarzenia]</code> <code>[pio_sakramenty]</code> <code>[pio_cytaty]</code> <code>[pio_liturgia]</code> <code>[pio_informacje]</code> <code>[pio_archiwum]</code>. Kroje pisma pochodzą z <em>Avada → Options → Typography</em>.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'piodesign' ); ?>

			<h2 class="title">Archiwum aktualności</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Nowy wygląd archiwum</th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $name( 'news_archive' ) ); ?>" value="1" <?php checked( $o['news_archive'] ); ?>> Strona aktualności, kategorie, tagi, archiwa miesięczne i wyszukiwanie we wpisach</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pd-archive_page">Strona „Aktualności”</label></th>
					<td>
						<?php
						wp_dropdown_pages(
							[
								'name'              => esc_attr( $name( 'archive_page' ) ),
								'id'                => 'pd-archive_page',
								'selected'          => (int) $o['archive_page'],
								'show_option_none'  => '— wykryj automatycznie (strona /aktualnosci/) —',
								'option_none_value' => '0',
							]
						);
						?>
						<p class="description">Na tej stronie wtyczka pokaże archiwum zamiast treści zbudowanej w Avadzie. Teraz: <?php echo piodesign_archive_page_id() ? '<a href="' . esc_url( get_permalink( piodesign_archive_page_id() ) ) . '">' . esc_html( get_the_title( piodesign_archive_page_id() ) ) . '</a>' : 'brak strony'; ?>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pd-per-page">Wpisów na stronę</label></th>
					<td><input id="pd-per-page" type="number" min="6" max="48" step="3" name="<?php echo esc_attr( $name( 'archive_per_page' ) ); ?>" value="<?php echo (int) $o['archive_per_page']; ?>" class="small-text"> <span class="description">najlepiej wielokrotność 3</span></td>
				</tr>
			</table>

			<h2 class="title">Porządek Mszy świętych <small>(<code>[pio_informacje]</code>)</small></h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="pd-msze_niedziela">Niedziele i święta</label></th><td><?php $ta( 'msze_niedziela', 6, 'Jedna Msza w wierszu: <code>godzina | uwaga</code>, np. <code>12:30 | suma parafialna</code>.' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-msze_powszednie">Poniedziałek – piątek</label></th><td><?php $ta( 'msze_powszednie', 4, 'Uwaga „w adwencie 6:30” sprawia, że w Adwencie licznik „najbliższa Msza” liczy od 6:30.' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-msze_sobota">Sobota</label></th><td><?php $ta( 'msze_sobota', 3, '' ); ?></td></tr>
			</table>

			<h2 class="title">Kancelaria</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="pd-kancelaria">Godziny</label></th><td><?php $ta( 'kancelaria', 4, '<code>dzień | od–do</code>, np. <code>środa | 9:00–10:00</code>.' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-kancelaria_uwagi">Uwagi</label></th><td><?php $tx( 'kancelaria_uwagi' ); ?></td></tr>
				<tr><th scope="row">I piątek miesiąca</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name( 'kancelaria_i_piatek' ) ); ?>" value="1" <?php checked( $o['kancelaria_i_piatek'] ); ?>> Kancelaria nieczynna w I piątek miesiąca (status „otwarte / zamknięte” to uwzględnia)</label></td></tr>
			</table>

			<h2 class="title">Kontakt i wsparcie</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="pd-nazwa">Nazwa parafii</label></th><td><?php $tx( 'nazwa' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-adres">Adres</label></th><td><?php $ta( 'adres', 2, '' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-telefon">Telefon</label></th><td><?php $tx( 'telefon' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-email">E-mail</label></th><td><?php $tx( 'email', '', 'email' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-konto">Numer konta</label></th><td><?php $tx( 'konto' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-mapa">Mapa – adres do wyszukania</label></th><td><?php $tx( 'mapa', 'Mapa Google ładuje się dopiero po kliknięciu (bez ciasteczek Google przy wejściu na stronę).' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-partnerzy">Strony zaprzyjaźnione</label></th><td><?php $ta( 'partnerzy', 3, '<code>nazwa | adres strony | krótki opis</code>' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-facebook">Facebook</label></th><td><?php $tx( 'facebook', '', 'url' ); ?></td></tr>
				<tr><th scope="row"><label for="pd-youtube">YouTube</label></th><td><?php $tx( 'youtube', '', 'url' ); ?></td></tr>
			</table>

			<h2 class="title">Cytaty św. Ojca Pio <small>(<code>[pio_cytaty]</code>)</small></h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="pd-cytaty">Cytaty</label></th><td><?php $ta( 'cytaty', 8, 'Jeden cytat w wierszu, bez cudzysłowów.' ); ?></td></tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
