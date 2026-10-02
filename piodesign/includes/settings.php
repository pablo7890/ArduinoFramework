<?php
/**
 * Settings → PioDesign: site-wide fonts and the news archive.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

function piodesign_option( $key ) {
	$defaults = [
		'site_fonts'       => 0,
		'news_archive'     => 1,
		'archive_per_page' => 18,
	];
	$opts = wp_parse_args( (array) get_option( 'piodesign_settings', [] ), $defaults );
	return $opts[ $key ] ?? null;
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
				'sanitize_callback' => static function ( $in ) {
					$in = (array) $in;
					return [
						'site_fonts'       => empty( $in['site_fonts'] ) ? 0 : 1,
						'news_archive'     => empty( $in['news_archive'] ) ? 0 : 1,
						'archive_per_page' => max( 6, min( 48, (int) ( $in['archive_per_page'] ?? 18 ) ) ),
					];
				},
			]
		);
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( PIODESIGN_DIR . 'piodesign.php' ),
	static function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=piodesign' ) ) . '">Ustawienia</a>' );
		return $links;
	}
);

function piodesign_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Parafia: PioDesign</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'piodesign' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Czcionki na całej stronie</th>
					<td>
						<label>
							<input type="checkbox" name="piodesign_settings[site_fonts]" value="1" <?php checked( piodesign_option( 'site_fonts' ) ); ?>>
							Użyj czcionek PioDesign w całym motywie Avada
						</label>
						<p class="description">
							Nagłówki, menu, przyciski i tytuły: <strong>Bricolage Grotesque</strong>. Treść: <strong>Newsreader</strong>.
							Czcionki są we wtyczce (bez Google Fonts). Nadpisuje kroje z <em>Avada → Options → Typography</em>;
							rozmiary i grubości nadal ustawiasz w Avadzie. Wyłączenie przywraca kroje Avady.
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Archiwum aktualności</th>
					<td>
						<label>
							<input type="checkbox" name="piodesign_settings[news_archive]" value="1" <?php checked( piodesign_option( 'news_archive' ) ); ?>>
							Nowy wygląd strony wpisów, kategorii, tagów, archiwów miesięcznych i wyszukiwania we wpisach
						</label>
						<p class="description">Jeśli w <em>Avada → Layouts</em> jest przypisany układ dla archiwów, ma on pierwszeństwo dla tych stron, które obejmuje.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="piodesign-per-page">Wpisów na stronę archiwum</label></th>
					<td>
						<input id="piodesign-per-page" type="number" min="6" max="48" step="3" name="piodesign_settings[archive_per_page]" value="<?php echo (int) piodesign_option( 'archive_per_page' ); ?>" class="small-text">
						<p class="description">Najlepiej wielokrotność 3 (karty stoją po trzy w rzędzie).</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * Site-wide fonts: point Avada's typography variables at our faces.
 * `html:root` outranks Avada's `:root`, and because Avada's per-element
 * variables (h1, body, nav…) are defined on :root as var(--awb-typography…),
 * overriding both levels covers global typography sets and direct choices.
 * ------------------------------------------------------------------------ */

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! piodesign_option( 'site_fonts' ) ) {
			return;
		}
		wp_enqueue_style( 'piodesign-fonts' );
		$display = '"Bricolage Grotesque", "Avenir Next", system-ui, sans-serif';
		$text    = '"Newsreader", "Iowan Old Style", Georgia, serif';
		$vars    = [];
		foreach ( [ 'awb-typography1', 'awb-typography2', 'awb-typography3', 'awb-typography5', 'h1_typography', 'h2_typography', 'h3_typography', 'h4_typography', 'h5_typography', 'h6_typography', 'post_title_typography', 'post_titles_extras_typography', 'footer_headings_typography', 'button_typography', 'nav_typography', 'mobile_menu_typography' ] as $v ) {
			$vars[] = '--' . $v . '-font-family:' . $display;
		}
		foreach ( [ 'awb-typography4', 'body_typography' ] as $v ) {
			$vars[] = '--' . $v . '-font-family:' . $text;
		}
		wp_add_inline_style( 'piodesign-fonts', 'html:root{' . implode( ';', $vars ) . '}' );
	},
	20
);
