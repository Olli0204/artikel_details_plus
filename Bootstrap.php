<?php declare(strict_types=1);

namespace Plugin\artikel_details_plus;

use JTL\Events\Dispatcher;
use JTL\Helpers\Form;
use JTL\Mail\Mail\Mail;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;

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
     * Fahrlevel-Stufen in der Reihenfolge der Leiste (Werte der Funktionsattribute fahrlevel_ab/_bis)
     */
    private const LEVELS = ['Beginner', 'Advanced', 'Professional'];

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
                $steps = [];
                foreach (self::LEVELS as $idx => $label) {
                    $steps[] = ['label' => $label, 'set' => $idx >= $fromIdx && $idx <= $toIdx];
                }
                $smarty->assign('adpLevel', [
                    'from'  => self::LEVELS[$fromIdx],
                    'to'    => self::LEVELS[$toIdx],
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

    private function levelIndex(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        foreach (self::LEVELS as $idx => $label) {
            if (\strcasecmp($label, $value) === 0) {
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
            ->assign('adpSpecsBoard', null);

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

            $nose  = $this->numericAttribute($artikel, 'nose');
            $waist = $this->numericAttribute($artikel, 'waist');
            $tail  = $this->numericAttribute($artikel, 'tail');
            if ($nose !== null && $waist !== null && $tail !== null && $nose > 0 && $waist > 0 && $tail > 0) {
                $board = $this->buildBoardSketch(
                    $nose,
                    $waist,
                    $tail,
                    $lengthCm,
                    $outline,
                    $inserts,
                    $stanceCm,
                    $setbackCm
                );
                // Beschriftung wie im Shop gepflegt (z. B. "298,5"), nicht als Float
                foreach (['nose', 'waist', 'tail'] as $part) {
                    $board[$part]['value'] = $dimensions[$part]['value'];
                }
                $smarty->assign('adpSpecsBoard', $board);
            }
        }
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
    private function buildBoardSketch(
        float $nose,
        float $waist,
        float $tail,
        ?float $lengthCm,
        string $outline,
        ?string $inserts,
        ?float $stanceCm,
        ?float $setbackCm
    ): array {
        $prop     = self::OUTLINES[$outline] ?? self::OUTLINES['twin'];
        $lengthMm = $lengthCm !== null ? $lengthCm * 10 : \max($nose, $waist, $tail) * 5.2;
        $scale    = 560.0 / $lengthMm;
        $x0       = 20.0;
        $x1       = 580.0;

        $nh = $nose * $scale / 2;
        $wh = $waist * $scale / 2;
        $th = $tail * $scale / 2;
        $maxHalf = \max($nh, $wh, $th);

        $yTop = $lengthCm !== null ? 40.0 : 16.0;
        $cy   = $yTop + $maxHalf;
        $labelY = $cy + $maxHalf + 30;
        $height = $labelY + 12;

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
        $tipIn = static function (float $xs, float $ys, float $xe, float $ye, float $k) use ($r): string {
            return \sprintf(
                'C %s %s %s %s %s %s',
                $r($xs + ($xe - $xs) * (1 - $k) * 0.9),
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
            'nose'    => ['x' => $r($xN), 'y1' => $r($cy - $nh), 'y2' => $r($cy + $nh), 'value' => $nose],
            'waist'   => ['x' => $r($xW), 'y1' => $r($cy - $wh), 'y2' => $r($cy + $wh), 'value' => $waist],
            'tail'    => ['x' => $r($xT), 'y1' => $r($cy - $th), 'y2' => $r($cy + $th), 'value' => $tail],
        ];
    }

    /**
     * Funktionsattribut vom Artikel, ersatzweise vom Vaterartikel (Keys sind im Core kleingeschrieben).
     */
    private function attribute(object $artikel, string $name): ?string
    {
        if (isset($artikel->FunktionsAttribute[$name])) {
            return \trim((string)$artikel->FunktionsAttribute[$name]);
        }
        if (isset($artikel->VaterFunktionsAttribute[$name])) {
            return \trim((string)$artikel->VaterFunktionsAttribute[$name]);
        }

        return null;
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
