# parafiapio.pl – moduły WordPress

## `piodesign/` – wtyczka „Parafia: PioDesign”

Nowy wygląd aktualności i wydarzeń na stronie głównej oraz widoków
The Events Calendar (pojedyncze wydarzenie, lista). Instalacja i opis:
[piodesign/README.md](piodesign/README.md).

## `preview/`

Podgląd bez WordPressa: te same szablony PHP wtyczki, wyrenderowane na
prawdziwych wpisach i wydarzeniach z parafiapio.pl, do jednego pliku HTML
z osadzonymi zdjęciami.

```bash
python3 preview/fetch.py     # pobiera aktualne wpisy i wydarzenia do preview/data.json
php preview/build.php        # tworzy preview/dist/index.html
```

Wymaga PHP 7.4+ z GD (WebP) i programu `curl`. Czcionki w `preview/fonts/`
udają kroje ustawione w Avadzie (Global Options).

## Instrukcje projektowe dla Claude (`.claude/skills/parafia-frontend/`)

Skill z systemem projektowym tych wtyczek: kolory, typografia, komponenty,
pułapki Avady i The Events Calendar, sposób pracy i skrypty testowe
(lokalny WordPress, zrzuty ekranu, sprawdzanie linków). Claude wczytuje go
sam w każdej sesji na tym repozytorium (`CLAUDE.md` na niego wskazuje) i
dopisuje do niego wnioski z kolejnych poprawek.

Inne repozytoria z wtyczkami parafii – jedna z dróg:

1. skopiuj folder `.claude/skills/parafia-frontend/` do `.claude/skills/`
   tamtego repozytorium (działa od razu w każdej sesji), albo
2. na początku sesji napisz: „użyj skilla parafia-frontend z repozytorium
   pablo7890/arduinoframework” – Claude dołączy to repozytorium i przeczyta
   instrukcje, albo
3. wgraj `parafia-frontend.zip` w claude.ai → Ustawienia → Możliwości →
   Umiejętności (Skills), żeby był dostępny także w zwykłych czatach.

Wzorcem pozostaje wersja w tym repozytorium – tu jest aktualizowana.
