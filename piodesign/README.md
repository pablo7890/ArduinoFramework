# Parafia: PioDesign

Wersja 1.3 · autor: cruzLabs

Wtyczka WordPress dla parafiapio.pl (Avada + The Events Calendar, wersja
darmowa): nowy układ strony głównej, archiwum aktualności, widoki wydarzeń,
sakramenty, cytaty św. Ojca Pio, liturgia dnia i sekcja informacji przed
stopką. Treść pisze się krojem z Avady, a tytuły zwężoną, pogrubioną
Bricolage Grotesque dołączoną do wtyczki (można to wyłączyć).

Co jest w środku:

| Widok | Jak się pojawia |
|---|---|
| **Aktualności** (12–15 wpisów, układ „gazetowy”) | shortcode `[pio_aktualnosci]` |
| **Nadchodzące wydarzenia** (oś 5 tygodni + 8–10 wydarzeń) | shortcode `[pio_wydarzenia]` |
| **Pojedyncze wydarzenie** (bilet z odliczaniem, dodaj do kalendarza) | automatycznie, podmienia szablon TEC; w Avada Layouts shortcode `[pio_wydarzenie]` |
| **Kalendarz parafii**: lista, miesiąc, dzień, minione, kategorie, wyszukiwanie | automatycznie, wtyczka rysuje całe widoki TEC |
| **Pojedynczy wpis** (nagłówek, zdjęcie 16:9, boczny pasek z datą i udostępnianiem, „Czytaj także”) | automatycznie, podmienia szablon wpisu; w Avada Layouts shortcode `[pio_wpis]` |
| **Archiwum aktualności** (strona „Aktualności”, kategorie, tagi, miesiące, wyszukiwanie we wpisach) | automatycznie, albo shortcode `[pio_archiwum]` |
| **Sakramenty** (animowane slajdy) | shortcode `[pio_sakramenty]` |
| **Cytaty św. Ojca Pio** (animowane slajdy) | shortcode `[pio_cytaty]` |
| **Liturgia dnia** (z wtyczki „Parafia: Kalendarz liturgiczny”) | shortcode `[pio_liturgia]` |
| **Informacje przed stopką** (Msze, kancelaria, kontakt, konto, mapa, strony zaprzyjaźnione) | shortcode `[pio_informacje]` |

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
3. Na stronie głównej w Avada Builderze dodaj kontenery z elementem
   **Code Block** (albo **Text Block**). Proponowany układ:

   | Kontener | Kolumny | Shortcode |
   |---|---|---|
   | 1 | 1/1 | `[pio_aktualnosci count="15"]` |
   | 2 | 1/1 | `[pio_wydarzenia count="10"]` |
   | 3 | 1/1 | `[pio_liturgia]` |
   | 4 | 2/3 + 1/3 | `[pio_sakramenty]` · `[pio_cytaty]` |
   | 5 (przed stopką) | 1/1 | `[pio_informacje]` |

   Kontener: szerokość „Site Width”, bez dodatkowych paddingów po bokach.
   Sakramenty i cytaty dopasowują się do szerokości kolumny (container
   queries), więc działają też na całą szerokość albo jedna pod drugą.
   `[pio_informacje]` możesz też wstawić do stopki w *Avada → Layouts*,
   wtedy pojawi się na każdej stronie.
4. *Ustawienia → PioDesign*: zakładki **Aktualności, Archiwum i wpis,
   Wydarzenia, Liturgia, Sakramenty i cytaty, Informacje, Ogólne**. Tam
   ustawisz domyślne nagłówki, liczby wpisów, długości zajawek (wpis główny,
   boczne, karty), widoczność elementów, cytaty Ojca Pio (dodawane i usuwane
   przyciskami), źródło godzin Mszy, kontakt, konto i strony zaprzyjaźnione.
   Parametry wpisane w shortcode mają pierwszeństwo przed ustawieniami.
5. Widoki TEC (lista i pojedyncze wydarzenie) oraz archiwum aktualności
   zmieniają się same.

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

`[pio_sakramenty]`

| Parametr | Domyślnie | Opis |
|---|---|---|
| `parent` | `sakramenty-i-sakramentalia` | Slug strony nadrzędnej; slajdy to jej podstrony w kolejności z *Atrybuty strony → Kolejność*. |
| `ids` | – | Zamiast tego konkretne strony, np. `ids="5313,5413"`. |
| `exclude` | – | Slugi do pominięcia, np. `exclude="pogrzeb"`. |
| `autoplay` | `7` | Sekundy na slajd, `0` wyłącza autoodtwarzanie. |
| `title` / `kicker` | `Droga wiary` / `Sakramenty i sakramentalia` | Duży nagłówek / mały napis nad nim. |

