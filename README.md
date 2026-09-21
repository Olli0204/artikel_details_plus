# Artikeldetails Plus

JTL-Shop 5 Plugin, das die Artikeldetailseite und die Artikellistenansicht um visuelle Bauteile und ein Kunden-Feedback-Formular erweitert — ohne dass das Shop-Template angefasst werden muss.

**Autor:** Oliver Kamps
**Version:** 0.8.0
**Kompatibel mit:** JTL-Shop 5.5.1 – 5.8.0
**Voraussetzung:** PHP 8.1+

---

## Funktionen

### 1. Snowboard-Specs (Fahreigenschaften, Körpergewicht, Fahrlevel, Dimensionen)
Auf der Artikeldetailseite werden im Beschreibungs-Tab die Snowboard-Eigenschaften visualisiert. Die Werte werden in `Bootstrap::assignSnowboardSpecs()` aus den **Funktionsattributen** des Artikels gelesen, ersatzweise vom Vaterartikel (Attributnamen kleingeschrieben, Dezimalkomma erlaubt), und den Templates als fertige Arrays übergeben.

| Bereich | Darstellung | Funktionsattribute |
|---|---|---|
| Fahreigenschaften | interaktives **Pentagon-SVG-Diagramm** (`ecm_polygon_svg.js`), Werte 0–10; erscheint ab drei vorhandenen Werten | `carving`, `jib`, `powder`, `all_mountain`, `jump` |
| Körpergewicht | Skalenleiste in kg | `koerpergewicht_ab`, `koerpergewicht_bis` |
| Fahrlevel | Leiste Beginner / Advanced / Professional | `fahrlevel_ab`, `fahrlevel_bis` |
| Profil | **Seitenansicht** (Camber, Rocker, Flat, Hybrid Camber, Hybrid Rocker, Flat Rocker) als vollbreite Karte unter den Spalten, vertikal übertrieben, mit Bodenlinie | `profil` (Vorrang), sonst erkannt aus `form`, ersatzweise `shape` |
| Flex | **Skala 1–10** aus zehn Segmenten mit fünf Zonen (Soft, Medium-Soft, Medium, Medium-Stiff, Stiff) in eigener Karte; Einzelwert oder Bereich, halbe Werte als halbes Segment | `flex` oder `flex_ab`, `flex_bis` (1–10, Dezimal erlaubt) |
| Dimensionen | Tabelle Länge / Form / Shape / Waist / Nose / Tail / Inserts plus **maßstäbliche SVG-Board-Skizze** (Draufsicht, Nose links, Tail rechts) mit Längen- und Breitenbemaßung, Twin- oder Directional-Umriss und Inserts (Lochmuster oder Channel); die Skizze erscheint, wenn `nose`, `waist` und `tail` numerisch sind | `form`, `shape`, `waist`, `nose`, `tail` (mm), `laenge` (cm), `inserts`, optional `outline`, `stance`, `setback` |

Fahreigenschaften-Diagramm und Dimensionen lassen sich einzeln abschalten; der Schalter „Merkmalwert-Anzeige aktiv" bleibt der Hauptschalter für den gesamten Bereich. Die Überschriften „Fahreigenschaften" und „Dimensionen" sind Sprachvariablen. Dieser Bereich ersetzt das frühere Plugin `snowboard_specs` (September 2026 integriert).

### 2. Merkmalbilder in der Artikelliste
Unterhalb jeder Produktbox in Kategorie- und Suchergebnislisten werden Bilder ausgewählter Merkmalwerte angezeigt — z. B. Technologie- oder Eigenschaftsbadges. In den Plugin-Einstellungen wird ausgewählt, welche Merkmale (nur die mit hinterlegten Bildern) angezeigt werden sollen.

### 3. Lagerbestandsanzeige
Sobald der Lagerbestand unter einen konfigurierbaren Schwellenwert fällt, erscheint ein **schlanker Fortschrittsbalken** mit der Restmenge. Der Balken füllt sich proportional zum Verhältnis Bestand/Schwellwert. Farbe ist im Backend per Color-Picker einstellbar; sie wird als CSS-Variable (`--adp-stock-color`) an das Element übergeben, das Styling selbst liegt im Stylesheet.

