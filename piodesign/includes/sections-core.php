<?php
/**
 * Pure helpers for the parish sections (sacraments, quotes, parish info,
 * liturgy of the day). No WordPress calls except escaping in templates, so
 * the offline preview uses the same code.
 *
 * @package PioDesign
 */

/* ---------------------------------------------------------------------------
 * Defaults (the parish's own texts from parafiapio.pl)
 * ------------------------------------------------------------------------ */

function piodesign_info_defaults() {
	return [
		'msze_niedziela'      => "07:30\n08:45\n10:00 | z udziałem dzieci\n11:15 | z udziałem dzieci\n12:30 | suma parafialna\n18:00",
		'msze_powszednie'     => "07:30 | w adwencie 6:30\n08:30\n18:00\n19:00 | w okresie kolędowym 15:00",
		'msze_sobota'         => "07:30 | w adwencie 6:30\n08:30\n18:00",
		'kancelaria'          => "poniedziałek | 17:00–17:45\nśroda | 9:00–10:00\nczwartek | 18:30–19:30\npiątek | 18:30–19:00",
		'kancelaria_uwagi'    => 'W I piątek miesiąca i w uroczystości biuro parafialne jest nieczynne.',
		'kancelaria_i_piatek' => 1,
		'nazwa'               => 'Parafia pw. św. Ojca Pio w Gdańsku',
		'adres'               => "ul. Przemyska 21\n80-180 Gdańsk",
		'telefon'             => '58 322 40 40',
		'email'               => 'info@parafiapio.pl',
		'konto'               => '20 1240 2920 1111 0000 4500 0120',
		'mapa'                => 'Parafia św. Ojca Pio, Przemyska 21, 80-180 Gdańsk',
		'partnerzy'           => "Stowarzyszenie Inicjatyw Lokalnych im. św. Ojca Pio | https://sil-pio.pl | Kultura, warsztaty i wydarzenia na Ujeścisku\nArchidiecezja Gdańska | https://www.diecezja.gda.pl | Komunikaty, wydarzenia i duszpasterstwo archidiecezji",
		'facebook'            => 'https://www.facebook.com/padrepiogda',
		'youtube'             => 'https://youtube.com/@Parafiaśw.OjcaPiowGdańsku',
		'cytaty'              => "Módl się i ufaj! Nie denerwuj się! Niepokój nie służy niczemu. Bóg jest miłosierny i wysłucha twoją modlitwę.\nPan Jezus nie żąda od ciebie, abyś z Nim dźwigała krzyż przez całe życie, ale niosła mały jego kawałek, w którym mieszczą się ludzkie cierpienia.\nNa tej ziemi każdy ma swój krzyż; powinniśmy jednak tak postępować, by nie być złym łotrem, ale dobrym.\nŚwiat mógłby istnieć bez słońca, ale nie bez Mszy Świętej.",
	];
}

/**
 * Sacrament texts and images from the homepage slider, keyed by page slug.
 * Used when a sacrament page has no excerpt or featured image of its own.
 */
function piodesign_sacrament_defaults() {
	return [
		'chrzest'              => [ 'Sakramentu chrztu udzielamy w II niedzielę miesiąca podczas Mszy św. o godz. 12.30. Możliwe jest udzielenie chrztu także w inne niedziele.', '2025/11/christening-4037405_1280.jpg', 'Chrzest święty' ],
		'bierzmowanie'         => [ 'Bierzmowanie to sakrament wtajemniczenia chrześcijańskiego, który potwierdza przynależność do Chrystusa i Kościoła i udziela łaski Ducha Świętego.', '2025/11/duch-swiety-2271709073.jpeg', '' ],
		'i-komunia-swieta'     => [ 'W przygotowaniu dziecka do pełnego udziału w Eucharystii uczestniczy cała rodzina – dając codzienny przykład chrześcijańskiego życia.', '2025/11/chalice-1591668_1280.jpg', '' ],
		'malzenstwo'           => [ 'Przed zawarciem sakramentu małżeństwa należy odbyć bezpośrednie przygotowanie w ramach kursu przedmałżeńskiego.', '2025/11/2643.jpg', '' ],
		'spowiedz'             => [ 'Spowiadamy codziennie od początku każdej Mszy Świętej, w czwartki podczas adoracji Najświętszego Sakramentu oraz przed I piątkiem miesiąca.', '', 'Spowiedź święta' ],
		'namaszczenie-chorych' => [ 'Sakrament namaszczenia chorych jest dla chorego szczególnym umocnieniem. Nie jest zwiastunem śmierci, lecz darem siły w chorobie i starości.', '2025/11/namaszczenie-chorych-898184390.jpg', '' ],
		'pogrzeb'              => [ 'Pogrzeb zgłasza najbliższa rodzina w parafii zamieszkania osoby zmarłej. W pierwszej kolejności wypełniamy Pogrzebowy Formularz Zgłoszeniowy.', '2025/11/candle-2038736_1280-e1762030146516.jpg', '' ],
	];
}

