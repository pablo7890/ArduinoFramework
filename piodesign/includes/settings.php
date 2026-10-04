<?php
/**
 * Settings → PioDesign. One schema drives the defaults, the sanitising and
 * the tabbed settings page; shortcodes take their defaults from here, so
 * shortcode attributes are optional.
 *
 * @package PioDesign
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tabs → fields. Field: [ key, type, label, default, help, extra ].
 * Types: checkbox, number, text, textarea, url, email, select, page, image, quotes.
 */
function piodesign_settings_schema() {
	$info = piodesign_info_defaults();
	return [
		'news'     => [
			'title'  => 'Aktualności',
			'intro'  => 'Shortcode <code>[pio_aktualnosci]</code> na stronie głównej.',
			'fields' => [
				[ 'news_title', 'text', 'Duży tytuł', 'Aktualności' ],
				[ 'news_kicker', 'text', 'Mały napis nad tytułem', 'Z życia parafii' ],
				[ 'news_count', 'number', 'Liczba wpisów', 15, 'Układ: 1 główny, 2 boczne, 6 w siatce, reszta jako krótkie wzmianki.', [ 3, 30 ] ],
				[ 'news_mobile', 'number', 'Wpisów na telefonie', 5, 'Pod nimi przycisk „Wszystkie aktualności”.', [ 1, 30 ] ],
				[ 'news_excerpt_lead', 'number', 'Zajawka – wpis główny (znaki)', 260, '0 ukrywa zajawkę.', [ 0, 600 ] ],
				[ 'news_excerpt_side', 'number', 'Zajawka – wpisy boczne (znaki)', 120, '', [ 0, 400 ] ],
				[ 'news_excerpt_card', 'number', 'Zajawka – wpisy w siatce (znaki)', 150, '', [ 0, 400 ] ],
				[ 'news_show_day', 'checkbox', 'Dzień liturgiczny przy tytule', 1, 'Data, kolor liturgiczny, dzień liturgiczny i święto parafialne (na telefonie ukryte).' ],
				[ 'news_show_chips', 'checkbox', 'Filtr kategorii', 1, 'Pokazuje się, gdy wpisy mają co najmniej dwie różne kategorie.' ],
				[ 'news_show_photos', 'checkbox', 'Licznik zdjęć na zdjęciu', 1 ],
				[ 'news_gallery_hover', 'checkbox', 'Podgląd galerii po najechaniu', 1 ],
				[ 'news_new_days', 'number', 'Etykieta „Nowe” przez (dni)', 3, '0 wyłącza etykietę.', [ 0, 30 ] ],
			],
		],
		'archive'  => [
			'title'  => 'Archiwum i wpis',
			'intro'  => 'Strona „Aktualności”, kategorie, tagi, miesiące i pojedynczy wpis. Shortcode <code>[pio_archiwum]</code>.',
			'fields' => [
				[ 'news_archive', 'checkbox', 'Strona „Aktualności” w nowym wyglądzie', 1, 'Wtyczka pokazuje archiwum zamiast treści strony wybranej niżej (i strony wpisów z Ustawienia → Czytanie). Wyłącz, jeśli budujesz tę stronę w Avadzie z shortcode’em [pio_archiwum].' ],
				[ 'news_terms', 'checkbox', 'Kategorie, tagi i archiwa w nowym wyglądzie', 1, 'Widoki /category/…, /tag/…, archiwa miesięczne, autorzy i wyszukiwanie we wpisach – tu prowadzą zakładki kategorii, także z [pio_archiwum].' ],
				[ 'archive_page', 'page', 'Strona „Aktualności”', 0, 'Na tej stronie wtyczka pokaże archiwum zamiast treści zbudowanej w Avadzie.', [ 'auto' => 'aktualnosci' ] ],
				[ 'archive_per_page', 'number', 'Wpisów na stronę', 18, 'Najlepiej wielokrotność 3.', [ 6, 48 ] ],
				[ 'archive_excerpt', 'number', 'Zajawka w archiwum (znaki)', 150, '', [ 0, 400 ] ],
				[ 'single_post', 'checkbox', 'Nowy wygląd pojedynczego wpisu', 1 ],
				[ 'single_related', 'number', 'Wpisy „Czytaj także”', 3, '0 ukrywa sekcję.', [ 0, 9 ] ],
				[ 'single_share', 'checkbox', 'Przyciski udostępniania', 1, 'Facebook i kopiowanie linku.' ],
			],
		],
		'events'   => [
			'title'  => 'Wydarzenia',
			'intro'  => 'Shortcode <code>[pio_wydarzenia]</code> oraz widoki The Events Calendar.',
			'fields' => [
				[ 'events_title', 'text', 'Duży tytuł', 'Wydarzenia' ],
				[ 'events_kicker', 'text', 'Mały napis nad tytułem', 'Nadchodzące' ],
				[ 'events_count', 'number', 'Liczba wydarzeń', 10, '', [ 2, 30 ] ],
				[ 'events_mobile', 'number', 'Wydarzeń na telefonie', 5, '', [ 1, 30 ] ],
				[ 'events_weeks', 'number', 'Długość osi (tygodnie)', 5, '', [ 2, 8 ] ],
				[ 'events_excerpt', 'number', 'Zajawka na liście wydarzeń (znaki)', 170, '', [ 0, 400 ] ],
				[ 'tec_subscribe', 'checkbox', 'Przycisk „Subskrybuj” w kalendarzu', 0, 'Google, iPhone / Outlook i plik .ics – w pasku nad kalendarzem.' ],
				[ 'cal_per_page', 'number', 'Kalendarz – wydarzeń na stronie listy', 12, '', [ 4, 60 ] ],
				[ 'cal_month_max', 'number', 'Kalendarz – wydarzeń w kratce miesiąca', 3, 'Reszta pod „+2 więcej” (prowadzi do widoku dnia).', [ 1, 8 ] ],
				[ 'cal_lit', 'checkbox', 'Kalendarz – kolor liturgiczny i święta w kratkach miesiąca', 1, 'Z wtyczki „Parafia: Kalendarz liturgiczny”.' ],
				[ 'single_width', 'select', 'Pojedyncze wydarzenie i wpis – szerokość', 'site', 'Jeśli dla wydarzeń lub wpisów masz układ w Avada → Layouts, wtyczka go nie zastępuje: wstaw w sekcji treści element Code Block z <code>[pio_wydarzenie]</code> lub <code>[pio_wpis]</code>.', [ 'site' => 'Szerokość strony (Site Width z Avady)', 'full' => 'Cała szerokość obszaru treści' ] ],
			],
		],
		'liturgy'  => [
			'title'  => 'Liturgia dnia',
			'intro'  => 'Shortcode <code>[pio_liturgia]</code>. Dane z wtyczki „Parafia: Kalendarz liturgiczny”.',
			'fields' => [
				[ 'lit_title', 'text', 'Duży tytuł', 'Liturgia dnia' ],
				[ 'lit_kicker', 'text', 'Mały napis nad tytułem', 'Kalendarz liturgiczny' ],
				[ 'lit_days', 'number', 'Dni na pasku', 7, '', [ 1, 14 ] ],
				[ 'lit_podcast_url', 'url', 'Rozważanie Ewangelii – Spotify', 'https://open.spotify.com/show/2F5tOicGHhqpSH9NuKTqLz', 'Adres podcastu lub odcinka na Spotify. Puste pole ukrywa odtwarzacz.' ],
				[ 'lit_podcast_title', 'text', 'Tytuł odtwarzacza', 'Ewangelia na dziś' ],
			],
		],
		'faith'    => [
			'title'  => 'Sakramenty i cytaty',
			'intro'  => 'Shortcode\'y <code>[pio_sakramenty]</code> i <code>[pio_cytaty]</code>.',
			'fields' => [
				[ 'sac_title', 'text', 'Sakramenty – duży tytuł', 'Droga wiary' ],
				[ 'sac_kicker', 'text', 'Sakramenty – mały napis', 'Sakramenty i sakramentalia' ],
				[ 'sac_parent', 'text', 'Strona nadrzędna (slug)', 'sakramenty-i-sakramentalia', 'Slajdy to jej podstrony w kolejności z „Atrybuty strony → Kolejność”. Tekst: zajawka strony, zdjęcie: obrazek wyróżniający.' ],
				[ 'sac_exclude', 'text', 'Pomiń strony (slugi po przecinku)', '' ],
				[ 'sac_button', 'text', 'Napis na przycisku', 'Dowiedz się więcej' ],
				[ 'sac_autoplay', 'number', 'Sakramenty – sekundy na slajd', 7, '0 wyłącza autoodtwarzanie.', [ 0, 60 ] ],
				[ 'q_title', 'text', 'Cytaty – napis u góry', 'Słowa na dziś' ],
				[ 'q_author', 'text', 'Cytaty – podpis', 'św. Ojciec Pio' ],
				[ 'q_photo', 'image', 'Cytaty – portret przy podpisie', '' ],
				[ 'q_autoplay', 'number', 'Cytaty – sekundy na cytat', 9, '0 wyłącza autoodtwarzanie.', [ 0, 60 ] ],
				[ 'cytaty', 'quotes', 'Cytaty św. Ojca Pio', $info['cytaty'] ],
			],
		],
		'info'     => [
			'title'  => 'Informacje',
			'intro'  => 'Shortcode <code>[pio_informacje]</code> – sekcja przed stopką.',
			'fields' => [
				[ 'info_title', 'text', 'Duży tytuł', 'Zapraszamy' ],
				[ 'info_kicker', 'text', 'Mały napis nad tytułem', 'Parafia św. Ojca Pio' ],
				[ 'mass_source', 'select', 'Licznik „Najbliższa Msza” liczy z', 'auto', 'Wtyczka intencji zna godziny każdej Mszy w danym tygodniu, więc licznik jest dokładny także w dni z innym porządkiem.', [ 'auto' => 'Intencji mszalnych, gdy są dostępne (zalecane)', 'settings' => 'Godzin wpisanych poniżej' ] ],
				[ 'intencje_page', 'page', 'Strona z intencjami', 0, 'Strona, na której wtyczka intencji pokazuje tydzień Mszy.', [ 'auto' => 'intencje-mszalne' ] ],
				[ 'msze_niedziela', 'textarea', 'Msze – niedziele i święta', $info['msze_niedziela'], 'Jedna Msza w wierszu: <code>godzina | uwaga</code>, np. <code>12:30 | suma parafialna</code>.' ],
				[ 'msze_powszednie', 'textarea', 'Msze – poniedziałek – piątek', $info['msze_powszednie'], 'Uwaga „w adwencie 6:30” zmienia godzinę w Adwencie.' ],
				[ 'msze_sobota', 'textarea', 'Msze – sobota', $info['msze_sobota'] ],
				[ 'kancelaria', 'textarea', 'Kancelaria – godziny', $info['kancelaria'], '<code>dzień | od–do</code>, np. <code>środa | 9:00–10:00</code>.' ],
				[ 'kancelaria_uwagi', 'text', 'Kancelaria – uwagi', $info['kancelaria_uwagi'] ],
				[ 'kancelaria_i_piatek', 'checkbox', 'Kancelaria nieczynna w I piątek miesiąca', 1 ],
				[ 'nazwa', 'text', 'Nazwa parafii', $info['nazwa'] ],
				[ 'adres', 'textarea', 'Adres', $info['adres'] ],
				[ 'telefon', 'text', 'Telefon', $info['telefon'] ],
				[ 'email', 'email', 'E-mail', $info['email'] ],
				[ 'konto', 'text', 'Numer konta', $info['konto'] ],
				[ 'mapa', 'text', 'Mapa – adres do wyszukania', $info['mapa'], 'Mapa Google ładuje się dopiero po kliknięciu.' ],
				[ 'partnerzy', 'textarea', 'Strony zaprzyjaźnione', $info['partnerzy'], '<code>nazwa | adres strony | krótki opis</code>' ],
				[ 'facebook', 'url', 'Facebook', $info['facebook'] ],
				[ 'youtube', 'url', 'YouTube', $info['youtube'] ],
			],
		],
		'general'  => [
			'title'  => 'Wygląd',
			'intro'  => '',
			'fields' => [
				[ 'display_font', 'select', 'Krój tytułów wtyczki', 'bundled', 'Avada zwykle wczytuje Bricolage w jednej grubości i bez osi szerokości, więc tytuły nie są pogrubione ani zwężone. Wbudowana wersja (185 KB) daje dokładnie wygląd z projektu. Treść zawsze używa kroju treści z Avady.', [ 'bundled' => 'Wbudowany Bricolage Grotesque – pogrubiony i zwężony, jak w projekcie', 'avada' => 'Krój nagłówków z Avady (Global Options)' ] ],
			],
		],
	];
}