Tekst slajdu to **zajawka strony** (wtyczka włącza pole „Zajawka” dla
stron). Zdjęcie to **obrazek wyróżniający** strony. Dopóki ich nie ma,
wtyczka bierze teksty i zdjęcia z obecnego slajdera na stronie głównej.
Spowiedź nie ma tam zdjęcia, więc pokazuje się z ozdobną literą – warto
dodać jej obrazek wyróżniający.

`[pio_cytaty]`

| Parametr | Domyślnie | Opis |
|---|---|---|
| `title` | `Słowa na dziś` | Napis u góry karty. |
| `autor` | `św. Ojciec Pio` | Podpis. |
| `zdjecie` | – | ID obrazka z biblioteki albo adres URL (okrągły portret przy podpisie). |
| `autoplay` | `9` | Sekundy na cytat, `0` wyłącza. |

Cytaty edytujesz w *Ustawienia → PioDesign* (jeden w wierszu) albo
wpisujesz między znacznikami: `[pio_cytaty]Pierwszy cytat
Drugi cytat[/pio_cytaty]`.

`[pio_liturgia]`

| Parametr | Domyślnie | Opis |
|---|---|---|
| `dni` | `7` | Ile dni pokazać na pasku (od dziś, 1–14). |
| `title` / `kicker` | `Liturgia dnia` / `Kalendarz liturgiczny` | Nagłówki. |

Dane pochodzą z filtrów `kalendarz_liturgiczny_days` i
`kalendarz_liturgiczny_day`: tytuł dnia, ranga, kolor (`color_hex`),
czytania (sigla – wtyczka sama podpisuje I czytanie, Psalm, II czytanie i
Ewangelię), wspomnienia dowolne, święta parafialne i okolicznościowe. Gdy
rok nie jest zatwierdzony, pokazuje się wyliczony okres liturgiczny.

Pasek z lewej strony karty to jednolity kolor liturgiczny dnia z cieniem.
Obok czytań jest odtwarzacz Spotify z rozważaniem Ewangelii (podcast
parafii; adres i podpis w zakładce *Liturgia*, parametr `podcast=""`
wyłącza). Tytuł najnowszego odcinka pobierany jest raz dziennie.

`[pio_informacje]`: parametry `title` (`Zapraszamy`) i `kicker`. Treści z
*Ustawienia → PioDesign*. Na żywo (także na stronach z pamięci podręcznej):

- **najbliższa Msza** – „dziś 18:00 · za 1 godz. 12 min”, liczona według
  czasu w Polsce. Źródło (zakładka *Informacje*): **strona intencji
  mszalnych** (domyślnie `/intencje-mszalne/`, do wybrania z listy) –
  wtyczka czyta godziny z bieżącego i następnego tygodnia (co godzinę),
  więc liczą się też Msze dodatkowe i zmiany; jeśli strony albo wtyczki
  intencji nie ma, używa godzin wpisanych w ustawieniach (w Adwencie z
  uwagami typu „w adwencie 6:30”),
- **kancelaria** – „Otwarte teraz · do 17:45” albo „Zamknięte · otwieramy w
  środę o 9:00”; z opcją „nieczynne w I piątek miesiąca”,
- kopiowanie numeru konta, telefonu i e-maila jednym kliknięciem,
- mapa Google ładowana dopiero po kliknięciu (żadnych ciasteczek Google przy
  wejściu na stronę).

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

- **Kalendarz (lista, miesiąc, dzień):** wtyczka rysuje całe widoki TEC sama
  (filtr `tribe_events_views_v2_bootstrap_pre_get_view_html`), więc nie
  pojawia się nic z domyślnego wyglądu – ani pasek wyszukiwania, ani siatka
  miesiąca, ani komunikaty TEC. Adresy, kategorie i ustawienia TEC zostają:
  - `/wydarzenia/` i `/wydarzenia/lista/` – nadchodzące, po 12 na stronie
    (*Ustawienia → PioDesign → Wydarzenia*), pogrupowane po miesiącach, z osią
    tygodni na pierwszej stronie;
  - `/wydarzenia/lista/?eventDisplay=past` – minione, od najnowszych;
  - `/wydarzenia/miesiac/` i `/wydarzenia/2026-11/` – siatka miesiąca:
    wydarzenia wielodniowe jako paski przez cały tydzień, pozostałe w dniach
    (najwyżej 3, dalej „+2 więcej” → widok dnia), kolor liturgiczny i nazwa
    święta w kratce (z wtyczki „Parafia: Kalendarz liturgiczny”), karta z
    opisem po najechaniu; na telefonie mała siatka z kropkami i lista dni pod
    nią (dotknięcie dnia przewija do niego);
  - `/wydarzenia/2026-10-05/`, `/wydarzenia/dzisiaj/` – jeden dzień z
    przejściem do poprzedniego / następnego;
  - `/wydarzenia/kategoria/…/` – każdy z tych widoków dla jednej kategorii
    (kategorie jako zakładki nad kalendarzem);
  - `?tribe-bar-search=…` – wyszukiwarka w nagłówku, w liście i w miesiącu.

  Miesiące i dni bez wydarzeń (także daleko w przyszłości) otwierają się
  normalnie z informacją o najbliższym wydarzeniu – TEC od wersji 6.16 dawał
  tam błąd 404; wtyczka wysyła zamiast tego `noindex`. Przycisk
  „Subskrybuj” (Google, iPhone/Outlook, plik .ics) włączysz w zakładce
  *Wydarzenia*. Wyłączenie: `add_filter( 'piodesign_tec_views', '__return_false' );`
  (wtedy lista TEC wraca z naszymi wierszami).