/* ---------------------------------------------------------------------------
 * Line formats used by the settings: "value | note | …"
 * ------------------------------------------------------------------------ */

function piodesign_lines( $text ) {
	$out = [];
	foreach ( preg_split( '/\R/u', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$out[] = array_map( 'trim', explode( '|', $line ) );
	}
	return $out;
}

/** "7.30" / "07:30" → "07:30"; null when no time found. */
function piodesign_hhmm( $s ) {
	if ( ! preg_match( '/(\d{1,2})[:.](\d{2})/', (string) $s, $m ) ) {
		return null;
	}
	return sprintf( '%02d:%s', (int) $m[1], $m[2] );
}

/** Display form: "7:30". */
function piodesign_time_label( $hhmm ) {
	return ltrim( substr( $hhmm, 0, 2 ), '0' ) . ':' . substr( $hhmm, 3, 2 );
}

/**
 * Mass times. A note such as "w adwencie 6:30" also gives the Advent time,
 * which the live "next Mass" uses during Advent.
 *
 * @return array{sun:array,wk:array,sat:array}
 */
function piodesign_mass_schedule( array $cfg ) {
	$parse = static function ( $text ) {
		$list = [];
		foreach ( piodesign_lines( $text ) as $row ) {
			$time = piodesign_hhmm( $row[0] );
			if ( ! $time ) {
				continue;
			}
			$note   = $row[1] ?? '';
			$advent = null;
			if ( preg_match( '/adwen\w*\s+(\d{1,2}[:.]\d{2})/iu', $note, $m ) ) {
				$advent = piodesign_hhmm( $m[1] );
			}
			$list[] = [
				'time'   => $time,
				'label'  => piodesign_time_label( $time ),
				'note'   => $note,
				'advent' => $advent,
			];
		}
		return $list;
	};
	return [
		'sun' => $parse( $cfg['msze_niedziela'] ?? '' ),
		'wk'  => $parse( $cfg['msze_powszednie'] ?? '' ),
		'sat' => $parse( $cfg['msze_sobota'] ?? '' ),
	];
}

/** Office hours: [ day (1–7), name, from, to ]. */
function piodesign_office_hours( $text ) {
	$days = [ 'pon' => 1, 'wt' => 2, 'śr' => 3, 'sr' => 3, 'czw' => 4, 'pi' => 5, 'pt' => 5, 'so' => 6, 'nie' => 7, 'nd' => 7 ];
	$out  = [];
	foreach ( piodesign_lines( $text ) as $row ) {
		$name = mb_strtolower( $row[0] );
		$day  = 0;
		foreach ( $days as $prefix => $n ) {
			if ( 0 === strpos( $name, $prefix ) ) {
				$day = $n;
				break;
			}
		}
		if ( ! $day || ! preg_match( '/(\d{1,2}[:.]\d{2})\s*[–—-]\s*(\d{1,2}[:.]\d{2})/u', $row[1] ?? '', $m ) ) {
			continue;
		}
		$out[] = [
			'day'  => $day,
			'name' => $row[0],
			'from' => piodesign_hhmm( $m[1] ),
			'to'   => piodesign_hhmm( $m[2] ),
		];
	}
	return $out;
}

/** Partners: [ name, url, host, note ]. */
function piodesign_partners( $text ) {
	$out = [];
	foreach ( piodesign_lines( $text ) as $row ) {
		if ( empty( $row[1] ) ) {
			continue;
		}
		$host  = preg_replace( '#^www\.#', '', (string) parse_url( $row[1], PHP_URL_HOST ) );
		$out[] = [
			'name' => $row[0],
			'url'  => $row[1],
			'host' => $host,
			'note' => $row[2] ?? '',
		];
	}
	return $out;
}

/** "20 1240 2920 …" → digits only, for copying. */
function piodesign_account_digits( $s ) {
	return preg_replace( '/\D+/', '', (string) $s );
}

/* ---------------------------------------------------------------------------
 * Quotes
 * ------------------------------------------------------------------------ */

function piodesign_quotes( $text ) {
	$out = [];
	foreach ( piodesign_lines( $text ) as $row ) {
		$q = trim( implode( ' | ', $row ), " \t\"„”“" );
		if ( '' !== $q ) {
			$out[] = $q;
		}
	}
	return $out;
}

/* ---------------------------------------------------------------------------
 * Liturgy of the day
 * ------------------------------------------------------------------------ */

/**
 * Splits the readings sigla ("Ga 1,6-12; Ps 111; Łk 10,25-37") into labelled
 * parts: psalm, gospel, first and second reading.
 */
function piodesign_readings( $sigla ) {
	$parts = array_values( array_filter( array_map( 'trim', preg_split( '/\s*;\s*/u', (string) $sigla ) ) ) );
	if ( ! $parts ) {
		return [];
	}
	$gospel = null;
	foreach ( $parts as $k => $p ) {
		if ( preg_match( '/^(Mt|Mk|Łk|Lk|J)\s+\d/u', $p ) ) {
			$gospel = $k; // last match wins
		}
	}
	$out     = [];
	$reading = 0;
	foreach ( $parts as $k => $p ) {
		if ( $k === $gospel ) {
			$label = 'Ewangelia';
		} elseif ( preg_match( '/^Ps\b/u', $p ) || ( null !== $gospel && $k < $gospel && $k > 0 && preg_match( '/^(Mt|Mk|Łk|Lk|J)\s+\d/u', $p ) ) ) {
			// Psalm, or a Gospel canticle (Łk 1,46-55) sung as the psalm.
			$label = 'Psalm';
		} elseif ( preg_match( '/^(Aklamacja|Alleluja)/iu', $p ) ) {
			$label = 'Aklamacja';
		} else {
			$reading++;
			$label = 1 === $reading ? 'I czytanie' : ( 2 === $reading ? 'II czytanie' : $reading . '. czytanie' );
		}
		$out[] = [
			'label' => $label,
			'sigla' => $p,
		];
	}
	return $out;
}

/**
 * One liturgical day for the [pio_liturgia] section.
 *
 * @param array|null $ext Day from "Parafia: Kalendarz liturgiczny"; null → season fallback.
 */
function piodesign_liturgy_day( DateTimeImmutable $d, $ext, DateTimeImmutable $now ) {
	$list = static function ( $v ) {
		$v = is_array( $v ) ? $v : ( '' === (string) $v ? [] : [ (string) $v ] );
		return array_values( array_filter( array_map( 'trim', $v ) ) );
	};
	$base = [
		'iso'       => $d->format( 'Y-m-d' ),
		'weekday'   => piodesign_weekdays()[ (int) $d->format( 'N' ) ],
		'weekday_s' => piodesign_weekdays( true )[ (int) $d->format( 'N' ) ],
		'day'       => $d->format( 'j' ),
		'month'     => piodesign_months()[ (int) $d->format( 'n' ) ],
		'date'      => piodesign_date( $d, true ),
		'sunday'    => 7 === (int) $d->format( 'N' ),
		'today'     => $d->format( 'Y-m-d' ) === $now->format( 'Y-m-d' ),
	];
	if ( is_array( $ext ) && ! empty( $ext['title'] ) ) {
		$color = (string) ( $ext['color_hex'] ?? '' );
		return $base + [
			'title'      => (string) $ext['title'],
			'rank'       => (string) ( $ext['rank_label'] ?? '' ),
			'color'      => preg_match( '/^#[0-9a-f]{3,8}$/i', $color ) ? $color : '#3f7a4f',
			'color_name' => (string) ( $ext['color_label'] ?? '' ),
			'optional'   => $list( $ext['optional_memorials'] ?? [] ),
			'parish'     => $list( $ext['parish'] ?? [] ),
			'occasional' => $list( $ext['occasional'] ?? [] ),
			'readings'   => piodesign_readings( $ext['readings'] ?? '' ),
			'source'     => 'kalendarz',
		];
	}
	$l = piodesign_liturgy( $d );
	return $base + [
		'title'      => trim( $l['label'] . ( $l['week'] ? ', ' . $l['week'] : '' ) ),
		'rank'       => '',
		'color'      => $l['color'],
		'color_name' => $l['color_name'],
		'optional'   => [],
		'parish'     => [],
		'occasional' => [],
		'readings'   => [],
		'source'     => 'fallback',
	];
}

/** Light liturgical colours (white, gold) need dark text on the swatch. */
function piodesign_is_light( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	return ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) > 170;
}