function piodesign_settings_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = [];
		foreach ( piodesign_settings_schema() as $tab ) {
			foreach ( $tab['fields'] as $f ) {
				$defaults[ $f[0] ] = $f[3];
			}
		}
	}
	return $defaults;
}

function piodesign_options() {
	static $opts = null;
	if ( null === $opts || doing_action( 'update_option_piodesign_settings' ) ) {
		$opts = wp_parse_args( (array) get_option( 'piodesign_settings', [] ), piodesign_settings_defaults() );
	}
	return $opts;
}

function piodesign_option( $key ) {
	$o = piodesign_options();
	return $o[ $key ] ?? null;
}

/** Page chosen in a "page" field, else the page with the given slug. */
function piodesign_page_option( $key, $slug ) {
	$id = (int) piodesign_option( $key );
	if ( $id && 'page' === get_post_type( $id ) ) {
		return $id;
	}
	$page = get_page_by_path( $slug );
	return $page ? (int) $page->ID : 0;
}

function piodesign_archive_page_id() {
	return piodesign_page_option( 'archive_page', 'aktualnosci' );
}

function piodesign_archive_url() {
	$page = piodesign_archive_page_id();
	if ( $page ) {
		return get_permalink( $page );
	}
	$posts_page = (int) get_option( 'page_for_posts' );
	return $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
}

