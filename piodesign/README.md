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
| **Archiwum aktualności** (strona wpisów, kategorie, tagi, miesiące, wyszukiwanie we wpisach) | automatycznie, do wyłączenia w *Ustawienia → PioDesign* |
| **Czcionki na całej stronie** | opcja w *Ustawienia → PioDesign* |

## Pomysły, których nie ma na co drugiej stronie

- **Dzień liturgiczny w „uchu” nagłówka.** Po prawej stronie tytułu
  „Aktualności” (i „Nadchodzące wydarzenia” w kalendarzu), jak ucho w gazecie:
  dzień tygodnia z datą, kółeczko w kolorze liturgicznym, „Dzień liturgiczny”
  (z rangą i kolorem) oraz „Święto parafialne”. Na telefonie na stronie
  głównej ucho jest ukryte – od razu widać „Z życia parafii”. Dane pochodzą z wtyczki
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

1. Spakuj folder `piodesign` do ZIP i wgraj w *Wtyczki → Dodaj nową →
   Wyślij wtyczkę na serwer* (albo skopiuj folder do `wp-content/plugins/`).
2. Włącz wtyczkę **Parafia: PioDesign**.
3. Na stronie głównej w Avada Builderze dodaj dwa kontenery, w każdym
   element **Code Block** (albo **Text Block**) z shortcode'em:

   ```
   [pio_aktualnosci count="15"]
   ```

   ```
   [pio_wydarzenia count="10"]
   ```

   Kontener: szerokość „Site Width”, bez dodatkowych paddingów po bokach.
4. Widoki TEC (lista wydarzeń i pojedyncze wydarzenie) oraz archiwum
   aktualności zmieniają się same. Opcje: *Ustawienia → PioDesign*.

### Aktualizacja z wersji w folderze `parafia-piodesign`

