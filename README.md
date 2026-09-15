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
3. Gå till **Utseende → Skapa kundtema** och skapa ett child-tema för kunden (se nedan).

> **Obs!** Använd inte GitHubs knapp *Code → Download ZIP*. Den ger mappnamnet `cotheme-main`, och då hittar child-temat inte sitt huvudtema (`Template: cotheme`).

## Kundtema

Varje kund får ett eget child-tema, till exempel `smf/` med temanamnet "SMF". Kundens egen CSS och egna funktioner läggs där, medan CoTheme uppdateras centralt utan att skriva över dem.

Från version 1.4.0 skapas kundtemat under **Utseende → Skapa kundtema**:

- **Kundens namn** blir temats namn och **mappnamnet** föreslås automatiskt.
- **Loggan** läggs på vald bakgrundsfärg och blir temats bild i temalistan.
- **Ta med inställningarna från det aktiva temat** kopierar färger, typsnitt, logga, menyplaceringar och Ytterligare CSS. En sajt som kör "CoTheme Child" kan därför byta till ett eget kundtema utan att tappa något.

Tänk på:

- **Döp aldrig om mappen för ett aktivt kundtema.** Customizer-inställningarna är kopplade till mappnamnet och försvinner. Skapa i stället ett nytt kundtema med *Ta med inställningarna* ikryssat.
- Kundteman hör inte hemma i det här repot, utan i kundens eget projekt.
- Sidan kräver att WordPress får skriva till `wp-content/themes`. På sajter med `DISALLOW_FILE_MODS` visas en varning, och då installeras `cotheme-child.zip` från Releases för hand. Ändra `Theme Name` och `Text Domain` i `style.css` och döp om mappen **innan** temat aktiveras.

## Uppdateringar

Från version 1.3.0 kollar temat själv efter nya releases här på GitHub, ungefär två gånger per dygn. När en ny version finns visas **Uppdatering finns** under *Utseende → Teman* och *Adminpanel → Uppdateringar*, och man uppdaterar med ett klick. Child-temat och inställningarna i Customizern påverkas inte.

Sajter som kör 1.2.1 eller äldre saknar uppdateringskollen. De behöver uppdateras till 1.3.0 **en gång för hand**: ladda upp `cotheme.zip` och välj **Ersätt nuvarande med uppladdat**. Därefter sköts uppdateringarna automatiskt.

Uppdateringskollen bygger på [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) (`cotheme/inc/lib/`, MIT-licens) och ställs in i `cotheme/inc/updater.php`.

## Släppa en ny version

1. Höj versionsnumret på **båda** ställena: `Version:` i `cotheme/style.css` och `COTHEME_VERSION` i `cotheme/functions.php`.
2. Committa och pusha till `main`.
3. Bygg zippen (mappen i zippen måste heta `cotheme/`) och skapa en release med taggen `v` + versionsnumret:

```bash
zip -r cotheme.zip cotheme -x "*.DS_Store"
gh release create v1.3.0 cotheme.zip --title "CoTheme 1.3.0" --notes "Vad som är nytt …"
```

Tänk på:

- Filen **måste** heta exakt `cotheme.zip`. Saknas den visar sajterna ingen uppdatering alls. GitHubs automatiska källkods-zip används aldrig, eftersom den har fel mappstruktur.
- Sajterna ser bara den release som är markerad som *Latest*. Utkast och pre-releases ignoreras, så de går bra att använda för tester.
- Det du skriver i release-anteckningarna visas som ändringslogg i WordPress.
- En release når **alla** sajter som kör temat. Testa på en stagingsajt innan du publicerar.

## Licens

Copyright © 2026 Reklam & Co. Licensierat under GNU General Public License v2 eller senare, se [LICENSE](LICENSE).

Det medföljande biblioteket Plugin Update Checker är skrivet av Jānis Elsts och har MIT-licens.

---

CoTheme utvecklas och underhålls av [Reklam & Co](https://reklamco.se).
