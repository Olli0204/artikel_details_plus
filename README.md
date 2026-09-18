# Artikeldetails Plus

JTL-Shop 5 Plugin, das die Artikeldetailseite und die Artikellistenansicht um visuelle Bauteile und ein Kunden-Feedback-Formular erweitert — ohne dass das Shop-Template angefasst werden muss.

**Autor:** Oliver Kamps
**Version:** 0.3.0
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
| Dimensionen | Tabelle Form / Shape / Waist / Nose / Tail plus **SVG-Board-Skizze** mit Breitenmaßen (Nose links, Tail rechts, breiteste Stelle als Referenz); die Skizze erscheint, wenn `nose`, `waist` und `tail` numerisch sind | `form`, `shape`, `waist`, `nose`, `tail` (Breiten in mm) |

Fahreigenschaften-Diagramm und Dimensionen lassen sich einzeln abschalten; der Schalter „Merkmalwert-Anzeige aktiv" bleibt der Hauptschalter für den gesamten Bereich. Die Überschriften „Fahreigenschaften" und „Dimensionen" sind Sprachvariablen. Dieser Bereich ersetzt das frühere Plugin `snowboard_specs` (September 2026 integriert).

### 2. Merkmalbilder in der Artikelliste
Unterhalb jeder Produktbox in Kategorie- und Suchergebnislisten werden Bilder ausgewählter Merkmalwerte angezeigt — z. B. Technologie- oder Eigenschaftsbadges. In den Plugin-Einstellungen wird ausgewählt, welche Merkmale (nur die mit hinterlegten Bildern) angezeigt werden sollen.

### 3. Lagerbestandsanzeige
Sobald der Lagerbestand unter einen konfigurierbaren Schwellenwert fällt, erscheint ein **farbiger Fortschrittsbalken** mit der Restmenge. Der Balken füllt sich proportional zum Verhältnis Bestand/Schwellwert. Farbe ist im Backend per Color-Picker einstellbar.

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
| Fahreigenschaften | Dimensionen anzeigen | Checkbox (Default an) | Tabelle und Board-Skizze mit Form/Shape/Waist/Nose/Tail |
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
| `artikel_details_plus_stock_text` | Nur noch %s Stück verfügbar! | Only %s pieces available! |
| `artikel_details_plus_specs_heading_characteristics` | Fahreigenschaften | Ride Characteristics |
| `artikel_details_plus_specs_heading_dimensions` | Dimensionen | Dimensions |
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
| `productdetails-details-stock` | `details.tpl` | Lagerbestand-Balken + „Günstiger gesehen"-Button |
| `tab-description-media-types`, `productdetails-tabs-card-description-content` | `tabs.tpl` | Pentagon/Gewicht/Fahrlevel im Beschreibungs-Tab |
| `productdetails-popups` | `popups.tpl` | Modal mit Formular |
| `productlist-index-include-price` | `item_box.tpl` | Merkmalbilder unter Artikelboxen |

### Hooks
- `HOOK_ARTIKEL_PAGE` (registriert in `Bootstrap.php`): verarbeitet POST-Submissions des „Günstiger gesehen"-Formulars (PRG-Redirect) und befüllt die Smarty-Variablen für alle Bauteile: `assignSnowboardSpecs()` setzt `adpSpecsCharacteristics`, `adpSpecsDimensions`, `adpSpecsBoard`, `adpFrontendURL`; `assignDetailExtras()` setzt `adpStock`, `adpCheaperActive`, `adpWeight`, `adpLevel`. Die Templates rechnen nichts mehr selbst.

### Dynamische Optionsquelle
- `adminmenu/merkmalwerte.php`: SQL-Query über `tmerkmal`/`tmerkmalwert`, liefert nur Merkmale mit mindestens einem bebilderten Wert. Versorgt die Mehrfachauswahl „Merkmalwerte mit Bildern".

### Assets
- `frontend/js/ecm_polygon_svg.js`: JS-Klasse zur Berechnung und Darstellung des Pentagon-Radar-Diagramms (eigenständige ES6-Klasse, benötigt jQuery aus dem Template). Wird via `<script src>` in `svg_attributes.tpl` eingebunden.
- `frontend/css/artikel_details_plus.css`: Styles der Snowboard-Specs (Überschriften, Board-Skizze, Dimensionen-Tabelle); wird in `tabs.tpl` mit Versions-Parameter verlinkt. Farbe der Skizze über `--primary`, Fallback `#FFA54F`.

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
│   │   ├── details.tpl                    # Lagerbestand, Cheaper-Button
│   │   ├── tabs.tpl                       # Pentagon/Gewicht/Fahrlevel im Beschreibungs-Tab
│   │   ├── svg_attributes.tpl             # Pentagon-SVG + Gewicht/Fahrlevel-Logik
│   │   ├── snowboard_values.tpl           # Dimensionen: Board-Skizze + Tabelle (Form/Shape/Waist/Nose/Tail)
│   │   ├── popups.tpl                     # Modal-Wrapper
│   │   └── cheaper.tpl                    # Formular-Markup
│   └── productlist/
│       └── item_box.tpl                   # Merkmalbilder unter Artikelboxen
├── frontend/css/artikel_details_plus.css  # Styles der Snowboard-Specs
├── frontend/js/ecm_polygon_svg.js         # Pentagon-Radar-Diagramm
├── info.xml                               # Plugin-Manifest
└── README.md
```

### Board-Skizze
`Bootstrap::buildBoardSketch()` berechnet den SVG-Pfad (viewBox 600×220): die breiteste der drei Breiten wird auf 140 Einheiten skaliert, Nose/Waist/Tail liegen bei x = 80 / 300 / 520, die Kanten sind kubische Bézier-Kurven. `snowboard_values.tpl` zeichnet Umriss, gestrichelte Maßlinien und Beschriftungen; Werte werden so ausgegeben, wie sie im Shop gepflegt sind (z. B. `298,5`).

---

## Versionsverlauf

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
