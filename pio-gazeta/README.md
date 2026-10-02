# Parafia: PioDesign

Wersja 1.0 · autor: cruzLabs

Nowy wygląd aktualności i kalendarza dla parafiapio.pl.

Wtyczka WordPress, która daje stronie głównej parafii nowy układ aktualności
i wydarzeń oraz nowy wygląd widoków The Events Calendar (TEC, wersja darmowa).

Co jest w środku:

| Widok | Jak się pojawia |
|---|---|
| **Aktualności** (12–15 wpisów, układ „gazetowy”) | shortcode `[pio_aktualnosci]` |
| **Nadchodzące wydarzenia** (oś 5 tygodni + 8–10 wydarzeń) | shortcode `[pio_wydarzenia]` |
| **Pojedyncze wydarzenie** (bilet z odliczaniem, dodaj do kalendarza) | automatycznie, podmienia szablon TEC |
| **Lista wydarzeń** (archiwum TEC, widok „Lista”) | automatycznie, podmienia wiersze listy TEC |

## Pomysły, których nie ma na co drugiej stronie

- **Dzień liturgiczny w nagłówku.** Nad aktualnościami i nad kalendarzem:
  dzień tygodnia z datą, kółeczko w kolorze liturgicznym, „Dzień liturgiczny”
  (z rangą i kolorem) oraz „Święto parafialne”. Dane pochodzą z wtyczki
  **Parafia: Kalendarz liturgiczny** (filtr `kalendarz_liturgiczny_day`).
  Gdy jej nie ma albo rok nie jest zatwierdzony, wtyczka sama liczy okres
  liturgiczny („Okres zwykły, XXVI tydzień”) i pokazuje go jako „Okres
  liturgiczny”. Pole „Święto parafialne” pojawia się tylko, gdy w danym
  dniu coś jest wpisane.
- **Podgląd galerii na najechanie.** Wpis z fotorelacją pokazuje licznik zdjęć
  („137 zdjęć”), a po najechaniu myszą przewija 3 kolejne zdjęcia z galerii
  (FooGallery, galerie WordPressa i zdjęcia we wpisie są wykrywane same).
  Na telefonie galeria odtwarza się, gdy karta jest na środku ekranu.
- **Dwie gazetowe kolumny wydarzeń.** Karta „Najbliżej” otwiera pierwszą
  kolumnę, a lista płynie pod nią i dalej w drugiej; kolumny wyrównują się
  same, bez pustych miejsc. Na końcu jest kafelek „Pełny kalendarz parafii”.
- **Oś wydarzeń.** Pas 5 tygodni: kropka to wydarzenie jednodniowe, pasek to
  wydarzenie kilkudniowe (np. „Modlitwa różańcowa w październiku”). Niedziele
  są wyróżnione. Klik w kropkę przewija do wydarzenia i je podświetla;
  najechanie na wydarzenie podświetla jego dzień na osi.
- **„Trwa teraz” z paskiem postępu.** Wydarzenia wielodniowe w trakcie mają
  pasek „dzień 29 z 30”.
- **Relatywne daty.** „jutro”, „za 3 dni”, „za tydzień” zamiast samych dat;
  podział na „W tym tygodniu / W przyszłym tygodniu / Później”.
- **Inteligentne kadrowanie 16:9.** Zdjęcia są kadrowane do 16:9, ale plakaty,
  zdjęcia pionowe i kwadratowe pokazują się w całości na rozmytym tle z
  tego samego obrazu – nie ucina napisów ani twarzy.
- **Bilet wydarzenia.** Pojedyncze wydarzenie ma przyklejony z boku „bilet”
  z dużą datą, godziną, odliczaniem na żywo, miejscem (link do mapy),
  organizatorem i przyciskami „Kalendarz Google” oraz „.ics” (iPhone/Outlook).
- **Filtr kategorii z płynnym przełożeniem układu** (View Transitions API).

Animacje: wejście tytułów i głównego zdjęcia, odsłanianie zdjęć przy
przewijaniu (CSS scroll-driven animations), rosnące paski na osi, tykające
odliczanie. Wszystko wyłącza się przy systemowym „ogranicz ruch”.