### 4. Countdown (umgezogen)
Der Countdown auf der Artikeldetailseite wird seit 0.3.0 von der **Countdown-Verwaltung in Startseite Plus** (Plugin-Tab „Countdowns“, Option „Auf Artikeldetailseiten anzeigen“) bereitgestellt. Dort lassen sich mehrere Countdowns für verschiedene Aktionen pflegen, die auch der Aktions-Banner nutzt.

### 5. „Günstiger gesehen?"-Formular
Ein Bootstrap-Modal mit Formular, in dem Kunden einen günstigeren Wettbewerberpreis melden können. Pflichtfelder: E-Mail und URL zum günstigeren Angebot, optional eine Nachricht.

Nach dem Absenden wird eine Mail an den **Shop-Master-Absender** (`email_master_absender` aus den Shop-Einstellungen) verschickt; Reply-To ist die Kundenadresse, sodass direkt geantwortet werden kann.

Das Formular nutzt Post-Redirect-Get (PRG): nach dem Submit wird per 303 zurück auf die Artikelseite umgeleitet — das verhindert versehentliche Mehrfachabsendung beim Reload.

**Schutzmechanismen:**
- CSRF-Token-Validierung (`Form::validateToken()`)
- Honeypot-Feld gegen Bots (`Form::honeypotWasFilledOut()`)
- E-Mail-Validierung via `FILTER_VALIDATE_EMAIL`, URL via `FILTER_VALIDATE_URL`

Erfolgs- und Fehlermeldungen werden als Alerts oberhalb des Formulars angezeigt; das Modal öffnet sich automatisch erneut, damit der Kunde das Feedback sieht.

---

## Installation

1. Plugin-Ordner in das Verzeichnis `plugins/` des JTL-Shops kopieren (z. B. via Git: `git clone https://github.com/OkampsUni/artikel_details_plus.git`)
2. Im Backend unter **Plugin-Manager → Verfügbar** das Plugin installieren und aktivieren
3. Unter **Plugins → Artikeldetails Plus** die einzelnen Funktionsbereiche konfigurieren
4. Bei Bedarf das E-Mail-Template **„Günstiger gesehen Benachrichtigung"** unter **Inhalte → E-Mail-Vorlagen** anpassen

---

## Konfiguration

Die Einstellungen sind in vier Tabs gegliedert. Alle „Aktiv"-Einstellungen sind Checkboxen (seit 0.2.2; gespeichert wird `on` bzw. leer).

| Tab | Einstellung | Typ | Beschreibung |
|---|---|---|---|
| Fahreigenschaften | Merkmalwert-Anzeige aktiv | Checkbox | Schaltet Pentagon-Diagramm und Gewichtsleiste im Beschreibungs-Tab ein |
| Fahreigenschaften | Fahrlevelanzeige aktiv | Checkbox | Schaltet die Beginner/Advanced/Professional-Leiste ein |
| Fahreigenschaften | Fahreigenschaften-Diagramm anzeigen | Checkbox (Default an) | Pentagon-Diagramm der Fahreigenschaften |
| Fahreigenschaften | Dimensionen anzeigen | Checkbox (Default an) | Tabelle und maßstäbliche Board-Skizze (Länge, Form/Shape, Waist/Nose/Tail, Inserts) |
| Merkmalbilder | Merkmalbilder Anzeige aktiv | Checkbox | Aktiviert Bilder unter den Artikelboxen in der Listenansicht |
| Merkmalbilder | Merkmalwerte mit Bildern | Mehrfachauswahl | Welche Merkmale (mit hinterlegten Bildern) angezeigt werden — dynamisch aus `tmerkmal` |
| Lagerbestandsanzeige | Lagerbestandsanzeige aktiv | Checkbox | Zeigt den Fortschrittsbalken bei niedrigem Bestand |
| Lagerbestandsanzeige | Nur bei Lagerbestand unter | Number (Default 10) | Schwellenwert, ab dem die Anzeige erscheint |
| Lagerbestandsanzeige | Farbe der Anzeige | Color (Default `#ffa54f`) | Farbe des Fortschrittsbalkens |
| Günstiger gesehen | Formular aktiv | Checkbox | Schaltet Button und Modal auf der Artikeldetailseite ein |

---

## Übersetzbare Texte

Alle frontend-relevanten Texte sind als Sprachvariablen hinterlegt und können unter **Inhalte → Sprachvariablen → Plugins → Artikeldetails Plus** angepasst werden:

| Variable | Default DE | Default EN |
|---|---|---|
| `artikel_details_plus_weight_title` | Empfohlenes Körpergewicht: | Suggested Weight: |
| `artikel_details_plus_level_title` | Fahrlevel: | Rider Skills: |
| `artikel_details_plus_flex_title` | Flex | Flex |
| `artikel_details_plus_flex_zone_soft` … `_stiff` | Soft, Medium-Soft, Medium, Medium-Stiff, Stiff | (gleich) |
| `artikel_details_plus_stock_text` | Nur noch %s Stück verfügbar! | Only %s pieces available! |
| `artikel_details_plus_specs_heading_characteristics` | Fahreigenschaften | Ride Characteristics |
| `artikel_details_plus_specs_heading_dimensions` | Dimensionen | Dimensions |
| `artikel_details_plus_specs_heading_profile` | Profil | Profile |
| `artikel_details_plus_form_button` | Günstiger gesehen? | Seen it cheaper? |
| `artikel_details_plus_cheaper_title` | Günstiger gesehen? | Seen it cheaper? |
| `artikel_details_plus_cheaper_success` | Vielen Dank! Wir haben Ihren Preishinweis erhalten… | Thank you! We have received your price tip… |
| `artikel_details_plus_cheaper_err_validation` | Bitte füllen Sie E-Mail-Adresse und Link korrekt aus. | Please enter a valid email address and link. |
| `artikel_details_plus_cheaper_err_csrf` | Ungültige Anfrage. Bitte laden Sie die Seite neu… | Invalid request. Please reload the page… |
| `artikel_details_plus_cheaper_err_general` | Beim Senden ist ein Fehler aufgetreten… | An error occurred while sending… |
| `artikel_details_plus_cheaper_label_email` | Ihre E-Mail-Adresse * | Your email address * |
| `artikel_details_plus_cheaper_label_url` | Link zum günstigeren Angebot * | Link to the cheaper offer * |
| `artikel_details_plus_cheaper_label_message` | Nachricht (optional) | Message (optional) |
| `artikel_details_plus_cheaper_submit` | Abschicken | Submit |

---

## Architektur

### Smarty-Block-Erweiterungen
Das Plugin hängt sich per `prepend` / `append` in vorhandene NOVA-Blöcke ein, ohne sie zu überschreiben:

| Block | Datei | Wirkung |
|---|---|---|
| `productdetails-details-stock` | `details.tpl` | Stylesheet-Einbindung + Lagerbestand-Balken (eigene `col col-12` unter dem Preis) |
| `productdetails-details-question-on-item` | `details.tpl` | „Günstiger gesehen"-Button neben NOVAs „Frage zum Artikel" |
| `tab-description-media-types`, `productdetails-tabs-card-description-content` | `tabs.tpl` | Zwei Spalten mit den Karten `characteristics.tpl` (Diagramm), `flex.tpl`, `fit.tpl` (Gewicht/Fahrlevel), `snowboard_values.tpl` (Dimensionen); darunter vollbreit `profile.tpl` (Seitenansicht) |
| `productdetails-popups` | `popups.tpl` | Modal mit Formular |
| `productlist-index-include-price` | `item_box.tpl` | Merkmalbilder unter Artikelboxen |

### Hooks
- `HOOK_ARTIKEL_PAGE` (registriert in `Bootstrap.php`): verarbeitet POST-Submissions des „Günstiger gesehen"-Formulars (PRG-Redirect) und befüllt die Smarty-Variablen für alle Bauteile: `assignSnowboardSpecs()` setzt `adpSpecsCharacteristics`, `adpSpecsDimensions`, `adpSpecsBoard`, `adpFrontendURL`; `assignDetailExtras()` setzt `adpStock`, `adpCheaperActive`, `adpWeight`, `adpLevel`. Die Templates rechnen nichts mehr selbst.

### Dynamische Optionsquelle
- `adminmenu/merkmalwerte.php`: SQL-Query über `tmerkmal`/`tmerkmalwert`, liefert nur Merkmale mit mindestens einem bebilderten Wert. Versorgt die Mehrfachauswahl „Merkmalwerte mit Bildern".

