<?php declare(strict_types=1);

namespace Plugin\artikel_details_plus;

use JTL\Events\Dispatcher;
use JTL\Helpers\Form;
use JTL\Mail\Mail\Mail;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        // Artikellisten haben keinen eigenen Hook: Schalter fuer item_box.tpl beim Smarty-Start bereitstellen
        $dispatcher->hookInto(\HOOK_SMARTY_INC, function (array $args): void {
            $config  = $this->getPlugin()->getConfig();
            $ids     = $config->getValue('artikel_details_plus_merkmalwerte');
            $args['smarty']->assign('adpFeatureImagesActive', $this->isOn($config->getValue('artikel_details_plus_merkmalbilder_aktiv')))
                ->assign('adpFeatureIds', \is_array($ids) ? \array_map('\intval', $ids) : []);
        });

        $dispatcher->hookInto(\HOOK_ARTIKEL_PAGE, function (array $args): void {
            $this->handleCheaperForm();
            $this->loadFeatureAttributes($args['oArtikel'] ?? null);
            $this->assignSnowboardSpecs($args['oArtikel'] ?? null);
            $this->assignDetailExtras($args['oArtikel'] ?? null);
        });
    }

    /**
     * Checkbox-Einstellungen: JTL speichert 'on' (angehakt) bzw. '' (abgewaehlt); 'Y' stammt aus den Selectboxen bis 0.2.1
     */
    private function isOn(mixed $value): bool
    {
        return \in_array((string)$value, ['on', 'Y'], true);
    }

    /**
     * Fahrlevel-Stufen in der Reihenfolge der Leiste: Schlüsselwert (fahrlevel_ab/_bis) => Sprachvariable der Beschriftung
     */
    private const LEVELS = [
        'Beginner'     => 'artikel_details_plus_level_beginner',
        'Advanced'     => 'artikel_details_plus_level_intermediate',
        'Professional' => 'artikel_details_plus_level_expert',
    ];

    /**
     * Fahrlevel-Wörter => Stufe (0-2). Die Reihenfolge zählt: je Wert gewinnt das erste Muster,
     * damit "Advanced/Expert" (Shop-Merkmal) die oberste Stufe ist, "Advanced" allein aber die mittlere.
     */
    private const LEVEL_WORDS = [
        '/expert|professional|\bpro\b|profi/u'                => 2,
        '/interm|fortgeschritten|mittel/u'                    => 1,
        '/beginner|anf(?:ä|ae)nger|einsteiger|novice|entry/u' => 0,
        '/advanced/u'                                         => 1,
    ];

    /** Flex-Skala 1-10 in fünf Zonen: [von, bis, Sprachvariable] */
    private const FLEX_ZONES = [
        [1, 2, 'artikel_details_plus_flex_zone_soft'],
        [3, 4, 'artikel_details_plus_flex_zone_medium_soft'],
        [5, 6, 'artikel_details_plus_flex_zone_medium'],
        [7, 8, 'artikel_details_plus_flex_zone_medium_stiff'],
        [9, 10, 'artikel_details_plus_flex_zone_stiff'],
    ];

    /**
     * Skalen der Körpergewichtsleiste (kg), jeweils mit "+" am Anfang und Ende
     */
    private const WEIGHT_STEPS_DESKTOP = [35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100];
    private const WEIGHT_STEPS_MOBILE  = [40, 50, 60, 70, 80, 90, 100];

    /**
     * Lagerbestandsanzeige, "Günstiger gesehen", Körpergewicht und Fahrlevel:
     * alle Berechnungen passieren hier, die Templates geben nur noch aus.
     * (Der Countdown ist seit 0.3.0 in der Countdown-Verwaltung von Startseite Plus.)
     */
    public function assignDetailExtras(?object $artikel): void
    {
        $smarty = Shop::Smarty();
        $config = $this->getPlugin()->getConfig();

        $smarty->assign('adpStock', null)
            ->assign('adpCheaperActive', $this->isOn($config->getValue('artikel_details_plus_cheaper_aktiv')))
            ->assign('adpWeight', null)
            ->assign('adpLevel', null)
            ->assign('adpFlex', null);

        if ($artikel === null) {
            return;
        }

        // Lagerbestand: Balken nur unterhalb des Schwellenwerts, Division nur mit Schwellenwert > 0
        if ($this->isOn($config->getValue('artikel_details_plus_lagerbestand_aktiv'))) {
            $threshold = (float)\str_replace(',', '.', (string)$config->getValue('artikel_details_plus_lagerbestand_wert'));
            $stock     = (float)($artikel->fLagerbestand ?? 0);
            $color     = (string)$config->getValue('artikel_details_plus_lagerbestand_farbe');
            if (!\preg_match('/^#[0-9a-f]{3,8}$/i', $color)) {
                $color = '#ffa54f';
            }
            if ($threshold > 0 && $stock > 0 && $stock < $threshold) {
                $smarty->assign('adpStock', [
                    'count' => \fmod($stock, 1.0) === 0.0 ? (string)(int)$stock : (string)$stock,
                    'pct'   => (int)\round(\min(100.0, \max(0.0, $stock / $threshold * 100))),
                    'color' => $color,
                ]);
            }
        }

        if (!$this->isOn($config->getValue('artikel_details_plus_merkmalwerte_aktiv'))) {
            return;
        }

        // Körpergewicht
        $from = $this->numericAttribute($artikel, 'koerpergewicht_ab');
        $to   = $this->numericAttribute($artikel, 'koerpergewicht_bis');
        if ($from !== null && $to !== null && $to >= $from) {
            $smarty->assign('adpWeight', [
                'from'    => $from,
                'to'      => $to,
                'desktop' => $this->weightSteps(self::WEIGHT_STEPS_DESKTOP, $from, $to),
                'mobile'  => $this->weightSteps(self::WEIGHT_STEPS_MOBILE, $from, $to),
            ]);
        }

        // Fahrlevel
        if ($this->isOn($config->getValue('artikel_details_plus_fahrlevel_aktiv'))) {
            $fromIdx = $this->levelIndex($this->attribute($artikel, 'fahrlevel_ab'));
            $toIdx   = $this->levelIndex($this->attribute($artikel, 'fahrlevel_bis'));
            if ($fromIdx !== null || $toIdx !== null) {
                $fromIdx ??= $toIdx;
                $toIdx   ??= $fromIdx;
                if ($fromIdx > $toIdx) {
                    [$fromIdx, $toIdx] = [$toIdx, $fromIdx];
                }
                $loc    = $this->getPlugin()->getLocalization();
                $labels = \array_map(
                    static fn(string $var): string => (string)$loc->getTranslation($var),
                    \array_values(self::LEVELS)
                );
                $steps  = [];
                foreach ($labels as $idx => $label) {
                    $steps[] = ['label' => $label, 'set' => $idx >= $fromIdx && $idx <= $toIdx];
                }
                $smarty->assign('adpLevel', [
                    'from'  => $labels[$fromIdx],
                    'to'    => $labels[$toIdx],
                    'steps' => $steps,
                ]);
            }
        }

        $smarty->assign('adpFlex', $this->flexScale($artikel));
    }

    /**
     * Flex-Skala: Funktionsattribut "flex" (1-10, Dezimal erlaubt) oder Bereich "flex_ab"/"flex_bis".
     * Liefert zehn Segmente (fill 0 / 0.5 / 1), fünf Zonen mit Aktiv-Flag und den Klartext für die Kopfzeile.
     *
     * @return array{from: float, to: float, segments: list<array{fill: float}>,
     *               zones: list<array{label: string, set: bool}>, zone: string, value: string, text: string}|null
     */
    private function flexScale(object $artikel): ?array
    {
        $single = $this->numericAttribute($artikel, 'flex');
        $from   = $this->numericAttribute($artikel, 'flex_ab') ?? $single;
        $to     = $this->numericAttribute($artikel, 'flex_bis') ?? $single;
        if ($from === null && $to === null) {
            return null;
        }
        $from ??= $to;
        $to   ??= $from;
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        $from = \max(1.0, \min(10.0, $from));
        $to   = \max(1.0, \min(10.0, $to));

        $segments = [];
        for ($i = 1; $i <= 10; $i++) {
            // Segment i deckt den Wertebereich (i-1, i] ab; beim Einzelwert zählt alles bis zum Wert
            $lo = $from === $to ? 0.0 : $from - 1;
            $covered = \max(0.0, \min((float)$i, $to) - \max((float)($i - 1), $lo));
            $segments[] = ['fill' => $covered >= 0.99 ? 1.0 : ($covered >= 0.4 ? 0.5 : 0.0)];
        }

        $loc    = $this->getPlugin()->getLocalization();
        $zones  = [];
        $labels = [];
        foreach (self::FLEX_ZONES as [$zFrom, $zTo, $var]) {
            $label = $loc->getTranslation($var);
            $set   = $to >= $zFrom && $from <= $zTo;
            $zones[] = ['label' => $label, 'set' => $set];
            if ($set) {
                $labels[] = $label;
            }
        }
        $zoneText = \count($labels) > 1 ? $labels[0] . ' – ' . $labels[\count($labels) - 1] : ($labels[0] ?? '');
        $valText  = $from === $to
            ? $this->formatNumber($from) . '/10'
            : $this->formatNumber($from) . '–' . $this->formatNumber($to) . '/10';

        return [
            'from'     => $from,
            'to'       => $to,
            'segments' => $segments,
            'zones'    => $zones,
            'zone'     => $zoneText,
            'value'    => $valText,
            'text'     => \trim($zoneText . ' · ' . $valText, ' ·'),
        ];
    }

    /**
     * @param int[] $scale
     * @return array<int, array{label: string, set: bool}>
     */
    private function weightSteps(array $scale, float $from, float $to): array
    {
        $steps   = [['label' => '+', 'set' => $scale[0] > $from]];
        foreach ($scale as $kg) {
            $steps[] = ['label' => (string)$kg, 'set' => $kg >= $from && $kg <= $to];
        }
        $steps[] = ['label' => '+', 'set' => $scale[\count($scale) - 1] < $to];

        return $steps;
    }

    /**
     * Stufe (0-2) eines Fahrlevel-Textes, z. B. "Beginner", "Intermediate", "Advanced/Expert", "Profi"; sonst null.
     */
    private function levelIndex(?string $value): ?int
    {
        $value = \mb_strtolower((string)$value);
        foreach (self::LEVEL_WORDS as $pattern => $idx) {
            if (\preg_match($pattern, $value)) {
                return $idx;
            }
        }

        return null;
    }

    /**
     * Fahreigenschaften (Skala 0-10) – Funktionsattribut => Beschriftung im Diagramm
     */
    private const CHARACTERISTICS = [
        'carving'      => 'Carving',
        'jib'          => 'Jib',
        'powder'       => 'Powder',
        'all_mountain' => 'All-Mountain',
        'jump'         => 'Jump',
    ];

    /**
     * Dimensionen – Funktionsattribut => Beschriftung in der Tabelle (Breiten in mm)
     */
    private const DIMENSIONS = [
        'laenge'  => 'Länge',
        'form'    => 'Form',
        'shape'   => 'Shape',
        'waist'   => 'Waist',
        'nose'    => 'Nose',
        'tail'    => 'Tail',
        'inserts' => 'Inserts',
        'stance'  => 'Stance',
        'setback' => 'Setback',
    ];

    /** Maßstab der Skizze ohne gepflegte Länge (cm) und Taille ohne gepflegte Breiten (mm) */
    private const GENERIC_LENGTH_CM = 156.0;
    private const GENERIC_WAIST_MM  = 250.0;

    /** Einheiten der Dimensionen-Tabelle */
    private const DIMENSION_UNITS = [
        'laenge'  => 'cm',
        'waist'   => 'mm',
        'nose'    => 'mm',
        'tail'    => 'mm',
        'stance'  => 'cm',
        'setback' => 'cm',
    ];

    /** Anzeigename der Insert-Systeme (Schlüssel = normalisierter Wert) */
    private const INSERT_LABELS = [
        'channel' => 'The Channel',
        '2x4'     => '2x4',
        '4x4'     => '4x4',
    ];

    /**
     * Zonen der Seitenansicht je Profiltyp: [von, bis, Art] entlang der Länge (0 = Nose, 1 = Tail).
     * Art: kick (Aufbiegung der Spitze), rocker (Reverse Camber), camber (Positive Camber), flat (Zero Camber)
     */
    private const PROFILE_ZONES = [
        'camber'        => [[0, 0.12, 'kick'], [0.12, 0.88, 'camber'], [0.88, 1, 'kick']],
        'flat'          => [[0, 0.12, 'kick'], [0.12, 0.88, 'flat'], [0.88, 1, 'kick']],
        'rocker'        => [[0, 1, 'rocker']],
        'hybrid camber' => [[0, 0.25, 'rocker'], [0.25, 0.75, 'camber'], [0.75, 1, 'rocker']],
        'hybrid rocker' => [[0, 0.12, 'kick'], [0.12, 0.42, 'camber'], [0.42, 0.58, 'rocker'], [0.58, 0.88, 'camber'], [0.88, 1, 'kick']],
        'flat rocker'   => [[0, 0.25, 'rocker'], [0.25, 0.75, 'flat'], [0.75, 1, 'rocker']],
    ];

    /** Zonenarten => Sprachvariable der Legende */
    private const PROFILE_ZONE_LABELS = [
        'camber' => 'artikel_details_plus_profile_zone_camber',
        'rocker' => 'artikel_details_plus_profile_zone_rocker',
        'flat'   => 'artikel_details_plus_profile_zone_flat',
        'kick'   => 'artikel_details_plus_profile_zone_kick',
    ];

    /**
     * Beispielbegriffe je Profiltyp für den Backend-Tab "Profil-Übersicht" – bei Änderungen an profileType() mitpflegen
     * (der lokale Test prüft, dass jeder Begriff den angegebenen Typ ergibt).
     */
    private const PROFILE_HINTS = [
        'camber'        => ['Camber', 'Positive Camber', 'Traditional Camber'],
        'rocker'        => ['Rocker', 'Reverse Camber', 'Banana'],
        'flat'          => ['Flat', 'Zero Camber', 'Camber/Flat/Camber'],
        'hybrid camber' => ['Hybrid Camber', 'CamRock', 'Directional Camber', 'Rocker/Camber/Rocker', 'Camber/Rocker'],
        'hybrid rocker' => ['Hybrid Rocker', 'Flying V', 'Camber/Rocker/Camber', 'Rocker/Camber'],
        'flat rocker'   => ['Flat Rocker', 'Zero Rocker', 'Rocker/Flat/Rocker'],
    ];

    /** Quellen der Profilerkennung in der Reihenfolge der Artikelseite: Funktionsattribut => Beschriftung */
    private const PROFILE_SOURCES = [
        'profil'  => 'Funktionsattribut profil',
        'profile' => 'Funktionsattribut profile',
        'form'    => 'Funktionsattribut form',
        'shape'   => 'Funktionsattribut shape (Ersatz)',
    ];

    /** Profile der Seitenansicht: Typ => Anzeigename */
    private const PROFILES = [
        'camber'        => 'Camber',
        'rocker'        => 'Rocker',
        'flat'          => 'Flat',
        'hybrid camber' => 'Hybrid Camber',
        'hybrid rocker' => 'Hybrid Rocker',
        'flat rocker'   => 'Flat Rocker',
    ];

    /**
     * Skizzen-Proportionen je Umriss: Anteil der Boardlänge von der Spitze bis zur
     * breitesten Stelle (Nose/Tail), Standard-Setback in cm, Rundung der Enden (0 = spitz, 1 = eckig)
     */
    private const OUTLINES = [
        'twin'             => ['nose' => 0.115, 'tail' => 0.115, 'setback' => 0.0, 'noseTip' => 0.55, 'tailTip' => 0.55],
        'directional twin' => ['nose' => 0.115, 'tail' => 0.115, 'setback' => 1.0, 'noseTip' => 0.55, 'tailTip' => 0.55],
        'directional'      => ['nose' => 0.145, 'tail' => 0.085, 'setback' => 2.0, 'noseTip' => 0.5,  'tailTip' => 0.8],
    ];

    /**
     * Liest Fahreigenschaften und Dimensionen aus den Funktionsattributen des Artikels
     * (mit Fallback auf den Vaterartikel) und stellt sie den Templates bereit.
     * Die Templates finden so immer definierte Variablen vor, auch wenn nichts anzuzeigen ist.
     */
    public function assignSnowboardSpecs(?object $artikel): void
    {
        $smarty = Shop::Smarty();
        $smarty->assign('adpFrontendURL', \rtrim($this->getPlugin()->getPaths()->getFrontendURL(), '/') . '/')
            ->assign('adpSpecsActive', $this->isOn($this->getPlugin()->getConfig()->getValue('artikel_details_plus_merkmalwerte_aktiv')))
            ->assign('adpSpecsCharacteristics', [])
            ->assign('adpSpecsDimensions', [])
            ->assign('adpSpecsBoard', null)
            ->assign('adpProfile', null);

        if ($artikel === null) {
            return;
        }
        $config = $this->getPlugin()->getConfig();
        if (!$this->isOn($config->getValue('artikel_details_plus_merkmalwerte_aktiv'))) {
            return;
        }

        if ($this->isOn($config->getValue('artikel_details_plus_specs_characteristics_aktiv'))) {
            $characteristics = [];
            foreach (self::CHARACTERISTICS as $key => $label) {
                $value = $this->numericAttribute($artikel, $key);
                if ($value === null) {
                    continue;
                }
                $characteristics[] = [
                    'key'   => $key,
                    'label' => $label,
                    'value' => \max(0.0, \min(10.0, $value)),
                    'max'   => 10,
                ];
            }
            // Ein Polygon braucht mindestens drei Ecken
            if (\count($characteristics) >= 3) {
                $smarty->assign('adpSpecsCharacteristics', $characteristics);
            }
        }

        if ($this->isOn($config->getValue('artikel_details_plus_specs_dimensions_aktiv'))) {
            $lengthCm    = $this->boardLength($artikel);
            $outline     = $this->outlineType($artikel);
            $inserts     = $this->insertType($this->attribute($artikel, 'inserts'));
            $stanceCm    = $this->numericAttribute($artikel, 'stance');
            $setbackCm   = $this->numericAttribute($artikel, 'setback');

            $dimensions = [];
            foreach (self::DIMENSIONS as $key => $label) {
                $value = $this->attribute($artikel, $key);
                if ($key === 'laenge') {
                    // Attribut oder gewählte Variation, siehe boardLength()
                    $value = $lengthCm !== null ? $this->formatNumber($lengthCm) : null;
                } elseif ($key === 'inserts') {
                    $value = $inserts !== null ? self::INSERT_LABELS[$inserts] : null;
                }
                if ($value === null || $value === '') {
                    continue;
                }
                $dimensions[$key] = [
                    'label' => $label,
                    'value' => $value,
                    'unit'  => self::DIMENSION_UNITS[$key] ?? '',
                ];
            }
            $smarty->assign('adpSpecsDimensions', $dimensions);

            // Breiten nur, wenn numerisch und positiv; fehlende zeichnet die Skizze generisch ohne Bemaßung
            $widths = [];
            foreach (['nose', 'waist', 'tail'] as $part) {
                $value          = $this->numericAttribute($artikel, $part);
                $widths[$part] = $value !== null && $value > 0 ? $value : null;
            }
            // Skizze, sobald ein Umriss (shape/outline) gepflegt ist oder alle drei Breiten vorliegen
            $hasShape = ($this->attribute($artikel, 'shape') ?? '') !== ''
                || ($this->attribute($artikel, 'outline') ?? '') !== '';
            if ($hasShape || !\in_array(null, $widths, true)) {
                $board = $this->buildBoardSketch(
                    $widths['nose'],
                    $widths['waist'],
                    $widths['tail'],
                    $lengthCm,
                    $outline,
                    $inserts,
                    $stanceCm,
                    $setbackCm
                );
                $aria = [];
                foreach (['nose', 'waist', 'tail'] as $part) {
                    if ($board[$part] !== null) {
                        // Beschriftung wie im Shop gepflegt (z. B. "298,5"), nicht als Float
                        $board[$part]['value'] = $dimensions[$part]['value'];
                        $board[$part]['label'] = $dimensions[$part]['label'];
                        $aria[] = $dimensions[$part]['label'] . ' ' . $dimensions[$part]['value'] . ' mm';
                    }
                }
                if ($board['length'] !== null) {
                    $aria[] = self::DIMENSIONS['laenge'] . ' ' . $board['length']['label'];
                }
                $board['aria'] = \implode(', ', [\ucfirst($outline), ...$aria]);
                $smarty->assign('adpSpecsBoard', $board);
            }
        }

        // Seitenansicht (Camber/Rocker/Flat …): Attribut "profil" hat Vorrang, sonst aus "form"/"shape"
        $profileText = $this->attribute($artikel, 'profil') ?? $this->attribute($artikel, 'profile');
        $profileType = $this->profileType($profileText ?? '');
        if ($profileType === null) {
            $profileText = $this->attribute($artikel, 'form');
            $profileType = $this->profileType($profileText ?? '');
        }
        if ($profileType === null) {
            $profileText = $this->attribute($artikel, 'shape');
            $profileType = $this->profileType($profileText ?? '');
        }
        if ($profileType !== null) {
            $smarty->assign('adpProfile', [
                'type'    => $profileType,
                'label'   => self::PROFILES[$profileType],
                'text'    => \trim((string)$profileText),
                'colored' => $this->isOn($config->getValue('artikel_details_plus_profile_zones_aktiv')),
            ] + $this->buildProfileSketch($profileType));
        }
    }

    /**
     * Profiltyp aus einem Freitext ("Hybrid Camber", "Flying V", "Camber/Rocker/Camber", "Zero" …).
     */
    private function profileType(string $text): ?string
    {
        $t = \mb_strtolower($text);
        if ($t === '') {
            return null;
        }
        // Dreiteilige Notation "X/Y/X": das mittlere Element beschreibt den Bereich zwischen den Füßen
        if (\preg_match('/^\s*(camber|rocker|flat)\s*[\/\-|]\s*(camber|rocker|flat)\s*[\/\-|]\s*(camber|rocker|flat)\s*$/u', $t, $m)) {
            return match ($m[2]) {
                'rocker' => 'hybrid rocker',
                'camber' => 'hybrid camber',
                default  => $m[1] === 'rocker' ? 'flat rocker' : 'flat',
            };
        }
        $has = static fn(string $needle): bool => \str_contains($t, $needle);
        if ($has('flying v') || $has('hybrid rocker') || $has('rocker/camber') || $has('rocker-camber') || $has('rocker camber')) {
            return 'hybrid rocker';
        }
        if ($has('hybrid camber') || $has('camber/rocker') || $has('camber-rocker') || $has('camber rocker') || $has('camrock') || $has('directional camber')) {
            return 'hybrid camber';
        }
        if (($has('flat') || $has('zero')) && $has('rocker')) {
            return 'flat rocker';
        }
        if ($has('flat') || $has('zero')) {
            return 'flat';
        }
        if ($has('rocker') || $has('reverse') || $has('banana')) {
            return 'rocker';
        }
        if ($has('camber')) {
            return 'camber';
        }

        return null;
    }

    /**
     * Seitenansicht als Polylinie: Höhe über dem Boden entlang der Länge (0 = Nose, 1 = Tail),
     * vertikal übertrieben, damit Camber und Rocker auf den ersten Blick zu unterscheiden sind.
     * Zurück kommen der Pfad des Bretts, die Zonen als eigene Teilpfade (für die farbige Darstellung),
     * die Legende, die Bodenlinie und die viewBox.
     *
     * @return array{path: string, zones: list<array{kind: string, path: string}>,
     *               legend: list<array{kind: string, label: string}>, ground: float, viewBox: string}
     */
    private function buildProfileSketch(string $type): array
    {
        $tipKick = 26.0;   // Anhebung der Spitzen
        $camber  = 12.0;   // Höhe des Camber-Bogens
        $tip     = static fn(float $d, float $zone): float => $tipKick * (($zone - $d) / $zone) ** 2;
        $arch    = static fn(float $u, float $from, float $to, float $h): float => $h * \sin(\M_PI * ($u - $from) / ($to - $from));

        $height = function (float $u) use ($type, $tip, $arch, $camber, $tipKick): float {
            $d = \min($u, 1 - $u); // Abstand zur nächsten Spitze
            switch ($type) {
                case 'camber':
                    return $d < 0.12 ? $tip($d, 0.12) : $arch($u, 0.12, 0.88, $camber);
                case 'flat':
                    return $d < 0.12 ? $tip($d, 0.12) : 0.0;
                case 'rocker':
                    return $tipKick * ((($u - 0.5) / 0.5) ** 2);
                case 'hybrid camber':
                    return $d < 0.25 ? $tip($d, 0.25) : $arch($u, 0.25, 0.75, $camber * 0.8);
                case 'flat rocker':
                    return $d < 0.25 ? $tip($d, 0.25) : 0.0;
                case 'hybrid rocker':
                    if ($d < 0.12) {
                        return $tip($d, 0.12);
                    }
                    // Kurzer Rocker zwischen den Füßen: liegt fast flach auf, nur die Ränder heben sich um $rise
                    $rise = 1.5;
                    if ($d < 0.42) {
                        // langer Camber-Bogen unter dem Fuß, vom Kontaktpunkt (0,12) bis auf den Rocker-Rand
                        return $arch($d, 0.12, 0.42, $camber) + $rise * ($d - 0.12) / 0.3;
                    }

                    return $rise * ((($u - 0.5) / 0.08) ** 2);
            }

            return 0.0;
        };

        $x0 = 20.0;
        $x1 = 580.0;
        $ground = 52.0;
        $thick  = 6.0;
        $steps  = 112;
        $point  = static fn(float $u): string => \sprintf(
            '%s %s',
            \round($x0 + ($x1 - $x0) * $u, 1),
            \round($ground - $thick / 2 - $height($u), 1)
        );
        $points = [];
        for ($i = 0; $i <= $steps; $i++) {
            $points[] = $point($i / $steps);
        }

        // Zonen: Teilpfade mit gemeinsamen Randpunkten, damit die Farbwechsel nahtlos sind
        $zones  = [];
        $legend = [];
        $loc    = $this->getPlugin()->getLocalization();
        foreach (self::PROFILE_ZONES[$type] ?? [] as [$from, $to, $kind]) {
            $zonePoints = [$point($from)];
            for ($i = 0; $i <= $steps; $i++) {
                $u = $i / $steps;
                if ($u > $from && $u < $to) {
                    $zonePoints[] = $point($u);
                }
            }
            $zonePoints[] = $point($to);
            $zones[] = ['kind' => $kind, 'path' => 'M ' . \implode(' L ', $zonePoints)];
            if (!isset($legend[$kind])) {
                $label         = (string)$loc->getTranslation(self::PROFILE_ZONE_LABELS[$kind]);
                $legend[$kind] = ['kind' => $kind, 'label' => $label !== '' ? $label : \ucfirst($kind)];
            }
        }

        return [
            'path'    => 'M ' . \implode(' L ', $points),
            'zones'   => $zones,
            'legend'  => \array_values($legend),
            'ground'  => $ground,
            'viewBox' => \sprintf('0 0 600 %s', $ground + 12),
        ];
    }

    /**
     * Boardlänge in cm: Funktionsattribut "laenge" (cm, Werte über 400 gelten als mm),
     * ersatzweise der gewählte Wert einer Variation "Länge"/"Length" beim Kind-Artikel ("156 Wide" => 156).
     */
    private function boardLength(object $artikel): ?float
    {
        $value = $this->numericAttribute($artikel, 'laenge') ?? $this->numericAttribute($artikel, 'length');
        if ($value !== null && $value > 0) {
            return $value > 400 ? $value / 10 : $value;
        }
        foreach ($artikel->oVariationenNurKind_arr ?? [] as $variation) {
            if (!\preg_match('/l[äa]ng|length|size|gr[öo]/iu', (string)($variation->cName ?? ''))) {
                continue;
            }
            foreach ($variation->Werte ?? [] as $wert) {
                if (\preg_match('/^\s*(\d{2,3}(?:[.,]\d)?)/', (string)($wert->cName ?? ''), $m)) {
                    $cm = (float)\str_replace(',', '.', $m[1]);
                    if ($cm >= 80 && $cm <= 200) {
                        return $cm;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Umriss der Skizze: Attribut "outline" (twin | directional | directional twin) hat Vorrang,
     * sonst wird aus den Texten von "form" und "shape" erkannt. Standard ist Twin.
     */
    private function outlineType(object $artikel): string
    {
        // Explizites "outline" hat Vorrang vor der Erkennung aus form/shape
        $explicit = $this->detectOutline($this->attribute($artikel, 'outline') ?? '');
        if ($explicit !== null) {
            return $explicit;
        }

        return $this->detectOutline(\implode(' ', \array_filter([
            $this->attribute($artikel, 'form'),
            $this->attribute($artikel, 'shape'),
        ]))) ?? 'twin';
    }

    /**
     * "directional twin", "directional" oder "twin" aus einem Text; null, wenn keines der Wörter vorkommt.
     */
    private function detectOutline(string $text): ?string
    {
        $text = \mb_strtolower($text);
        if (\str_contains($text, 'directional')) {
            return \str_contains($text, 'twin') ? 'directional twin' : 'directional';
        }

        return \str_contains($text, 'twin') ? 'twin' : null;
    }

    /**
     * Insert-System aus dem Attribut "inserts": channel | 2x4 | 4x4, sonst null (keine Inserts in der Skizze).
     */
    private function insertType(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = \mb_strtolower($value);
        if (\str_contains($value, 'channel')) {
            return 'channel';
        }
        if (\preg_match('/2\s*x\s*4/', $value)) {
            return '2x4';
        }
        if (\preg_match('/4\s*x\s*4/', $value)) {
            return '4x4';
        }

        return null;
    }

    private function formatNumber(float $value): string
    {
        return \fmod($value, 1.0) === 0.0 ? (string)(int)$value : \str_replace('.', ',', (string)$value);
    }

    /**
     * Geometrie für die Board-Skizze (Draufsicht, Nose links, Tail rechts) in SVG-Einheiten.
     * Die Boardlänge wird auf 560 Einheiten skaliert, alle Breiten im selben Maßstab – das Board
     * erscheint damit im echten Seitenverhältnis. Ohne bekannte Länge gilt ein typisches
     * Verhältnis von 5,2:1 zur breitesten Stelle, und die Längenbemaßung entfällt.
     *
     * @return array{
     *     viewBox: string, path: string, cy: float, labelY: float,
     *     length: array{x1: float, x2: float, y: float, label: string}|null,
     *     inserts: array{type: string, holes: list<array{cx: float, cy: float, r: float}>,
     *                    slots: list<array{x: float, y: float, w: float, h: float}>}|null,
     *     nose: array{x: float, y1: float, y2: float, value: float},
     *     waist: array{x: float, y1: float, y2: float, value: float},
     *     tail: array{x: float, y1: float, y2: float, value: float}
     * }
     */
    /**
     * Draufsicht des Boards. Fehlende Breiten (null) werden aus den vorhandenen bzw. typischen Proportionen
     * ergänzt und nicht bemaßt; ohne Länge gilt 156 cm als Maßstab ohne Längenmaß.
     */
    private function buildBoardSketch(
        ?float $nose,
        ?float $waist,
        ?float $tail,
        ?float $lengthCm,
        string $outline,
        ?string $inserts,
        ?float $stanceCm,
        ?float $setbackCm
    ): array {
        $prop     = self::OUTLINES[$outline] ?? self::OUTLINES['twin'];
        $lengthMm = ($lengthCm ?? self::GENERIC_LENGTH_CM) * 10;
        $scale    = 560.0 / $lengthMm;
        $x0       = 20.0;
        $x1       = 580.0;

        [$noseW, $waistW, $tailW] = $this->completeWidths($nose, $waist, $tail, $outline);
        $hasWidthLabel = $nose !== null || $waist !== null || $tail !== null;

        $nh = $noseW * $scale / 2;
        $wh = $waistW * $scale / 2;
        $th = $tailW * $scale / 2;
        $maxHalf = \max($nh, $wh, $th);

        $yTop = $lengthCm !== null ? 40.0 : 16.0;
        $cy   = $yTop + $maxHalf;
        $labelY = $cy + $maxHalf + 30;
        $height = $hasWidthLabel ? $labelY + 12 : $cy + $maxHalf + 16;

        // breiteste Stellen und Taille entlang der Länge
        $xN = $x0 + 560 * $prop['nose'];
        $xT = $x1 - 560 * $prop['tail'];
        $xW = ($xN + $xT) / 2;

        $r = static fn(float $v): float => \round($v, 1);

        // Enden: kubische Kurven zwischen Spitze und breitester Stelle. "k" steuert die Rundung
        // (0,5 = gleichmäßig rund, 0,8 = stumpf), an der breitesten Stelle ist die Tangente waagerecht.
        $tipOut = static function (float $xs, float $ys, float $xe, float $ye, float $k) use ($r): string {
            return \sprintf(
                'C %s %s %s %s %s %s',
                $r($xs),
                $r($ys + ($ye - $ys) * $k),
                $r($xs + ($xe - $xs) * (1 - $k) * 0.9),
                $r($ye),
                $r($xe),
                $r($ye)
            );
        };
        // Exaktes Spiegelbild von $tipOut: waagerechter Handle im selben Abstand zur Spitze
        $tipIn = static function (float $xs, float $ys, float $xe, float $ye, float $k) use ($r): string {
            return \sprintf(
                'C %s %s %s %s %s %s',
                $r($xe - ($xe - $xs) * (1 - $k) * 0.9),
                $r($ys),
                $r($xe),
                $r($ys + ($ye - $ys) * (1 - $k)),
                $r($xe),
                $r($ye)
            );
        };
        // Sidecut: S-Kurve mit waagerechten Tangenten
        $side = static function (float $xs, float $ys, float $xe, float $ye) use ($r): string {
            $mid = ($xs + $xe) / 2;

            return \sprintf('C %s %s %s %s %s %s', $r($mid), $r($ys), $r($mid), $r($ye), $r($xe), $r($ye));
        };

        $path = \sprintf('M %s %s ', $r($x0), $r($cy))
            . $tipOut($x0, $cy, $xN, $cy - $nh, $prop['noseTip']) . ' '
            . $side($xN, $cy - $nh, $xW, $cy - $wh) . ' '
            . $side($xW, $cy - $wh, $xT, $cy - $th) . ' '
            . $tipIn($xT, $cy - $th, $x1, $cy, $prop['tailTip']) . ' '
            . $tipOut($x1, $cy, $xT, $cy + $th, $prop['tailTip']) . ' '
            . $side($xT, $cy + $th, $xW, $cy + $wh) . ' '
            . $side($xW, $cy + $wh, $xN, $cy + $nh) . ' '
            . $tipIn($xN, $cy + $nh, $x0, $cy, $prop['noseTip']) . ' Z';

        // Inserts: Referenzstance mittig (plus Setback Richtung Tail), Maße in mm
        $insertData = null;
        if ($inserts !== null) {
            $stanceMm  = ($stanceCm ?? \min(60.0, \max(40.0, $lengthMm / 10 * 0.36))) * 10;
            $setbackMm = ($setbackCm ?? $prop['setback']) * 10;
            $centerX   = $x0 + ($lengthMm / 2 + $setbackMm) * $scale;
            $feet      = [$centerX - $stanceMm / 2 * $scale, $centerX + $stanceMm / 2 * $scale];
            $holes     = [];
            $slots     = [];
            if ($inserts === 'channel') {
                $len = 170 * $scale;
                $wid = \max(4.0, 12 * $scale);
                foreach ($feet as $fx) {
                    $slots[] = ['x' => $r($fx - $len / 2), 'y' => $r($cy - $wid / 2), 'w' => $r($len), 'h' => $r($wid)];
                }
            } else {
                $cols   = $inserts === '2x4' ? [-50, -30, -10, 10, 30, 50] : [-40, 0, 40];
                $radius = \max(2.4, 4 * $scale);
                foreach ($feet as $fx) {
                    foreach ($cols as $dx) {
                        foreach ([-20, 20] as $dy) {
                            $holes[] = ['cx' => $r($fx + $dx * $scale), 'cy' => $r($cy + $dy * $scale), 'r' => $r($radius)];
                        }
                    }
                }
            }
            $insertData = ['type' => $inserts, 'holes' => $holes, 'slots' => $slots];
        }

        return [
            'viewBox' => \sprintf('0 0 600 %s', $r($height)),
            'path'    => $path,
            'cy'      => $r($cy),
            'labelY'  => $r($labelY),
            'length'  => $lengthCm !== null
                ? ['x1' => $x0, 'x2' => $x1, 'y' => 18.0, 'label' => $this->formatNumber($lengthCm) . ' cm']
                : null,
            'inserts' => $insertData,
            // Bemaßung nur für gepflegte Breiten
            'nose'    => $nose !== null ? ['x' => $r($xN), 'y1' => $r($cy - $nh), 'y2' => $r($cy + $nh), 'value' => $nose] : null,
            'waist'   => $waist !== null ? ['x' => $r($xW), 'y1' => $r($cy - $wh), 'y2' => $r($cy + $wh), 'value' => $waist] : null,
            'tail'    => $tail !== null ? ['x' => $r($xT), 'y1' => $r($cy - $th), 'y2' => $r($cy + $th), 'value' => $tail] : null,
        ];
    }

    /**
     * Ergänzt fehlende Breiten (mm): Nose und Tail liegen typisch 45 mm über der Taille, beim
     * Directional-Umriss die Nose etwas breiter als das Tail. Ohne jede Breite gilt eine Taille von 250 mm.
     *
     * @return array{0: float, 1: float, 2: float} Nose, Waist, Tail
     */
    private function completeWidths(?float $nose, ?float $waist, ?float $tail, string $outline): array
    {
        $offset = $outline === 'directional' ? [50.0, 40.0] : [45.0, 45.0];
        $waist ??= match (true) {
            $nose !== null && $tail !== null => \min($nose, $tail) - 45,
            $nose !== null                   => $nose - $offset[0],
            $tail !== null                   => $tail - $offset[1],
            default                          => self::GENERIC_WAIST_MM,
        };
        $waist = \max(150.0, $waist);

        return [$nose ?? $waist + $offset[0], $waist, $tail ?? $waist + $offset[1]];
    }

    /**
     * Funktionsattribut vom Artikel, ersatzweise vom Vaterartikel (Keys sind im Core kleingeschrieben),
     * zuletzt der aus einem zugeordneten Merkmal abgeleitete Wert (siehe loadFeatureAttributes()).
     */
    private function attribute(object $artikel, string $name): ?string
    {
        if (isset($artikel->FunktionsAttribute[$name])) {
            return \trim((string)$artikel->FunktionsAttribute[$name]);
        }
        if (isset($artikel->VaterFunktionsAttribute[$name])) {
            return \trim((string)$artikel->VaterFunktionsAttribute[$name]);
        }

        return $this->featureAttributes[$name] ?? null;
    }

    /**
     * Merkmal-Zuordnung: Feld => [Einstellung, Funktionsattribute der Gruppe].
     * Ist eines der Funktionsattribute gepflegt, bleibt das Merkmal für die ganze Gruppe unbeachtet.
     */
    private const FEATURE_FIELDS = [
        'flex'           => ['artikel_details_plus_merkmal_flex', ['flex', 'flex_ab', 'flex_bis']],
        'fahrlevel'      => ['artikel_details_plus_merkmal_fahrlevel', ['fahrlevel_ab', 'fahrlevel_bis']],
        'koerpergewicht' => ['artikel_details_plus_merkmal_koerpergewicht', ['koerpergewicht_ab', 'koerpergewicht_bis']],
        'laenge'         => ['artikel_details_plus_merkmal_laenge', ['laenge', 'length']],
        'form'           => ['artikel_details_plus_merkmal_form', ['form']],
        'shape'          => ['artikel_details_plus_merkmal_shape', ['shape']],
        'inserts'        => ['artikel_details_plus_merkmal_inserts', ['inserts']],
    ];

    /** Flex-Wörter in Merkmalwerten => Bereich auf der Skala 1-10 (zusammengesetzte zuerst prüfen) */
    private const FLEX_WORDS = [
        '/medium\s*stiff|mittel\s*(?:hart|steif)/u' => [7, 8],
        '/medium\s*soft|mittel\s*weich/u'           => [3, 4],
        '/stiff|hart|steif/u'                       => [9, 10],
        '/soft|weich/u'                             => [1, 2],
        '/medium|mittel/u'                          => [5, 6],
    ];

    /**
     * Ersatz-Attribute des aktuellen Artikels aus den zugeordneten Merkmalen, gleiche Schlüssel wie die Funktionsattribute
     *
     * @var array<string, string>
     */
    private array $featureAttributes = [];

    /**
     * Liest die im Pluginmenü zugeordneten Merkmale des Artikels (ersatzweise des Vaterartikels) in der
     * Standardsprache und übersetzt sie in Ersatz-Attribute. Felder, deren Funktionsattribute gepflegt sind,
     * werden übersprungen – so ändert sich an Artikeln mit Funktionsattributen nichts.
     */
    public function loadFeatureAttributes(?object $artikel): void
    {
        $this->featureAttributes = [];
        if ($artikel === null || (int)($artikel->kArtikel ?? 0) <= 0) {
            return;
        }
        $config = $this->getPlugin()->getConfig();
        $fields = [];
        foreach (self::FEATURE_FIELDS as $field => [$setting, $attributes]) {
            $featureID = (int)$config->getValue($setting);
            if ($featureID <= 0) {
                continue;
            }
            foreach ($attributes as $name) {
                if (($this->attribute($artikel, $name) ?? '') !== '') {
                    continue 2;
                }
            }
            $fields[$field] = $featureID;
        }
        if (\count($fields) === 0) {
            return;
        }

        $productID = (int)$artikel->kArtikel;
        $parentID  = (int)($artikel->kVaterArtikel ?? 0);
        $rows      = $this->getDB()->getObjects(
            "SELECT am.kArtikel, am.kMerkmal, mws.cWert
                FROM tartikelmerkmal am
                JOIN tmerkmalwert mw
                    ON mw.kMerkmalWert = am.kMerkmalWert
                JOIN tmerkmalwertsprache mws
                    ON mws.kMerkmalWert = am.kMerkmalWert
                JOIN tsprache s
                    ON s.kSprache = mws.kSprache
                    AND s.cShopStandard = 'Y'
                WHERE am.kArtikel IN (" . \implode(',', \array_unique(\array_filter([$productID, $parentID]))) . ')
                    AND am.kMerkmal IN (' . \implode(',', \array_unique($fields)) . ')
                ORDER BY mw.nSort, mws.cWert'
        );
        // Werte je Merkmal, getrennt nach Artikel und Vaterartikel
        $values = [];
        foreach ($rows as $row) {
            $text = \trim(\html_entity_decode(\strip_tags((string)$row->cWert), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
            if ($text !== '') {
                $values[(int)$row->kMerkmal][(int)$row->kArtikel === $productID ? 'own' : 'parent'][] = $text;
            }
        }

        foreach ($fields as $field => $featureID) {
            // Merkmale des Artikels selbst haben Vorrang vor denen des Vaterartikels
            $texts = $values[$featureID]['own'] ?? $values[$featureID]['parent'] ?? [];
            if (\count($texts) > 0) {
                $this->featureAttributes += $this->featureToAttributes($field, \array_values(\array_unique($texts)));
            }
        }
    }

    /**
     * Übersetzt die Merkmalwerte eines Feldes in Ersatz-Attribute.
     *
     * @param string[] $texts
     * @return array<string, string>
     */
    private function featureToAttributes(string $field, array $texts): array
    {
        switch ($field) {
            case 'flex':
                $range = $this->flexRange($texts);
                return $range === null ? [] : ['flex_ab' => (string)$range[0], 'flex_bis' => (string)$range[1]];
            case 'fahrlevel':
                // Jeder Wert ist eine Stufe; "Beginner - Advanced" / "Anfänger bis Profi" in einem Wert sind zwei
                $found = [];
                foreach ($texts as $text) {
                    foreach (\preg_split('/\s+(?:-|–|bis|to)\s+/u', $text) ?: [] as $part) {
                        $idx = $this->levelIndex($part);
                        if ($idx !== null) {
                            $found[] = $idx;
                        }
                    }
                }
                $keys = \array_keys(self::LEVELS);
                return \count($found) === 0 ? [] : [
                    'fahrlevel_ab'  => $keys[\min($found)],
                    'fahrlevel_bis' => $keys[\max($found)],
                ];
            case 'koerpergewicht':
                $numbers = \array_filter(
                    $this->numbersIn(\implode(' ', $texts)),
                    static fn(float $kg): bool => $kg >= 20 && $kg <= 200
                );
                return \count($numbers) === 0 ? [] : [
                    'koerpergewicht_ab'  => (string)\min($numbers),
                    'koerpergewicht_bis' => (string)\max($numbers),
                ];
            case 'laenge':
                // Nur eine eindeutige Länge übernehmen; mehrere Längen (Vaterartikel) überlässt man der Variation
                $lengths = [];
                foreach ($this->numbersIn(\implode(' ', $texts)) as $number) {
                    $number = $number > 400 ? $number / 10 : $number;
                    if ($number >= 80 && $number <= 200) {
                        $lengths[(string)$number] = true;
                    }
                }
                return \count($lengths) === 1 ? ['laenge' => (string)\array_key_first($lengths)] : [];
            default:
                return [$field => \implode(', ', $texts)];
        }
    }

    /**
     * Flex-Bereich aus Merkmalwerten: Zahlen 1-10 ("6", "5-7", "6/10"), sonst Wörter (soft … stiff).
     *
     * @param string[] $texts
     * @return array{0: float, 1: float}|null
     */
    private function flexRange(array $texts): ?array
    {
        $numbers = [];
        $words   = [];
        foreach ($texts as $text) {
            // "6/10" ist Wert von Skala, die 10 zählt nicht
            $clean   = (string)\preg_replace('~/\s*10\b~', '', $text);
            $numbers = [...$numbers, ...\array_filter(
                $this->numbersIn($clean),
                static fn(float $n): bool => $n >= 1 && $n <= 10
            )];
            $lower = \mb_strtolower(\str_replace(['-', '_'], ' ', $text));
            foreach (self::FLEX_WORDS as $pattern => $range) {
                if (\preg_match($pattern, $lower)) {
                    $words = [...$words, ...$range];
                    break;
                }
            }
        }
        if (\count($numbers) > 0) {
            return [\min($numbers), \max($numbers)];
        }

        return \count($words) > 0 ? [(float)\min($words), (float)\max($words)] : null;
    }

    /**
     * Alle Zahlen eines Textes (Dezimalkomma erlaubt, Bindestriche gelten als Trenner, nicht als Minus).
     *
     * @return float[]
     */
    private function numbersIn(string $text): array
    {
        \preg_match_all('/\d+(?:[.,]\d+)?/u', $text, $hits);

        return \array_map(static fn(string $n): float => (float)\str_replace(',', '.', $n), $hits[0]);
    }

    private function numericAttribute(object $artikel, string $name): ?float
    {
        $value = $this->attribute($artikel, $name);
        if ($value === null) {
            return null;
        }
        $value = \str_replace(',', '.', $value);

        return \is_numeric($value) ? (float)$value : null;
    }

    /**
     * Backend-Tabs aus info.xml (Customlink). Der Core ruft das für jeden Customlink bei jedem Aufruf auf,
     * deshalb verarbeitet der Tab nur sein eigenes Formularfeld adp_profile_term.
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        if ($tabName !== 'Profil-Übersicht') {
            return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
        }
        $term = null;
        if (isset($_POST['adp_profile_term']) && Form::validateToken()) {
            $term = \trim((string)$_POST['adp_profile_term']);
        }
        $colored = $this->isOn($this->getPlugin()->getConfig()->getValue('artikel_details_plus_profile_zones_aktiv'));

        $types = [];
        foreach (self::PROFILES as $type => $label) {
            $types[] = $this->profileView($type, $colored) + ['hints' => self::PROFILE_HINTS[$type] ?? []];
        }

        return $smarty->assign('adpMenuID', $menuID)
            ->assign('adpTerm', $term)
            ->assign('adpTermProfile', $term !== null && $term !== '' ? $this->profileView($this->profileType($term), $colored) : null)
            ->assign('adpProfileTypes', $types)
            ->assign('adpProfileTerms', $this->profileTerms($colored))
            ->assign('adpProfileSvgTpl', $this->getPlugin()->getPaths()->getFrontendPath() . 'template/productdetails/profile_svg.tpl')
            ->assign('adpFrontendCss', $this->getPlugin()->getPaths()->getFrontendURL() . 'css/artikel_details_plus.css?v='
                . $this->getPlugin()->getMeta()->getVersion())
            ->fetch($this->getPlugin()->getPaths()->getAdminPath() . 'templates/profiles.tpl');
    }

    /**
     * Anzeige-Daten eines Profiltyps wie auf der Artikelseite; null = nicht erkannt (keine Skizze).
     *
     * @return array<string, mixed>|null
     */
    private function profileView(?string $type, bool $colored): ?array
    {
        if ($type === null) {
            return null;
        }

        return ['type' => $type, 'label' => self::PROFILES[$type], 'colored' => $colored] + $this->buildProfileSketch($type);
    }

    /**
     * Alle Profil-Begriffe, die im Shop an Artikeln hängen: Funktionsattribute profil/profile/form/shape und
     * die in der Merkmal-Zuordnung gewählten Merkmale für Form und Shape (Standardsprache), gruppiert nach
     * Quelle und Wert, mit Artikelanzahl, Beispielen und erkanntem Typ.
     *
     * @return list<array<string, mixed>>
     */
    private function profileTerms(bool $colored): array
    {
        $db   = $this->getDB();
        $rows = [];
        foreach (
            $db->getObjects(
                "SELECT LOWER(aa.cName) AS source, aa.cWert AS term, a.kArtikel, a.cArtNr, a.cName
                    FROM tartikelattribut aa
                    JOIN tartikel a
                        ON a.kArtikel = aa.kArtikel
                    WHERE LOWER(aa.cName) IN ('profil', 'profile', 'form', 'shape')"
            ) as $row
        ) {
            $row->label = self::PROFILE_SOURCES[$row->source] ?? $row->source;
            $rows[]     = $row;
        }
        $config = $this->getPlugin()->getConfig();
        foreach (['form' => 'Form', 'shape' => 'Shape (Ersatz)'] as $field => $fieldLabel) {
            $featureID = (int)$config->getValue(self::FEATURE_FIELDS[$field][0]);
            if ($featureID <= 0) {
                continue;
            }
            foreach (
                $db->getObjects(
                    "SELECT m.cName AS featureName, mws.cWert AS term, a.kArtikel, a.cArtNr, a.cName
                        FROM tartikelmerkmal am
                        JOIN tartikel a
                            ON a.kArtikel = am.kArtikel
                        JOIN tmerkmal m
                            ON m.kMerkmal = am.kMerkmal
                        JOIN tmerkmalwertsprache mws
                            ON mws.kMerkmalWert = am.kMerkmalWert
                        JOIN tsprache s
                            ON s.kSprache = mws.kSprache
                            AND s.cShopStandard = 'Y'
                        WHERE am.kMerkmal = " . $featureID
                ) as $row
            ) {
                $row->source = 'merkmal_' . $field;
                $row->label  = 'Merkmal „' . $row->featureName . '“ → ' . $fieldLabel;
                $rows[]      = $row;
            }
        }

        $order = \array_flip(['profil', 'profile', 'form', 'merkmal_form', 'shape', 'merkmal_shape']);
        $terms = [];
        foreach ($rows as $row) {
            $term = \trim(\html_entity_decode(\strip_tags((string)$row->term), \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
            if ($term === '') {
                continue;
            }
            $key = $row->source . "\0" . \mb_strtolower($term);
            if (!isset($terms[$key])) {
                $terms[$key] = [
                    'source'   => $row->source,
                    'label'    => $row->label,
                    'fallback' => \str_contains($row->source, 'shape'),
                    'term'     => $term,
                    'articles' => [],
                    'profile'  => $this->profileView($this->profileType($term), $colored),
                    'sort'     => $order[$row->source] ?? 99,
                ];
            }
            $terms[$key]['articles'][(int)$row->kArtikel] = \trim($row->cArtNr . ' ' . \html_entity_decode((string)$row->cName, \ENT_QUOTES | \ENT_HTML5, 'UTF-8'));
        }
        foreach ($terms as &$item) {
            $item['count']    = \count($item['articles']);
            $item['examples'] = \array_slice(\array_values($item['articles']), 0, 3);
            unset($item['articles']);
        }
        unset($item);
        // Nicht erkannte Profil-Begriffe zuerst, dann Quelle, Häufigkeit und Begriff
        \usort($terms, static fn(array $a, array $b): int => [
            $a['fallback'] || $a['profile'] !== null, $a['sort'], -$a['count'], \mb_strtolower($a['term'])
        ] <=> [
            $b['fallback'] || $b['profile'] !== null, $b['sort'], -$b['count'], \mb_strtolower($b['term'])
        ]);

        return $terms;
    }

    private function handleCheaperForm(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST['adp_cheaper_submit'])) {
            return;
        }
        // Formular deaktiviert: POST ignorieren, sonst könnte weiterhin Mail ausgelöst werden
        if (!$this->isOn($this->getPlugin()->getConfig()->getValue('artikel_details_plus_cheaper_aktiv'))) {
            return;
        }

        $post     = static fn(string $key): string => \is_string($_POST[$key] ?? null) ? \trim($_POST[$key]) : '';
        $kArtikel = (int)($_POST['adp_artikel_id'] ?? 0);

        // PRG: saubere Redirect-URL ohne eigene GET-Params aufbauen
        $parts = parse_url($_SERVER['REQUEST_URI'] ?? '/');
        $path  = $parts['path'] ?? '/';
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            unset($query['adp_cheaper'], $query['adp_err'], $query['adp_ka']);
        }
        $uri = $path . ($query ? '?' . http_build_query($query) : '');
        $sep = $query ? '&' : '?';

        $redirectError = function (string $code) use ($uri, $sep, $kArtikel): void {
            header('Location: ' . $uri . $sep . 'adp_cheaper=error&adp_err=' . $code . '&adp_ka=' . $kArtikel, true, 303);
            exit;
        };

        if (!Form::validateToken()) {
            $redirectError('csrf');
        }

        // Honeypot: Bots bekommen einen stillen Schein-Erfolg
        if (Form::honeypotWasFilledOut($_POST)) {
            header('Location: ' . $uri . $sep . 'adp_cheaper=success&adp_ka=' . $kArtikel, true, 303);
            exit;
        }

        $email     = \filter_var($post('adp_email'), \FILTER_VALIDATE_EMAIL);
        $url       = \filter_var($post('adp_url'), \FILTER_VALIDATE_URL);
        $nachricht = \mb_substr(\strip_tags($post('adp_nachricht')), 0, 2000);

        if (!$email || !$url || !\in_array(\parse_url($url, \PHP_URL_SCHEME), ['http', 'https'], true)) {
            $redirectError('validation');
        }

        // Artikelname aus der Datenbank statt aus dem Formular, damit der Betreff nicht manipulierbar ist
        $product     = $kArtikel > 0 ? $this->getDB()->select('tartikel', 'kArtikel', $kArtikel) : null;
        $artikelName = $product !== null ? (string)$product->cName : '';

        $config  = Shop::getSettings([\CONF_EMAILS]);
        $toEmail = $config['emails']['email_master_absender'] ?? '';

        if (empty($toEmail)) {
            $redirectError('config');
        }

        $data             = new \stdClass();
        $mailData         = new \stdClass();
        $mailData->toEmail      = $toEmail;
        $mailData->toName       = '';
        $mailData->replyToEmail = (string)$email;
        $mailData->replyToName  = (string)$email;
        $data->mail         = $mailData;
        $data->cEmail       = (string)$email;
        $data->cURL         = (string)$url;
        $data->cNachricht   = $nachricht;
        $data->kArtikel     = $kArtikel;
        $data->cArtikelName = $artikelName;

        try {
            $mailer  = Shop::Container()->getMailer();
            $mailObj = (new Mail())->createFromTemplateID(
                'kPlugin_' . $this->getPlugin()->getID() . '_guenstigergesehen',
                $data
            );
            $sent = $mailer->send($mailObj);
        } catch (\Exception $e) {
            Shop::Container()->getLogService()->error(
                'ArtikelDetailsPlus cheaper form: ' . $e->getMessage()
            );
            $sent = false;
        }
        if ($sent !== true) {
            $redirectError('mail');
        }

        header('Location: ' . $uri . $sep . 'adp_cheaper=success&adp_ka=' . $kArtikel, true, 303);
        exit;
    }
}
