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

Wymaga PHP 7.4+ z GD (WebP) i programu `curl`.