### Assets
- `frontend/js/ecm_polygon_svg.js`: JS-Klasse zur Berechnung und Darstellung des Pentagon-Radar-Diagramms (eigenständige ES6-Klasse, benötigt jQuery aus dem Template). Wird via `<script src>` in `svg_attributes.tpl` eingebunden.
- `frontend/css/artikel_details_plus.css`: komplettes Frontend-Design des Plugins (Lagerbestand, „Günstiger gesehen"-Zeile, Specs-Panels, Radar-Diagramm, Gewichts-/Fahrlevel-Leisten, Board-Skizze und Dimensionen-Tabelle); wird einmalig in `details.tpl` mit Versions-Parameter verlinkt. Akzentfarbe über `--primary`, Fallback `#FFA54F`; lokale Tokens `--adp-accent`, `--adp-border`, `--adp-surface`, `--adp-radius`.

### Design (seit 0.4.0)
Alle Bauteile teilen sich ein Design-System im Stylesheet — keine Inline-`<style>`-Blöcke und keine Farben im JavaScript mehr:

- **Panels:** Jeder Specs-Bereich sitzt in einer eigenen Karte (`.adp-panel`, 1px Rahmen, 6px Radius) mit kleiner Versal-Überschrift und Akzentstrich.
- **Raster:** `.adp-specs__grid` enthält zwei echte Spalten (`.adp-specs__col`, Flex-Column): links Fahreigenschaften + Flex, rechts Gewicht/Fahrlevel + Dimensionen; ab 768px nebeneinander, darunter gestapelt. Die Spalten sind als Grid-Zellen gleich hoch, die Fahreigenschaften-Karte wächst (`flex: 1 0 auto`) und verteilt die Resthöhe über und unter dem Diagramm — so enden beide Spalten bündig, egal wie lang die Dimensionen-Tabelle ist. Eine leere Spalte wird in `tabs.tpl` gar nicht ausgegeben, die verbleibende spannt dann über die volle Breite (`:only-child`).
- **Radar-Diagramm:** Gitter und Fläche werden über die Klassen `.adp-radar__grid`, `.adp-radar__area`, `.adp-radar__hit` und `.adp-radar__label` gestylt (Akzentfarbe statt Rot). Unter dem Diagramm steht eine Chip-Liste mit allen Werten, damit die Zahlen auch ohne Hover (Touch) sichtbar sind; beim Überfahren eines Sektors wird der passende Chip hervorgehoben.
- **Profil:** `profileType()` erkennt den Typ aus Freitext. Dreiteilige Notation `X/Y/X` (auch mit `-`) wird nach dem *mittleren* Element gelesen — es beschreibt den Bereich zwischen den Füßen: `Camber/Rocker/Camber` → Hybrid Rocker (Lib Tech C2, Nitro Gullwing), `Rocker/Camber/Rocker` → Hybrid Camber (Rome CamRock), `Rocker/Flat/Rocker` → Flat Rocker. Sonst Schlüsselwörter: „Flying V", „Hybrid Rocker" → Hybrid Rocker; „Hybrid Camber", „CamRock", „Directional Camber" → Hybrid Camber; „Flat"/„Zero" (+ „Rocker") → Flat (Rocker); „Rocker", „Reverse", „Banana" → Rocker; „Camber" → Camber. Unbekannte Texte (z. B. „3BT") zeichnen nichts. `buildProfileSketch()` erzeugt eine Polylinie aus 113 Stützpunkten über eine Höhenfunktion je Typ (Spitzen 26, Camber 12 Einheiten) — die Werte sind bewusst übertrieben, damit die Unterschiede auf den ersten Blick sichtbar sind.
- **Flex:** Eigene Karte unter den Fahreigenschaften: Titel, Kopfzeile mit Zone (fett) und Wert, zehn Segmente, fünf Zonenbeschriftungen. Zonennamen sind Sprachvariablen (`artikel_details_plus_flex_zone_*`).
- **Gewicht und Fahrlevel:** Pill-Leisten (`.adp-meter`) mit hellem Track, akzentfarbenem Bereich und der Spanne im Klartext neben der Überschrift statt im Tooltip.
- **Dimensionen:** Board-Skizze und Tabelle stehen per Container-Query nebeneinander, sobald das Panel breit genug ist.
- **„Günstiger gesehen?":** Pill-Button mit hellem Rahmen und Preisschild-Icon (`fa-tag`). Er sitzt in NOVAs Spalte `.question-on-item`, also in derselben Zeile wie „Frage zum Artikel" statt in einem eigenen Band darüber. Die Pill-Form hält die beiden Aktionen trotz gemeinsamer Zeile auseinander; Hover und Fokus färben Rahmen, Text und Icon im Akzent.
- **Positionen in NOVAs Preis-Row:** Preis, Lagerbalken und Lieferinfo liegen bei NOVA in einer gemeinsamen `.row`. Eigene Bauteile brauchen dort zwingend eine `col`-Klasse — ohne sie werden sie zum nackten Flex-Item, das auf Inhaltsbreite schrumpft, an den rechten Rand rutscht und den Preisblock schmaler macht.

---

## Update / Migration

Ab Version **0.1.1** sind die früheren Checkbox-Einstellungen auf **Selectbox (Ja/Nein)** umgestellt. `Migrations/Migration20260504120100.php` konvertiert beim Plugin-Update bestehende `'on'`-Werte automatisch zu `'Y'`; Einstellungen bleiben erhalten. (Die damalige Annahme, Checkboxen ließen sich im JTL-Core nicht abwählen, hat sich als falsch erwiesen; die Selectboxen bleiben trotzdem, weil sie funktionieren.)

Ab **0.2.2** sind die Schalter wieder Checkboxen; `Migrations/Migration20260918120000.php` wandelt gespeicherte `Y`/`N` in `on`/leer um. Der Code akzeptiert zur Sicherheit beide Schreibweisen.

---

## Entwicklung

### Voraussetzungen
- JTL-Shop 5.5.1 – 5.8.0
- PHP 8.1+ (`str_contains()`, `declare(strict_types=1)`)
- jQuery (Standard im JTL-Template enthalten)

### Verzeichnisstruktur

```
artikel_details_plus/
├── Bootstrap.php                          # Hook-Registrierung, Cheaper-Form-Handling, Snowboard-Specs-Daten
├── Migrations/                            # DB-Migrationen
│   └── Migration20260504120100.php
├── adminmenu/
│   └── merkmalwerte.php                   # Dynamische Optionsquelle (Selectbox)
├── frontend/template/
│   ├── productdetails/
│   │   ├── details.tpl                    # Stylesheet, Lagerbestand, Cheaper-Button
│   │   ├── tabs.tpl                       # Zweispaltiges Raster im Beschreibungs-Tab
│   │   ├── characteristics.tpl            # Fahreigenschaften: Radar-Diagramm + Chips
│   │   ├── flex.tpl                       # Flex-Skala
│   │   ├── fit.tpl                        # Körpergewicht und Fahrlevel
│   │   ├── profile.tpl                    # Seitenansicht Camber/Rocker
│   │   ├── snowboard_values.tpl           # Dimensionen: Board-Skizze + Tabelle (Form/Shape/Waist/Nose/Tail)
│   │   ├── popups.tpl                     # Modal-Wrapper
│   │   └── cheaper.tpl                    # Formular-Markup
│   └── productlist/
│       └── item_box.tpl                   # Merkmalbilder unter Artikelboxen
├── frontend/css/artikel_details_plus.css  # Frontend-Design aller Bauteile
├── frontend/js/ecm_polygon_svg.js         # Pentagon-Radar-Diagramm
├── info.xml                               # Plugin-Manifest
└── README.md
```

### Board-Skizze
`Bootstrap::buildBoardSketch()` berechnet die Geometrie in SVG-Einheiten (viewBox 600 × dynamische Höhe): die **Boardlänge** wird auf 560 Einheiten skaliert, alle Breiten im selben Maßstab – ein 157er Board mit 300 mm Nose erscheint also im echten Verhältnis 5,2:1. Ohne bekannte Länge gilt dieses typische Verhältnis zur breitesten Stelle, und die Längenbemaßung entfällt.

- **Länge:** Funktionsattribut `laenge` in cm (Werte über 400 gelten als mm). Fehlt es, liest `boardLength()` beim Kind-Artikel den gewählten Wert einer Variation, deren Name „Läng“, „Length“, „Size“ oder „Grö“ enthält, und nimmt die führende Zahl (`156 Wide` → 156). Auf der Vaterseite ohne gewählte Variation bleibt die Länge unbekannt.
- **Umriss:** `outlineType()` nimmt zuerst das Attribut `outline` (`twin`, `directional`, `directional twin`); fehlt es oder enthält es keines der Wörter, wird aus den Texten von `form` und `shape` erkannt (Groß-/Kleinschreibung egal): „directional" → Directional, „directional" + „twin" → Directional Twin, sonst Twin. Die Proportionen stehen in `Bootstrap::OUTLINES`: Anteil der Länge bis zur breitesten Stelle an Nose/Tail (Twin 11,5 % / 11,5 %, Directional 14,5 % / 8,5 %), Rundung der Enden (Directional-Tail stumpfer) und Standard-Setback (0 / 1 / 2 cm). Die Enden sind kubische Bézier-Kurven mit senkrechter Tangente an der Spitze und waagerechter an der breitesten Stelle, die Sidecuts S-Kurven.
- **Inserts:** `insertType()` normalisiert das Attribut `inserts` auf `channel` (enthält „channel“), `2x4` oder `4x4`; andere Werte zeichnen nichts. Gezeichnet wird pro Fuß: 4x4 = 3 Spalten × 2 Reihen im 4-cm-Raster, 2x4 = 6 Spalten (2 cm) × 2 Reihen (4 cm), Channel = ein 17 cm langer Schlitz. Die Füße stehen im Referenzstance (`stance` in cm, sonst 36 % der Länge, begrenzt auf 40–60 cm) um die Boardmitte plus Setback (`setback` in cm Richtung Tail, sonst der Umriss-Standard).

`snowboard_values.tpl` zeichnet Umriss, Inserts, Längenmaß oben, Breitenmaße unten; Werte werden so ausgegeben, wie sie im Shop gepflegt sind (z. B. `298,5`). Der Harness zum Prüfen der Geometrie liegt nicht im Repo: Stubs für `JTL\Plugin\Bootstrapper`, `JTL\Shop` und `JTL\Events\Dispatcher`, dann `buildBoardSketch()` per Reflection mit Beispielboards aufrufen und das SVG mit dem Plugin-CSS rendern.

---

## Versionsverlauf

### 0.8.0 (2026-09-21)
- Neu: Karte „Profil" mit Seitenansicht des Bretts (Camber, Rocker, Flat, Hybrid Camber, Hybrid Rocker, Flat Rocker) über die volle Breite unter den beiden Spalten; erkannt aus `form` (ersatzweise `shape`), Attribut `profil` hat Vorrang; `X/Y/X`-Notation wird nach dem mittleren Element gelesen
- Neue Sprachvariable `artikel_details_plus_specs_heading_profile`

### 0.7.2 (2026-09-21)
- Fix: Twin-Boards waren in der Skizze nicht spiegelsymmetrisch. Die Kurve vom breitesten Punkt zur Spitze setzte ihren waagerechten Handle vom breitesten Punkt aus statt von der Spitze – das Board war punkt- statt spiegelsymmetrisch (Tail oben spitzer, Nose unten spitzer). Jetzt ist jede Spitze das exakte Spiegelbild der Gegenseite; Prüfung: alle Pfadpunkte haben ein Gegenstück bei 600 − x

### 0.7.1 (2026-09-21)
- Fix: Das Attribut `outline` hat jetzt Vorrang vor der Erkennung aus `form`/`shape` (vorher wurden alle drei Texte zusammen ausgewertet, ein explizites `twin` konnte ein „Directional Camber" im Profil nicht überstimmen)

### 0.7.0 (2026-09-21)
- Flex-Skala als eigene Karte unter den Fahreigenschaften (vorher am unteren Rand der Diagramm-Karte)
- Raster auf zwei echte Spalten umgebaut: links Fahreigenschaften + Flex, rechts Gewicht/Fahrlevel + Dimensionen; beide Spalten enden bündig, die Diagramm-Karte nimmt die Höhendifferenz auf
- `svg_attributes.tpl` in `characteristics.tpl`, `flex.tpl` und `fit.tpl` aufgeteilt

### 0.6.0 (2026-09-21)
- Neu: Flex-Skala 1–10 mit fünf Zonen (Soft bis Stiff) am unteren Rand der Fahreigenschaften-Karte; Funktionsattribut `flex` oder Bereich `flex_ab`/`flex_bis`, Dezimalwerte als halbes Segment, Zonennamen als Sprachvariablen
- Layout: Die Fahreigenschaften-Karte wird auf die Höhe der rechten Spalte gestreckt – die Lücke unter dem Diagramm, die je nach Länge der Dimensionen-Tabelle entstand, ist damit weg

### 0.5.0 (2026-09-21)
- Board-Skizze maßstäblich: die Boardlänge bestimmt den Maßstab, Breiten werden im selben Verhältnis gezeichnet (vorher wurde die breiteste Stelle fix auf 140 von 600 Einheiten gestreckt – jedes Board sah gleich dick aus)
- Länge aus dem Funktionsattribut `laenge` (cm) oder automatisch aus der gewählten Variation „Länge“ des Kind-Artikels; erscheint als Längenmaß über der Skizze und als erste Tabellenzeile
- Directional-Umriss (längere, spitzere Nose, kürzeres, stumpferes Tail) neben Twin und Directional Twin; erkannt aus `form`/`shape` oder per Attribut `outline`
- Inserts in der Skizze: Lochmuster 4x4 und 2x4 oder Burtons Channel, über das Attribut `inserts`; Referenzstance und Setback optional über `stance`/`setback` (cm), sonst plausible Standardwerte
- Tabelle um Länge, Inserts, Stance und Setback erweitert

### 0.4.2 (2026-09-18)
- Fix: Der Lagerbalken hing ohne `col`-Klasse als nacktes Flex-Item in NOVAs Preis-Row — er schrumpfte auf Inhaltsbreite, rutschte neben den Preis an den rechten Rand und verschmälerte den Preisblock von 625px auf 437px. Jetzt eigene `col col-12` in voller Breite unter dem Preis
- Der „Günstiger gesehen?"-Button steht nicht mehr als eigenes Band zwischen Preis und Lieferinfo, sondern in NOVAs Button-Spalte direkt vor „Frage zum Artikel" — eine Aktionszeile statt zwei
- Das Plugin-Stylesheet wird nicht mehr als (0px breites) Flex-Item in die Preis-Row gehängt, sondern in die eigene Spalte
- Der Button erscheint nicht mehr im Quickview, wo NOVA das zugehörige Modal gar nicht rendert

### 0.4.1 (2026-09-18)
- „Günstiger gesehen?" ist jetzt ein Pill-Button mit Preisschild-Icon statt eines Fragezeichen-Links: die NOVA-Zeile „Frage zum Artikel" steht direkt darunter, beide sahen vorher praktisch gleich aus

### 0.4.0 (2026-09-18)
- Design-Überarbeitung der Artikeldetailseite: Fahreigenschaften, Körpergewicht/Fahrlevel und Dimensionen liegen jetzt in einheitlichen Karten mit gemeinsamem Raster (ab 768px zweispaltig) statt frei im Beschreibungs-Tab
- Radar-Diagramm in Akzentfarbe mit hellem Gitter und lesbaren Beschriftungen; neue Chip-Liste mit allen Werten (vorher nur per Hover sichtbar, auf Touch-Geräten gar nicht)
- Gewichts- und Fahrlevel-Leisten als abgerundete Pill-Leisten mit Klartext-Spanne statt schwarzem Kasten mit Tooltip
- Lagerbestandsanzeige als schlanker Balken ohne Rahmen; die eingestellte Farbe kommt als CSS-Variable ins Markup
- Alle Inline-`<style>`-Blöcke und die fest kodierten Farben in `ecm_polygon_svg.js` entfernt; das Stylesheet wird einmalig in `details.tpl` eingebunden, das Diagramm-Skript ist gegen doppeltes Laden abgesichert

### 0.3.0 (2026-09-18)
- Countdown entfernt: Einstellungen, Sprachvariablen und Template-Block sind in die Countdown-Verwaltung von Startseite Plus 2.1.0 umgezogen (mehrere Countdowns, Anzeige auf Artikelseiten wahlweise nur bei Sonderpreis). Migration räumt die alten Einstellungswerte auf.

### 0.2.2 (2026-09-18)
- Alle acht Aktiv-Schalter sind wieder Checkboxen; Migration konvertiert gespeicherte Werte
- Feste Texte als Sprachvariablen: Körpergewicht- und Fahrlevel-Überschrift, Lagerbestandstext (mit `%s` für die Stückzahl), Countdown-Beschriftungen (waren bisher nur deutsch)
- Merkmalbilder in der Artikelliste: Schalter und Merkmalauswahl werden per `HOOK_SMARTY_INC` als Smarty-Variablen bereitgestellt, das Template liest keine Config-Werte mehr

### 0.2.1 (2026-09-18)
- Fix: Division durch den Lagerbestand-Schwellenwert lief im Template auf jeder Artikelseite; bei Schwellenwert 0 führte das zu einem Fatal Error. Berechnung jetzt in PHP, Balken nur bei Schwellenwert > 0 und Bestand > 0
- Fix: Modal-ID des „Günstiger gesehen"-Formulars folgt jetzt wie NOVA dem Kind-Artikel bei Variationskombinationen; vorher öffnete sich das Modal dort nicht
- Fix: Fahrlevel-Leiste zeigte auf Mobilgeräten die Gewichts-Skala; Körpergewicht und Fahrlevel werden jetzt in PHP berechnet (mit Vaterartikel-Fallback), Fahrlevel-Werte werden unabhängig von Groß-/Kleinschreibung erkannt
- Fix: Countdown erscheint nur mit gültigem Datum und Uhrzeit in der Zukunft
- Sicherheit: Formular-POST wird ignoriert, wenn das Formular deaktiviert ist; Artikelname kommt aus der Datenbank statt aus dem Formular; nur http(s)-Links; fehlgeschlagener Mailversand führt zur Fehlermeldung statt zur Erfolgsmeldung; Merkmalwert-Tooltips und Board-Beschriftungen werden escaped
- Merkmalbilder: Shop-URL-Variable korrigiert (`$ShopURL`), leere Merkmalauswahl erzeugt keine leere Liste mehr
- Button-Beschriftung nutzt Bootstrap-4-Klassen (`d-none d-md-inline`) statt Bootstrap-3-Klassen

### 0.2.0 (2026-09-15)
- Funktionalität des Plugins `snowboard_specs` integriert: Fahreigenschaften und Dimensionen werden in `Bootstrap::assignSnowboardSpecs()` aus den Funktionsattributen gelesen (mit Vaterartikel-Fallback und Dezimalkomma-Unterstützung), neue Schalter „Fahreigenschaften-Diagramm anzeigen" und „Dimensionen anzeigen", übersetzbare Überschriften
- Neu: SVG-Board-Skizze mit Nose/Waist/Tail-Maßen plus Tabelle für Form/Shape/Waist/Nose/Tail (vorher reine Liste ohne Vaterartikel-Fallback)
- Pentagon-Diagramm erscheint ab drei vorhandenen Werten, Werte werden auf 0–10 begrenzt
- Fix: `$oBrowser->bMobile` (wird vom Core nicht mehr gesetzt) durch `$isMobile` ersetzt
- `ecm_polygon_svg.js` nach `frontend/js/` verschoben, Asset-URLs über den Plugin-Frontend-Pfad statt `$shopURL`
- Kompatibilität mit JTL-Shop 5.8.0 geprüft (alle sechs NOVA-Blöcke vorhanden, Core-API unverändert), MaxShopVersion auf 5.8.0

### 0.1.2 (2026-05-04)
- Cleanup: ungenutzten `cheaper.php`-Stub gelöscht
- Cleanup: `snowboard_values.tpl` auf reine Spezifikationsliste reduziert (vorher leerer Canvas-Container ohne JS)
- Robustness: Redirect-URL-Bereinigung im „Günstiger gesehen"-Handler nutzt jetzt `parse_url`/`http_build_query` statt fragiler Regex

### 0.1.1 (2026-05-04)
- Fix: Checkbox-Einstellungen durch Selectbox (Ja/Nein) ersetzt — beheben Bug, der das Deaktivieren verhindert hat
- Migration konvertiert bestehende `'on'`-Werte zu `'Y'`
- Templates einheitlich auf `=== 'Y'`-Vergleich umgestellt

### 0.1.0
- „Günstiger gesehen?"-Formular implementiert (Modal, CSRF, Honeypot, PRG-Redirect, E-Mail-Versand)
- Sprachvariablen für DE/EN
- Kompatibilität mit JTL-Shop 5.7.0

### 0.0.x
- Pentagon-SVG-Diagramm, Lagerbestandsanzeige, Countdown, Merkmalbilder