Folder i funkcje mają teraz nazwę `piodesign`, więc WordPress widzi to jako
nową wtyczkę. Najpierw **wyłącz i usuń** starą „Parafia: PioDesign” (z
folderu `parafia-piodesign`), potem wgraj nową. Shortcode'y się nie
zmieniły, więc strona główna działa bez poprawek.

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
add_filter( 'piodesign_category_color', function ( $color, $slug ) {
	$map = [ 'liturgia' => '#6b4a8f', 'caritas' => '#b3261e' ];
	return $map[ $slug ] ?? $color;
}, 10, 2 );
```

## The Events Calendar – szczegóły

- **Lista wydarzeń:** wtyczka podmienia tylko dwa szablony TEC
  (`v2/list/event.php` i `v2/list/month-separator.php`) przez filtr
  `tribe_template_file`. Wyszukiwarka, przełącznik widoków, paginacja i AJAX
  zostają z TEC (przekolorowane zmiennymi `--tec-*`). Nad nimi jest nagłówek
  „Kalendarz / Nadchodzące wydarzenia” z uchem dnia i osią 5 tygodni.
- **Pojedyncze wydarzenie:** filtr `tribe_events_template` podmienia
  `single-event.php`. Widok zajmuje całą szerokość: boczny panel Avady
  „Szczegóły” (opcja *Avada → Options → The Events Calendar → Event Meta
  Layout: Sidebar*) jest wyłączany na tej stronie, bo bilet pokazuje te same
  dane. Możesz też ustawić tę opcję na *Disabled* na stałe.
- Działa dla wydarzeń w klasycznym edytorze (tak jak teraz). Jeżeli w
  *Wydarzenia → Ustawienia* włączysz edytor blokowy dla wydarzeń, TEC użyje
  innego szablonu.
- **Avada Layouts:** jeżeli w *Avada → Layouts* jest przypisany układ dla
  pojedynczych wydarzeń albo archiwów, ma on pierwszeństwo – trzeba go odpiąć.
- Wyłączenie którejkolwiek części:

  ```php
  add_filter( 'piodesign_tec_list', '__return_false' );     // lista wydarzeń
  add_filter( 'piodesign_tec_single', '__return_false' );   // pojedyncze wydarzenie
  add_filter( 'piodesign_news_archive', '__return_false' ); // archiwum aktualności
  ```

## Archiwum aktualności

Obejmuje: stronę wpisów (*Ustawienia → Czytanie → Strona z wpisami*),
kategorie, tagi, archiwa miesięczne i roczne oraz wyszukiwanie ograniczone do
wpisów (formularz w archiwum dodaje `post_type=post`).

- Nagłówek jak na stronie głównej, w uchu: liczba wpisów, numer strony i
  wyszukiwarka. Pod nim kategorie jako zakładki (z liczbą wpisów).
- Strona 1: wpis główny i dwa boczne, dalej wpisy pogrupowane po miesiącach
  pod dużymi nagłówkami („Wrzesień 2026”), po trzy w rzędzie. Te same karty,
  podgląd galerii i animacje co na stronie głównej.
- Paginacja: „Nowsze / Starsze” i numery stron.
- Liczba wpisów na stronę: *Ustawienia → PioDesign* (domyślnie 18).
- Szablon używa nagłówka i stopki Avady (`get_header()` / `get_footer()`),
  bez paska bocznego. Pasek tytułu strony Avady (Page Title Bar) możesz
  wyłączyć w opcjach archiwów, bo archiwum ma własny tytuł.

## Czcionki

Czcionki są we wtyczce (`assets/fonts/`, WOFF2, znaki łacińskie i polskie,
licencja SIL OFL dołączona): **Bricolage Grotesque** (tytuły, daty,
etykiety) i **Newsreader** (treść, zajawki). Strona nie łączy się z Google
Fonts. Dwa główne pliki są wczytywane z wyprzedzeniem (`preload`).

### Na całej stronie

Nie trzeba niczego wgrywać do Avady. Zaznacz *Ustawienia → PioDesign →
Czcionki na całej stronie*. Wtyczka podmienia kroje w zmiennych typografii
Avady (globalne zestawy Typography 1–5 oraz nagłówki, menu, przyciski,
treść):

- nagłówki, menu, przyciski, tytuły wpisów: Bricolage Grotesque,
- treść: Newsreader.

Rozmiary, grubości i interlinię nadal ustawiasz w *Avada → Options →
Typography*. Dobrze wyglądają: nagłówki 700, menu 600, treść 17–18 px
(Newsreader ma niższe małe litery niż typowy krój systemowy). Odznaczenie
opcji przywraca kroje Avady.

Jeśli wolisz zrobić to ręcznie w Avadzie: *Avada → Options → Typography →
Custom Fonts*, wgraj pliki z `assets/fonts/` i wybierz je w Global
Typography. Plików jest jednak kilka (zakresy znaków), więc opcja we
wtyczce jest prostsza.

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
  `piodesign_cache_ttl`.
- Zdjęcia mają `srcset` i `loading="lazy"` (poza głównym zdjęciem).
- Lista zdjęć z galerii wpisu jest liczona raz i trzymana do zmiany wpisu.
- JS ok. 10 KB (bez kompresji), bez jQuery.

## Filtry dla programisty

| Filtr | Do czego |
|---|---|
| `piodesign_posts_query` | Argumenty `WP_Query` dla aktualności. |
| `piodesign_post_gallery_ids` | Lista ID zdjęć z galerii wpisu. |
| `piodesign_generic_categories` | Slugi kategorii pomijanych przy wyborze etykiety. |
| `piodesign_category_color` | Kolor kategorii. |
| `piodesign_template` | Ścieżka szablonu (możesz podmienić dowolny plik z `templates/`). |
| `piodesign_load_fonts` | `false` wyłącza ładowanie czcionek przez wtyczkę. |
| `piodesign_cache_ttl`, `piodesign_tec_list`, `piodesign_tec_single`, `piodesign_news_archive` | Opisane wyżej. |

## Style i Avada

Style edytujesz w `assets/src/piodesign.css`, a potem uruchamiasz:

```bash
php tools/build-css.php
```

Skrypt zapisuje `assets/piodesign.css`, w którym każdy selektor wtyczki
dostaje powtórzoną pierwszą klasę (`.pio-ev__title` →
`.pio-ev__title.pio-ev__title`). Dzięki temu reguły Avady typu
`.post-content h2 { font-family… }` czy `h3 { margin-top… }` nie nadpisują
nagłówków wtyczki. Bez tego nagłówki dostawały krój, kolor i marginesy z
Avady.

## Struktura

```
piodesign/
├── piodesign.php              shortcode'y, zasoby, cache
├── includes/core.php          daty po polsku, rok liturgiczny, logika wydarzeń, kadrowanie, paginacja
├── includes/wp-data.php       zapytania WP/TEC → dane dla szablonów
├── includes/tec.php           integracja z The Events Calendar
├── includes/archive.php       archiwum aktualności
├── includes/settings.php      Ustawienia → PioDesign, czcionki na całej stronie
├── templates/                 szablony HTML (news, events, news-archive, single-event, partials/…)
├── tec/                       szablony podpinane do TEC (lista, pojedyncze wydarzenie)
├── wp/news-archive.php        szablon motywu dla archiwum (nagłówek i stopka Avady)
├── assets/src/piodesign.css   źródło stylów
├── assets/piodesign.css       style po zbudowaniu (tools/build-css.php)
├── assets/piodesign.js        interakcje
├── assets/piodesign-fonts.css + assets/fonts/   czcionki (WOFF2, OFL)
└── tools/build-css.php        budowanie stylów
```