## Instalacja

1. Skopiuj folder `pio-gazeta` do `wp-content/plugins/` (albo spakuj go do ZIP
   i wgraj w *Wtyczki → Dodaj nową → Wyślij wtyczkę na serwer*).
2. Włącz wtyczkę **Parafia: PioDesign**.
3. Na stronie głównej w Avada Builderze usuń obecne elementy „Aktualności”
   i „Wydarzenia”, a w ich miejsce dodaj dwa kontenery, w każdym element
   **Code Block** (albo **Text Block**) z shortcode'em:

   ```
   [pio_aktualnosci count="15"]
   ```

   ```
   [pio_wydarzenia count="10"]
   ```

   Kontener: szerokość „Site Width”, bez dodatkowych paddingów po bokach.
   Sekcja wydarzeń ma własne ciemne tło z zaokrągleniem, więc kontener może
   zostać biały.
4. Widoki TEC (lista wydarzeń i pojedyncze wydarzenie) zmieniają się same.

### Parametry shortcode'ów

`[pio_aktualnosci]`

| Parametr | Domyślnie | Opis |
|---|---|---|
| `count` | `15` | Liczba wpisów. Układ: 1 główny + 2 boczne + 6 w siatce + reszta jako krótkie wzmianki. |
| `mobile` | `5` | Ile wpisów widać na telefonie (do 700 px); pod nimi przycisk „Wszystkie aktualności”. Filtr kategorii nadal przeszukuje wszystkie. |
| `category` | – | Slug kategorii, jeśli chcesz tylko jedną. |
| `title` | `Aktualności` | Duży tytuł sekcji. |
| `kicker` | `Z życia parafii` | Mały napis nad tytułem. |
| `archive_url` | strona wpisów | Adres przycisku „Starsze aktualności”. |

`[pio_wydarzenia]`

| Parametr | Domyślnie | Opis |
|---|---|---|
| `count` | `10` | Liczba nadchodzących (i trwających) wydarzeń. |
| `mobile` | `5` | Ile wydarzeń widać na telefonie (karta „Najbliżej” + lista); pod nimi „Pełny kalendarz parafii”. Blok „Trwa teraz” zostaje widoczny. |
| `category` | – | Slug kategorii wydarzeń TEC. |
| `weeks` | `5` | Długość osi w tygodniach (2–8). |
| `title` / `kicker` | `Wydarzenia` / `Nadchodzące` | Nagłówki. |
| `calendar_url` | strona kalendarza TEC | Adres przycisku „Cały kalendarz”. |

## Kategorie wpisów

Dziś prawie każdy wpis ma kategorię „Aktualności”, więc filtr kategorii
nie miałby czego filtrować (pokazuje się dopiero przy 2+ kategoriach).
Propozycja: dodać kategorie podrzędne, np. **Liturgia, Wspólnoty, Kultura,
Fotorelacje, Pielgrzymki, Caritas, Ogłoszenia**, i zaznaczać je obok
„Aktualności”. Wtyczka wybiera na kartę:

1. kategorię główną z Yoast SEO (jeśli jest ustawiona),
2. w przeciwnym razie najbardziej konkretną (pomija „Aktualności” i „Bez kategorii”),
3. a gdy nie ma innej – „Aktualności”.

Kolor kategorii jest dobierany automatycznie. Własne kolory:

```php
add_filter( 'pio_gazeta_category_color', function ( $color, $slug ) {
	$map = [ 'liturgia' => '#6b4a8f', 'caritas' => '#b3261e' ];
	return $map[ $slug ] ?? $color;
}, 10, 2 );
```

## The Events Calendar – szczegóły

- **Lista wydarzeń:** wtyczka rejestruje własny katalog szablonów przez filtr
  `tribe_template_path_list` i podmienia tylko `v2/list/event.php` oraz
  `v2/list/month-separator.php`. Wyszukiwarka, przełącznik widoków,
  paginacja i AJAX zostają z TEC (przekolorowane zmiennymi `--tec-*`).
  Nad wyszukiwarką pojawia się nagłówek „Kalendarz parafii” z osią.
