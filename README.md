# Artikeldetails Plus

Ein JTL-Shop Plugin, das die Artikeldetailseite und Artikelliste um nützliche Darstellungen und Funktionen erweitert — ohne Template-Anpassungen direkt im Shop.

**Autor:** Oliver Kamps  
**Version:** 0.0.14  
**Kompatibel mit:** JTL-Shop 5.5.1 – 5.5.3

---

## Funktionen

### Erweiterte Merkmaldarstellung
Zeigt Artikelmerkmale auf der Detailseite visuell aufbereitet an — inklusive interaktivem SVG-Pentagondiagramm (z. B. für Snowboard-Fahreigenschaften wie Carving, Jib, Powder, All-Mountain, Jump) sowie einer Körpergewichts- und Fahrlevelanzeige.

### Merkmalbilder in der Artikelliste
Unter jeder Artikelbox in der Listenansicht werden Bilder der konfigurierten Merkmalwerte angezeigt — ideal für Kategorien mit visuellen Produktmerkmalen (z. B. Technologiebadges).

### Lagerbestandsanzeige
Sobald der Lagerbestand unter einen konfigurierbaren Schwellenwert fällt, erscheint ein farbiger Fortschrittsbalken mit der verbleibenden Stückzahl — schafft Dringlichkeit beim Käufer.

### Countdown-Timer
Zeigt einen Countdown auf der Artikeldetailseite an, solange ein Sonderpreis aktiv ist. Datum und Uhrzeit sind frei konfigurierbar. Der Timer stoppt automatisch nach Ablauf.

### "Günstiger gesehen?"-Button
Ein Button auf der Artikeldetailseite, über den Kunden Preishinweise einreichen können.

---

## Installation

1. Plugin-Ordner in das Verzeichnis `plugins/` des JTL-Shops kopieren
2. Im JTL-Shop Backend unter **Plugin-Manager** das Plugin installieren und aktivieren
3. Einstellungen unter **Plugins → Artikeldetails Plus** konfigurieren

---

## Konfiguration

Das Plugin bietet folgende Einstellungsbereiche im Admin-Menü:

| Bereich | Einstellung | Beschreibung |
|---|---|---|
| Fahreigenschaften | Merkmalwert Anzeige aktivieren | Schaltet das SVG-Diagramm und Merkmalwerte ein |
| Fahreigenschaften | Fahrlevelanzeige aktivieren | Zeigt Beginner / Advanced / Professional Anzeige |
| Merkmalbilder | Merkmalbilder aktivieren | Aktiviert Bilder unter Artikelboxen in der Liste |
| Merkmalbilder | Merkmalwerte mit Bildern | Auswahl welche Merkmale Bilder anzeigen sollen |
| Countdown | Countdown aktivieren | Schaltet den Timer ein (nur bei aktivem Sonderpreis) |
| Countdown | Datum & Uhrzeit | Zieldatum und -uhrzeit des Countdowns |
| Lagerbestandsanzeige | Lagerbestandsanzeige aktivieren | Schaltet den Fortschrittsbalken ein |
| Lagerbestandsanzeige | Schwellenwert | Ab welchem Lagerbestand die Anzeige erscheint |
| Lagerbestandsanzeige | Farbe | Farbe des Fortschrittsbalkens |

---

## Geplante Features

- [ ] Formular-Backend für die Funktion "Günstiger gesehen?"

---

## Voraussetzungen

- JTL-Shop 5.5.1 oder höher
- PHP 7.4+
- jQuery (im Standard-JTL-Template enthalten)