/* ---------------------------------------------------------------------------
 * Live status (computed on the server for the first paint; the script keeps
 * it current, so cached pages stay right).
 * ------------------------------------------------------------------------ */

/** Masses for a weekday (1–7): Sunday list, Saturday list, else weekdays. */
function piodesign_masses_for( array $schedule, $n ) {
	return 7 === (int) $n ? $schedule['sun'] : ( 6 === (int) $n ? $schedule['sat'] : $schedule['wk'] );
}

/** "za 1 godz. 12 min", "za 25 min", "za 2 dni". */
function piodesign_in_label( $minutes ) {
	$minutes = max( 0, (int) $minutes );
	if ( $minutes < 60 ) {
		return 'za ' . $minutes . ' min';
	}
	if ( $minutes < 24 * 60 ) {
		$h = intdiv( $minutes, 60 );
		$m = $minutes % 60;
		return 'za ' . $h . ' godz.' . ( $m ? ' ' . $m . ' min' : '' );
	}
	$d = (int) round( $minutes / 1440 );
	return 'za ' . $d . ' ' . piodesign_plural( $d, 'dzień', 'dni', 'dni' );
}

/**
 * Next Mass from now: [ time, label (dziś / jutro / weekday), in, n ].
 */
function piodesign_next_mass( array $schedule, DateTimeImmutable $now, $advent = false ) {
	for ( $add = 0; $add < 8; $add++ ) {
		$day = $now->setTime( 0, 0 )->modify( '+' . $add . ' days' );
		$n   = (int) $day->format( 'N' );
		$list = piodesign_masses_for( $schedule, $n );
		$times = [];
		foreach ( $list as $m ) {
			$times[] = ( $advent && $m['advent'] && $n < 7 ) ? $m['advent'] : $m['time'];
		}
		sort( $times );
		foreach ( $times as $t ) {
			$at = $day->setTime( (int) substr( $t, 0, 2 ), (int) substr( $t, 3, 2 ) );
			if ( $at > $now ) {
				$mins = (int) floor( ( $at->getTimestamp() - $now->getTimestamp() ) / 60 );
				return [
					'time'  => $t,
					'label' => 0 === $add ? 'dziś' : ( 1 === $add ? 'jutro' : piodesign_weekdays()[ $n ] ),
					'in'    => piodesign_in_label( $mins ),
					'n'     => $n,
				];
			}
		}
	}
	return null;
}