- **Pojedyncze wydarzenie:** filtr `tribe_events_template` podmienia
  `single-event.php`. Widok ma szerokość strony (Site Width z Avady; w
  *Ustawienia → PioDesign → Wydarzenia* można wybrać całą szerokość obszaru
  treści). Bilet z datą jest przypięty przy przewijaniu tuż pod przyklejonym
  menu Avady (wtyczka mierzy jego wysokość, także gdy menu się zmniejsza i
  gdy widać pasek administratora). Boczny panel Avady
  „Szczegóły” (opcja *Avada → Options → The Events Calendar → Event Meta
  Layout: Sidebar*) jest wyłączany na tej stronie, bo bilet pokazuje te same
  dane. Możesz też ustawić tę opcję na *Disabled* na stałe.
- Działa dla wydarzeń w klasycznym edytorze (tak jak teraz). Jeżeli w
  *Wydarzenia → Ustawienia* włączysz edytor blokowy dla wydarzeń, TEC użyje
  innego szablonu.
- **Avada Layouts:** jeżeli w *Avada → Layouts* przypiszesz układ do
  pojedynczych wydarzeń (albo wpisów), wtyczka go nie zastępuje. W sekcji
  treści (Content Layout Section) wstaw kontener **Site Width** z elementem
  **Code Block** i shortcode `[pio_wydarzenie]` (dla wpisów `[pio_wpis]`) –
  bez parametrów pokazuje aktualnie oglądane wydarzenie / wpis; `id="123"`
  pokazuje konkretne. Nad i pod nim możesz układać własne sekcje Avady.
- Wyłączenie którejkolwiek części:

  ```php
  add_filter( 'piodesign_tec_list', '__return_false' );     // lista wydarzeń
  add_filter( 'piodesign_tec_single', '__return_false' );   // pojedyncze wydarzenie
  add_filter( 'piodesign_news_archive', '__return_false' ); // archiwum aktualności
  ```

## Archiwum aktualności

Obejmuje:

- stronę **Aktualności** – wybraną w *Ustawienia → PioDesign*, a domyślnie
  stronę o adresie `/aktualnosci/` (u Was to zwykła strona zbudowana w
  Avadzie; wtyczka pokazuje na niej archiwum zamiast jej treści, a kolejne
  strony mają adresy `/aktualnosci/page/2/`),
- stronę wpisów z *Ustawienia → Czytanie*, jeśli jest ustawiona,
- kategorie, tagi, archiwa miesięczne i roczne,
- wyszukiwanie ograniczone do wpisów (formularz w archiwum dodaje
  `post_type=post`).

Archiwum możesz też wstawić w dowolne miejsce shortcode'em
`[pio_archiwum]` (parametry `title`, `kicker`, `category`).

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

## Pojedynczy wpis

Wpisy (post) dostają widok w stylu wydarzenia: okruszki, kategoria i data,
duży tytuł z zajawką, zdjęcie 16:9, obok treści przyklejony pasek (kafelek
daty, czas czytania, liczba zdjęć, udostępnij na Facebooku / kopiuj link),
tagi, poprzedni / następny wpis i „Czytaj także” (z tej samej kategorii).
Włącza się w zakładce *Archiwum i wpis* (tam też liczba powiązanych wpisów
i przyciski udostępniania). Galerie i bloki z treści działają bez zmian.

## Czcionki

Treść, zajawki i opisy biorą krój treści z *Avada → Options → Typography*.
Tytuły domyślnie używają **Bricolage Grotesque dołączonej do wtyczki**
(zmienna czcionka z osią szerokości, ok. 60 kB, bez Google Fonts), bo Avada
ładuje wersję statyczną, bez zwężenia i z mniejszą liczbą grubości – stąd
różnica między podglądem a stroną. W zakładce *Ogólne* możesz przełączyć
tytuły na krój nagłówków z Avady.