/* ---------------------------------------------------------------------------
 * Registration, sanitising
 * ------------------------------------------------------------------------ */

add_action(
	'admin_menu',
	static function () {
		$hook = add_options_page( 'PioDesign', 'PioDesign', 'manage_options', 'piodesign', 'piodesign_settings_page' );
		add_action(
			'admin_enqueue_scripts',
			static function ( $current ) use ( $hook ) {
				if ( $current === $hook ) {
					wp_enqueue_media();
				}
			}
		);
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
	$out = [];
	foreach ( piodesign_settings_schema() as $tab ) {
		foreach ( $tab['fields'] as $f ) {
			list( $key, $type ) = $f;
			$val = $in[ $key ] ?? null;
			switch ( $type ) {
				case 'checkbox':
					$out[ $key ] = empty( $val ) ? 0 : 1;
					break;
				case 'number':
				case 'page':
					$n = (int) $val;
					if ( 'number' === $type && isset( $f[5] ) ) {
						$n = max( $f[5][0], min( $f[5][1], $n ) );
					}
					$out[ $key ] = max( 0, $n );
					break;
				case 'textarea':
					$out[ $key ] = sanitize_textarea_field( (string) $val );
					break;
				case 'quotes':
					$list        = array_filter( array_map( 'sanitize_textarea_field', (array) $val ), static fn( $q ) => '' !== trim( $q ) );
					$out[ $key ] = implode( "\n", array_map( static fn( $q ) => preg_replace( '/\s*\R\s*/u', ' ', trim( $q ) ), $list ) );
					break;
				case 'url':
				case 'image':
					$out[ $key ] = esc_url_raw( (string) $val );
					break;
				case 'email':
					$out[ $key ] = sanitize_email( (string) $val );
					break;
				case 'select':
					$out[ $key ] = array_key_exists( (string) $val, $f[5] ) ? (string) $val : $f[3];
					break;
				default:
					$out[ $key ] = sanitize_text_field( (string) $val );
			}
		}
	}
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

/* ---------------------------------------------------------------------------
 * Settings page
 * ------------------------------------------------------------------------ */

function piodesign_settings_field( array $f, array $o ) {
	list( $key, $type, $label ) = $f;
	$help  = $f[4] ?? '';
	$extra = $f[5] ?? [];
	$name  = 'piodesign_settings[' . $key . ']';
	$id    = 'pd-' . $key;
	$val   = $o[ $key ] ?? $f[3];
	$kses  = [ 'code' => [], 'strong' => [], 'em' => [], 'br' => [] ];

	echo '<tr><th scope="row">';
	if ( 'checkbox' !== $type ) {
		printf( '<label for="%s">%s</label>', esc_attr( $id ), esc_html( $label ) );
	} else {
		echo esc_html( $label );
	}
	echo '</th><td>';

	switch ( $type ) {
		case 'checkbox':
			printf( '<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>', esc_attr( $id ), esc_attr( $name ), checked( $val, 1, false ), $help ? wp_kses( $help, $kses ) : 'Włączone' );
			$help = '';
			break;
		case 'number':
			printf( '<input type="number" id="%1$s" name="%2$s" value="%3$d" min="%4$d" max="%5$d" class="small-text">', esc_attr( $id ), esc_attr( $name ), (int) $val, (int) ( $extra[0] ?? 0 ), (int) ( $extra[1] ?? 9999 ) );
			break;
		case 'textarea':
			printf( '<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text code">%4$s</textarea>', esc_attr( $id ), esc_attr( $name ), max( 2, min( 8, substr_count( (string) $val, "\n" ) + 2 ) ), esc_textarea( (string) $val ) );
			break;
		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $extra as $k => $l ) {
				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $k ), selected( $val, $k, false ), esc_html( $l ) );
			}
			echo '</select>';
			break;
		case 'page':
			wp_dropdown_pages(
				[
					'name'              => esc_attr( $name ),
					'id'                => esc_attr( $id ),
					'selected'          => (int) $val,
					'show_option_none'  => '— wykryj automatycznie (/' . $extra['auto'] . '/) —',
					'option_none_value' => '0',
				]
			);
			$found = piodesign_page_option( $key, $extra['auto'] );
			$help .= ' Teraz: ' . ( $found ? '<a href="' . esc_url( get_permalink( $found ) ) . '">' . esc_html( get_the_title( $found ) ) . '</a>' : 'brak strony' ) . '.';
			$kses['a'] = [ 'href' => [] ];
			break;
		case 'image':
			printf(
				'<span class="pd-image"><input type="url" id="%1$s" name="%2$s" value="%3$s" class="regular-text"> <button type="button" class="button pd-pick" data-target="%1$s">Wybierz z biblioteki</button> %4$s</span>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $val ),
				$val ? '<img src="' . esc_url( (string) $val ) . '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;vertical-align:middle">' : ''
			);
			break;
		case 'quotes':
			echo '<div class="pd-quotes" data-name="' . esc_attr( $name ) . '[]">';
			foreach ( piodesign_quotes( (string) $val ) as $q ) {
				printf( '<div class="pd-quote"><textarea name="%1$s[]" rows="2" class="large-text">%2$s</textarea><button type="button" class="button-link-delete pd-del">Usuń</button></div>', esc_attr( $name ), esc_textarea( $q ) );
			}
			echo '</div><p><button type="button" class="button pd-add">+ Dodaj cytat</button></p>';
			$help = 'Cytaty pokazują się po kolei w karcie „Słowa na dziś”. Bez cudzysłowów – wtyczka doda je sama.';
			break;
		default:
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text">', esc_attr( in_array( $type, [ 'url', 'email' ], true ) ? $type : 'text' ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
	}
	if ( $help ) {
		echo '<p class="description">' . wp_kses( $help, $kses ) . '</p>';
	}
	echo '</td></tr>';
}