- **Pojedyncze wydarzenie:** filtr `tribe_events_template` podmienia
  `single-event.php`. Działa dla wydarzeń edytowanych w klasycznym edytorze
  (tak jak teraz). Jeżeli w *Wydarzenia → Ustawienia* włączony jest edytor
  blokowy dla wydarzeń, TEC używa innego szablonu – wtedy trzeba go wyłączyć.
- **Avada Layouts:** jeżeli w *Avada → Layouts* jest przypisany układ
  dla pojedynczych wydarzeń (Single Event), ma on pierwszeństwo – trzeba go
  odpiąć, żeby zobaczyć nowy widok.
- Wyłączenie którejkolwiek części:

  ```php
  add_filter( 'pio_gazeta_tec_list', '__return_false' );   // lista
  add_filter( 'pio_gazeta_tec_single', '__return_false' ); // pojedyncze
  ```

## Czcionki

Czcionki są we wtyczce (`assets/fonts/`, WOFF2, znaki łacińskie i polskie,
licencja SIL OFL dołączona): **Bricolage Grotesque** (tytuły, daty,
etykiety) i **Newsreader** (treść, zajawki). Strona nie łączy się z Google
Fonts. Dwa główne pliki są wczytywane z wyprzedzeniem (`preload`).
Jeśli motyw sam dostarcza te kroje, można wyłączyć ładowanie:

```php
add_filter( 'pio_gazeta_load_fonts', '__return_false' );
```

## Dopasowanie do motywu

Wszystkie kolory i kroje to zmienne CSS na `.pio`. Przykład w *Avada →
Options → Custom CSS*:

```css
.pio, .pio-tec {
	--pio-rust: #c14e00;        /* akcent */
	--pio-gold: #dba562;        /* złoto w sekcji wydarzeń */
	--pio-night: #14161b;       /* tło sekcji wydarzeń */
	--pio-sticky-top: 110px;    /* wysokość przyklejonego menu Avady */
}
```

## Wydajność

- HTML sekcji jest cache'owany na 10 minut (transient) i czyszczony przy
  każdym zapisie wpisu, wydarzenia, miejsca, organizatora lub galerii.
  Zalogowani widzą zawsze wersję na żywo. Zmiana czasu: filtr
  `pio_gazeta_cache_ttl`.
- Zdjęcia mają `srcset` i `loading="lazy"` (poza głównym zdjęciem).
- Lista zdjęć z galerii wpisu jest liczona raz i trzymana do zmiany wpisu.
- JS ok. 10 KB (bez kompresji), bez jQuery.

## Filtry dla programisty

| Filtr | Do czego |
|---|---|
| `pio_gazeta_posts_query` | Argumenty `WP_Query` dla aktualności. |
| `pio_gazeta_post_gallery_ids` | Lista ID zdjęć z galerii wpisu. |
| `pio_gazeta_generic_categories` | Slugi kategorii pomijanych przy wyborze etykiety. |
| `pio_gazeta_category_color` | Kolor kategorii. |
| `pio_gazeta_template` | Ścieżka szablonu (możesz podmienić dowolny plik z `templates/`). |
| `pio_gazeta_load_fonts`, `pio_gazeta_cache_ttl`, `pio_gazeta_tec_list`, `pio_gazeta_tec_single` | Opisane wyżej. |

## Struktura

```
pio-gazeta/
├── pio-gazeta.php            shortcode'y, zasoby, cache
├── includes/core.php         daty po polsku, rok liturgiczny, logika wydarzeń, kadrowanie
├── includes/wp-data.php      zapytania WP/TEC → dane dla szablonów
├── includes/tec.php          integracja z The Events Calendar
├── templates/                szablony HTML (news, events, single-event, partials/…)
├── tribe/events/v2/list/     nadpisania widoku listy TEC
├── tec/single-event.php      nadpisanie pojedynczego wydarzenia TEC
└── assets/                   pio-gazeta.css, pio-gazeta.js
```
