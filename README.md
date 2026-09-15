# CoTheme

Ett minimalt WordPress-tema från Reklam & Co, byggt för Beaver Builder. Temat är en ren grund utan onödig kod: global typografi, färgvariabler och stöd för BB.

## Innehåll

| Mapp | Beskrivning |
|---|---|
| `cotheme/` | Huvudtemat |
| `cotheme-child/` | Child-tema där kundspecifika anpassningar läggs |

## Funktioner

- Stöd för Beaver Builder Themer (headers, footers och parts)
- Varumärkesfärger och typografi ställs in i Customizern och skrivs ut som CSS-variabler
- Färgerna och typsnitten syns direkt i BB:s färg- och typsnittsväljare
- Google Fonts, Adobe Fonts, egna uppladdade typsnitt och WP:s Font Library
- Sida med stilguide, sidlayoutinställningar och prestandaoptimeringar
- Stöd för WooCommerce
- Översättning till norska (`nb_NO`) i `cotheme/languages/`

## Krav

- WordPress 6.0 eller senare
- PHP 8.0 eller senare
- Beaver Builder (rekommenderas)

## Installation

1. Ladda ner `cotheme.zip` från den senaste versionen under [Releases](../../releases).
2. Gå till **Utseende → Teman → Lägg till nytt → Ladda upp tema** i WordPress och ladda upp zip-filen.
3. Installera `cotheme-child.zip` på samma sätt och aktivera child-temat.

> **Obs!** Använd inte GitHubs knapp *Code → Download ZIP*. Den ger mappnamnet `cotheme-main`, och då hittar child-temat inte sitt huvudtema (`Template: cotheme`).

### Uppdatera en befintlig sajt

Ladda upp den nya `cotheme.zip` på samma sätt. WordPress frågar om du vill ersätta det nuvarande temat, välj **Ersätt nuvarande med uppladdat**. Child-temat och dina inställningar i Customizern påverkas inte.

## Utveckling

- Följ WordPress Coding Standards och indentera PHP med tabbar.
- Höj versionsnumret på **båda** ställena inför en ny version: `Version:` i `cotheme/style.css` och `COTHEME_VERSION` i `cotheme/functions.php`.
- Skapa en zip där mappen heter `cotheme/` och publicera den som en ny release:

```bash
zip -r cotheme.zip cotheme -x "*.DS_Store"
gh release create v1.2.1 cotheme.zip --title "CoTheme 1.2.1"
```

## Licens

GNU General Public License v2 eller senare.
