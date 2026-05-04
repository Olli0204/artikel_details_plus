# Artikeldetails Plus

JTL-Shop 5 Plugin, das die Artikeldetailseite und die Artikellistenansicht um visuelle Bauteile und ein Kunden-Feedback-Formular erweitert — ohne dass das Shop-Template angefasst werden muss.

**Autor:** Oliver Kamps
**Version:** 0.1.2
**Kompatibel mit:** JTL-Shop 5.5.1 – 5.7.0
**Voraussetzung:** PHP 8.0+

---

## Funktionen

### 1. Erweiterte Merkmaldarstellung
Auf der Artikeldetailseite wird im Beschreibungs-Tab ein interaktives **Pentagon-SVG-Diagramm** für fünf Snowboard-Fahreigenschaften (Carving, Jib, Powder, All-Mountain, Jump) gerendert. Daneben optional eine **Körpergewichtsleiste** und eine **Fahrlevel-Anzeige** (Beginner / Advanced / Professional).

Erforderliche Funktionsattribute am Artikel — die Anzeige erscheint nur, wenn alle entsprechenden Attribute gesetzt sind:

| Funktion | Erforderliche Funktionsattribute |
|---|---|
| Pentagon-Diagramm | `carving`, `jib`, `powder`, `all_mountain`, `jump` (jeweils 0–10) |
| Körpergewichtsleiste | `koerpergewicht_ab`, `koerpergewicht_bis` (in kg) |
| Fahrlevel-Anzeige | `fahrlevel_ab`, `fahrlevel_bis` (Werte: `Beginner` / `Advanced` / `Professional`) |

### 2. Merkmalbilder in der Artikelliste
Unterhalb jeder Produktbox in Kategorie- und Suchergebnislisten werden Bilder ausgewählter Merkmalwerte angezeigt — z. B. Technologie- oder Eigenschaftsbadges. In den Plugin-Einstellungen wird ausgewählt, welche Merkmale (nur die mit hinterlegten Bildern) angezeigt werden sollen.

### 3. Lagerbestandsanzeige
Sobald der Lagerbestand unter einen konfigurierbaren Schwellenwert fällt, erscheint ein **farbiger Fortschrittsbalken** mit der Restmenge. Der Balken füllt sich proportional zum Verhältnis Bestand/Schwellwert. Farbe ist im Backend per Color-Picker einstellbar.