/** Is the given day the first Friday of its month? */
function piodesign_is_first_friday( DateTimeImmutable $d ) {
	return 5 === (int) $d->format( 'N' ) && (int) $d->format( 'j' ) <= 7;
}

/**
 * Parish office status: [ open (bool), text ].
 */
function piodesign_office_status( array $hours, DateTimeImmutable $now, $first_friday_closed = true ) {
	if ( ! $hours ) {
		return [ false, '' ];
	}
	$hm = $now->format( 'H:i' );
	foreach ( $hours as $h ) {
		if ( (int) $now->format( 'N' ) === $h['day'] && $hm >= $h['from'] && $hm < $h['to'] ) {
			if ( $first_friday_closed && piodesign_is_first_friday( $now ) ) {
				break;
			}
			return [ true, 'Otwarte teraz · do ' . piodesign_time_label( $h['to'] ) ];
		}
	}
	for ( $add = 0; $add < 15; $add++ ) {
		$day = $now->setTime( 0, 0 )->modify( '+' . $add . ' days' );
		if ( $first_friday_closed && piodesign_is_first_friday( $day ) ) {
			continue;
		}
		foreach ( $hours as $h ) {
			if ( (int) $day->format( 'N' ) !== $h['day'] || ( 0 === $add && $hm >= $h['from'] ) ) {
				continue;
			}
			$when = 0 === $add ? 'dziś' : ( 1 === $add ? 'jutro' : ( 2 === $h['day'] ? 'we ' : 'w ' ) . piodesign_weekday_acc( $h['day'] ) );
			return [ false, 'Zamknięte · otwieramy ' . $when . ' o ' . piodesign_time_label( $h['from'] ) ];
		}
	}
	return [ false, 'Zamknięte' ];
}