Rozmiary, grubości, szerokość i odstępy liter są dobrane tak, żeby dobrze
wyglądały zarówno z **Bricolage Grotesque** (krój ma oś szerokości, więc
tytuły są zwężone, jak w projekcie), jak i z szeryfowym **Iowan Old Style**
(tam zwężenie po prostu się nie stosuje, a tytuły są szersze i spokojniejsze).
W podglądzie można przełączać jeden i drugi wariant.

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
| `piodesign_archive_query` | Argumenty `WP_Query` archiwum na stronie „Aktualności” i w `[pio_archiwum]`. |
| `piodesign_event_category_color` | Kolor kategorii wydarzeń (domyślnie stały kolor z palety dla sluga). |
| `piodesign_cache_ttl`, `piodesign_tec_views`, `piodesign_tec_list`, `piodesign_tec_single`, `piodesign_news_archive` | Opisane wyżej. |

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
├── includes/tec.php           integracja z The Events Calendar (pojedyncze wydarzenie, wiersze listy)
├── includes/calendar.php      kalendarz: lista, miesiąc, dzień, kategorie, wyszukiwanie
├── includes/single.php        pojedynczy wpis, [pio_wpis], [pio_wydarzenie]
├── includes/archive.php       archiwum aktualności
├── includes/settings.php      Ustawienia → PioDesign
├── includes/sections.php      [pio_sakramenty] [pio_cytaty] [pio_informacje] [pio_liturgia]
├── includes/sections-core.php godziny Mszy, kancelaria, czytania, liturgia (bez WordPressa)
├── templates/                 szablony HTML (news, events, news-archive, sacraments, quotes, liturgy, info, single-event, partials/…)
├── tec/                       szablony podpinane do TEC (lista, pojedyncze wydarzenie)
├── wp/news-archive.php        szablon motywu dla archiwum (nagłówek i stopka Avady)
├── assets/src/piodesign.css   źródło stylów
├── assets/piodesign.css       style po zbudowaniu (tools/build-css.php)
├── assets/piodesign.js        interakcje
└── tools/build-css.php        budowanie stylów
```

## Zmiany

**1.3**

- Własne widoki kalendarza: lista, **miesiąc** (paski wydarzeń
  wielodniowych, kolor liturgiczny, karty po najechaniu, wersja na telefon z
  listą dni), dzień, minione, kategorie jako zakładki, wyszukiwanie w każdym
  widoku. Nic z domyślnego wyglądu TEC się nie ładuje.
- Przełącznik Lista / Miesiąc: napis był niewidoczny po odwiedzeniu strony
  (Avada koloruje odwiedzone linki w TEC) – poprawione.
- Pojedyncze wydarzenie i wpis: opcja szerokości (domyślnie Site Width),
  shortcode'y `[pio_wydarzenie]` i `[pio_wpis]` do Avada Layouts; przypięty
  bilet / pasek nie chowa się pod przyklejonym menu.
- Archiwum aktualności obejmuje też archiwa dzienne i autorów; kategoria
  wydarzenia na bilecie prowadzi do kalendarza tej kategorii.
- Brak błędów 404 dla miesięcy i dni bez wydarzeń.

**1.2**

- Licznik najbliższej Mszy czyta godziny ze strony intencji mszalnych
  (z wyborem strony i zapasowymi godzinami z ustawień).
- Ustawienia w zakładkach: nagłówki, liczby wpisów, długości zajawek,
  widoczność elementów, edytor cytatów z portretem.
- Nowy widok pojedynczego wpisu.
- Kalendarz: własna wyszukiwarka i przełącznik widoków, bez pozostałości
  TEC („Zasubskrybuj kalendarz”, pasek wyszukiwania).
- Liturgia: jednolity pasek koloru z cieniem i odtwarzacz rozważania
  Ewangelii ze Spotify.
- Sakramenty: „Sakramenty i sakramentalia” / „Droga wiary”.
- Strona główna: mniej pustego miejsca pod nagłówkami, bez linku
  „Wszystkie wpisy”.
- `[pio_archiwum]`: wyszukiwarka nie wychodzi poza kontener.
- Tytuły zwężoną, pogrubioną Bricolage Grotesque, jak w podglądzie.

**1.1**

- Archiwum aktualności działa na zwykłej stronie „Aktualności”; nowy
  shortcode `[pio_archiwum]`.
- Nowe sekcje: `[pio_sakramenty]`, `[pio_cytaty]`, `[pio_liturgia]`,
  `[pio_informacje]` i ich ustawienia.
- Kroje z Avady zamiast czcionek wtyczki; typografia dostrojona pod
  Bricolage Grotesque i Iowan Old Style.
- Pojedyncze wydarzenie: cień „biletu” nie miga już przy każdej sekundzie
  odliczania.
- Wersje plików CSS/JS zawierają datę modyfikacji, więc przeglądarki nie
  trzymają starych stylów po aktualizacji.