function piodesign_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o      = piodesign_options();
	$schema = piodesign_settings_schema();
	?>
	<div class="wrap pd-wrap">
		<h1>Parafia: PioDesign</h1>
		<nav class="nav-tab-wrapper pd-tabs">
			<?php foreach ( $schema as $slug => $tab ) : ?>
				<a href="#pd-<?php echo esc_attr( $slug ); ?>" class="nav-tab" data-tab="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'piodesign' ); ?>
			<?php foreach ( $schema as $slug => $tab ) : ?>
				<section class="pd-panel" id="pd-<?php echo esc_attr( $slug ); ?>" data-panel="<?php echo esc_attr( $slug ); ?>">
					<h2><?php echo esc_html( $tab['title'] ); ?></h2>
					<?php if ( $tab['intro'] ) : ?><p><?php echo wp_kses( $tab['intro'], [ 'code' => [] ] ); ?></p><?php endif; ?>
					<table class="form-table" role="presentation">
						<?php
						foreach ( $tab['fields'] as $f ) {
							piodesign_settings_field( $f, $o );
						}
						?>
					</table>
				</section>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<style>
		.pd-wrap .pd-panel { max-width: 1000px; }
		.pd-wrap.js .pd-panel { display: none; }
		.pd-wrap.js .pd-panel.is-on { display: block; }
		.pd-quote { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 8px; }
		.pd-quote textarea { flex: 1; }
		.pd-quote .pd-del { margin-top: 6px; }
	</style>
	<script>
	( function () {
		var wrap = document.querySelector( '.pd-wrap' );
		wrap.classList.add( 'js' );
		var tabs = wrap.querySelectorAll( '.pd-tabs a' );
		function show( slug ) {
			tabs.forEach( function ( t ) { t.classList.toggle( 'nav-tab-active', t.dataset.tab === slug ); } );
			wrap.querySelectorAll( '.pd-panel' ).forEach( function ( p ) { p.classList.toggle( 'is-on', p.dataset.panel === slug ); } );
			try { sessionStorage.setItem( 'pdTab', slug ); } catch ( e ) {}
		}
		tabs.forEach( function ( t ) { t.addEventListener( 'click', function ( e ) { e.preventDefault(); show( t.dataset.tab ); } ); } );
		var start = tabs[0].dataset.tab;
		try { start = sessionStorage.getItem( 'pdTab' ) || start; } catch ( e ) {}
		show( start );

		wrap.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'pd-del' ) ) {
				e.target.closest( '.pd-quote' ).remove();
			}
			if ( e.target.classList.contains( 'pd-add' ) ) {
				var list = wrap.querySelector( '.pd-quotes' );
				var row = document.createElement( 'div' );
				row.className = 'pd-quote';
				row.innerHTML = '<textarea rows="2" class="large-text"></textarea><button type="button" class="button-link-delete pd-del">Usuń</button>';
				row.querySelector( 'textarea' ).name = list.dataset.name;
				list.appendChild( row );
				row.querySelector( 'textarea' ).focus();
			}
			if ( e.target.classList.contains( 'pd-pick' ) && window.wp && wp.media ) {
				var input = document.getElementById( e.target.dataset.target );
				var frame = wp.media( { title: 'Wybierz zdjęcie', multiple: false, library: { type: 'image' } } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					input.value = ( a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url );
				} );
				frame.open();
			}
		} );
	} )();
	</script>
	<?php
}