/** "w poniedziałek", "we wtorek", "w środę"… (accusative). */
function piodesign_weekday_acc( $n ) {
	return [ 1 => 'poniedziałek', 'wtorek', 'środę', 'czwartek', 'piątek', 'sobotę', 'niedzielę' ][ (int) $n ];
}

/**
 * Mass times from the Mass intentions plugin's markup: every .ki-dzien-item
 * has a date (.ki-data, "dd.mm.yyyy") and times (.ki-godzina, "HH:MM").
 *
 * @return string[] "Y-m-dTH:i"
 */
function piodesign_parse_intentions( $html ) {
	$out    = [];
	$blocks = preg_split( '/class="[^"]*\bki-dzien-item\b[^"]*"/', (string) $html );
	array_shift( $blocks );
	foreach ( $blocks as $b ) {
		if ( ! preg_match( '/class="[^"]*\bki-data\b[^"]*"[^>]*>\s*(\d{1,2})\.(\d{1,2})\.(\d{4})/', $b, $d ) ) {
			continue;
		}
		$date = sprintf( '%04d-%02d-%02d', $d[3], $d[2], $d[1] );
		if ( preg_match_all( '/class="[^"]*\bki-godzina\b[^"]*"[^>]*>\s*(\d{1,2})[:.](\d{2})/', $b, $t, PREG_SET_ORDER ) ) {
			foreach ( $t as $x ) {
				$out[] = $date . 'T' . sprintf( '%02d:%s', $x[1], $x[2] );
			}
		}
	}
	return $out;
}

/** Next Mass from explicit slots ("Y-m-dTH:i"), or null. */
function piodesign_next_mass_from_slots( array $slots, DateTimeImmutable $now ) {
	foreach ( $slots as $slot ) {
		$at = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $slot, $now->getTimezone() );
		if ( $at && $at > $now ) {
			$days = (int) round( ( $at->setTime( 0, 0 )->getTimestamp() - $now->setTime( 0, 0 )->getTimestamp() ) / 86400 );
			$n    = (int) $at->format( 'N' );
			return [
				'time'  => $at->format( 'H:i' ),
				'label' => 0 === $days ? 'dziś' : ( 1 === $days ? 'jutro' : piodesign_weekdays()[ $n ] ),
				'in'    => piodesign_in_label( (int) floor( ( $at->getTimestamp() - $now->getTimestamp() ) / 60 ) ),
				'n'     => $n,
			];
		}
	}
	return null;
}