### 4. Countdown-Timer
Zeigt einen JavaScript-basierten Countdown (Tage / Stunden / Minuten / Sekunden) auf der Artikeldetailseite an — **aber nur, solange ein Sonderpreis aktiv ist** (`Sonderpreis_aktiv`). Zieldatum/-uhrzeit sind im Backend frei konfigurierbar; nach Ablauf wird der Timer automatisch ausgeblendet. Überschrift ist als Sprachvariable übersetzbar (Default: „Black Weekend Sale").

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

Die Einstellungen sind in fünf Tabs gegliedert. Alle „Aktiv"-Einstellungen sind als Selectbox mit Werten **Ja / Nein** umgesetzt (statt Checkbox — siehe Hinweis unten).

| Tab | Einstellung | Typ | Beschreibung |
|---|---|---|---|
| Fahreigenschaften | Merkmalwert-Anzeige aktiv | Ja/Nein | Schaltet Pentagon-Diagramm und Gewichtsleiste im Beschreibungs-Tab ein |
| Fahreigenschaften | Fahrlevelanzeige aktiv | Ja/Nein | Schaltet die Beginner/Advanced/Professional-Leiste ein |
| Merkmalbilder | Merkmalbilder Anzeige aktiv | Ja/Nein | Aktiviert Bilder unter den Artikelboxen in der Listenansicht |
| Merkmalbilder | Merkmalwerte mit Bildern | Mehrfachauswahl | Welche Merkmale (mit hinterlegten Bildern) angezeigt werden — dynamisch aus `tmerkmal` |
| Countdown | Countdown aktiv | Ja/Nein | Zeigt den Timer an (zusätzliche Bedingung: aktiver Sonderpreis) |
| Countdown | Datum / Zeit | Date / Time | Ablauf-Zeitpunkt des Countdowns |
| Lagerbestandsanzeige | Lagerbestandsanzeige aktiv | Ja/Nein | Zeigt den Fortschrittsbalken bei niedrigem Bestand |
| Lagerbestandsanzeige | Nur bei Lagerbestand unter | Number (Default 10) | Schwellenwert, ab dem die Anzeige erscheint |
| Lagerbestandsanzeige | Farbe der Anzeige | Color (Default `#ffa54f`) | Farbe des Fortschrittsbalkens |
| Günstiger gesehen | Formular aktiv | Ja/Nein | Schaltet Button und Modal auf der Artikeldetailseite ein |

**Hinweis:** Die Aktiv-Einstellungen sind bewusst keine Checkboxen — JTL-Core hat einen Bug, durch den ungecheckte Checkboxen beim Speichern nicht zurückgesetzt werden können (siehe Migration-Hinweis unten).

---

## Übersetzbare Texte

Alle frontend-relevanten Texte sind als Sprachvariablen hinterlegt und können unter **Inhalte → Sprachvariablen → Plugins → Artikeldetails Plus** angepasst werden:

| Variable | Default DE | Default EN |
|---|---|---|
| `artikel_details_plus_countdown_heading` | Black Weekend Sale | Black Weekend Sale |
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
| `productdetails-details-include-variation` | `details.tpl` | Countdown-Box oberhalb der Variationen |
| `productdetails-details-stock` | `details.tpl` | Lagerbestand-Balken + „Günstiger gesehen"-Button |
| `tab-description-media-types`, `productdetails-tabs-card-description-content` | `tabs.tpl` | Pentagon/Gewicht/Fahrlevel im Beschreibungs-Tab |
| `productdetails-popups` | `popups.tpl` | Modal mit Formular |
| `productlist-index-include-price` | `item_box.tpl` | Merkmalbilder unter Artikelboxen |

### Hooks
- `HOOK_ARTIKEL_PAGE` (registriert in `Bootstrap.php`): verarbeitet POST-Submissions des „Günstiger gesehen"-Formulars; führt PRG-Redirect aus.

### Dynamische Optionsquelle
- `adminmenu/merkmalwerte.php`: SQL-Query über `tmerkmal`/`tmerkmalwert`, liefert nur Merkmale mit mindestens einem bebilderten Wert. Versorgt die Mehrfachauswahl „Merkmalwerte mit Bildern".

### Assets
- `ecm_polygon_svg.js`: JS-Klasse zur Berechnung und Darstellung des Pentagon-Radar-Diagramms (kein jQuery-Plugin, eigenständige ES6-Klasse). Wird via `<script src>` in `svg_attributes.tpl` eingebunden.

---

## Update / Migration

Ab Version **0.1.1** sind die früheren Checkbox-Einstellungen auf **Selectbox (Ja/Nein)** umgestellt. Hintergrund: JTL-Core-Bug — eine ungecheckte Checkbox sendet beim Speichern keinen Wert, wodurch der vorherige Wert in `tplugineinstellungen` erhalten bleibt; die Einstellung lässt sich faktisch nicht mehr deaktivieren.

`Migrations/Migration20260504120100.php` konvertiert beim Plugin-Update bestehende `'on'`-Werte automatisch zu `'Y'`. Es ist also kein manueller Eingriff in die Datenbank nötig — Einstellungen bleiben erhalten.

---

## Entwicklung

### Voraussetzungen
- JTL-Shop 5.5.1 – 5.7.0
- PHP 8.0+ (`str_contains()`, `declare(strict_types=1)`)
- jQuery (Standard im JTL-Template enthalten)

### Verzeichnisstruktur

```
artikel_details_plus/
├── Bootstrap.php                          # Hook-Registrierung, Cheaper-Form-Handling
├── Migrations/                            # DB-Migrationen
│   └── Migration20260504120100.php
├── adminmenu/
│   └── merkmalwerte.php                   # Dynamische Optionsquelle (Selectbox)
├── frontend/template/
│   ├── productdetails/
│   │   ├── details.tpl                    # Countdown, Lagerbestand, Cheaper-Button
│   │   ├── tabs.tpl                       # Pentagon/Gewicht/Fahrlevel im Beschreibungs-Tab
│   │   ├── svg_attributes.tpl             # Pentagon-SVG + Gewicht/Fahrlevel-Logik
│   │   ├── snowboard_values.tpl           # Snowboard-Spezifikationsliste (Form/Shape/Waist/Nose/Tail)
│   │   ├── popups.tpl                     # Modal-Wrapper
│   │   └── cheaper.tpl                    # Formular-Markup
│   └── productlist/
│       └── item_box.tpl                   # Merkmalbilder unter Artikelboxen
├── ecm_polygon_svg.js                     # Pentagon-Radar-Diagramm
├── info.xml                               # Plugin-Manifest
└── README.md
```

### Snowboard-Spezifikationsliste
`snowboard_values.tpl` rendert eine `<ul class="adp-snowboard-specs">` mit Form / Shape / Waist / Nose / Tail (in mm), sobald alle fünf Funktionsattribute am Artikel gesetzt sind. Reine Textausgabe — Styling kann frei im Theme vorgenommen werden.

---

## Versionsverlauf

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
