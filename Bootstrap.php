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
            ->assign('adpLevel', null);

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
        'form'  => 'Form',
        'shape' => 'Shape',
        'waist' => 'Waist',
        'nose'  => 'Nose',
        'tail'  => 'Tail',
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
            $dimensions = [];
            foreach (self::DIMENSIONS as $key => $label) {
                $value = $this->attribute($artikel, $key);
                if ($value === null || $value === '') {
                    continue;
                }
                $dimensions[$key] = [
                    'label' => $label,
                    'value' => $value,
                    'unit'  => \in_array($key, ['waist', 'nose', 'tail'], true) ? 'mm' : '',
                ];
            }
            $smarty->assign('adpSpecsDimensions', $dimensions);

            $nose  = $this->numericAttribute($artikel, 'nose');
            $waist = $this->numericAttribute($artikel, 'waist');
            $tail  = $this->numericAttribute($artikel, 'tail');
            if ($nose !== null && $waist !== null && $tail !== null && $nose > 0 && $waist > 0 && $tail > 0) {
                $board = $this->buildBoardSketch($nose, $waist, $tail);
                // Beschriftung wie im Shop gepflegt (z. B. "298,5"), nicht als Float
                foreach (['nose', 'waist', 'tail'] as $part) {
                    $board[$part]['value'] = $dimensions[$part]['value'];
                }
                $smarty->assign('adpSpecsBoard', $board);
            }
        }
    }

    /**
     * Geometrie für die Board-Skizze (SVG viewBox 0 0 600 220): Nose links, Tail rechts.
     * Die breiteste Stelle wird auf 140 Einheiten skaliert, die anderen proportional dazu.
     *
     * @return array{path: string, nose: array{x: int, y1: float, y2: float, value: float},
     *               waist: array{x: int, y1: float, y2: float, value: float},
     *               tail: array{x: int, y1: float, y2: float, value: float}}
     */
    private function buildBoardSketch(float $nose, float $waist, float $tail): array
    {
        $cy    = 100.0;
        $scale = 140.0 / \max($nose, $waist, $tail);
        $nh    = \round($nose * $scale / 2, 1);
        $wh    = \round($waist * $scale / 2, 1);
        $th    = \round($tail * $scale / 2, 1);

        $path = \sprintf(
            'M 20 %1$s Q 20 %2$s 80 %2$s C 200 %2$s 200 %3$s 300 %3$s C 400 %3$s 400 %4$s 520 %4$s Q 580 %4$s 580 %1$s'
            . ' Q 580 %5$s 520 %5$s C 400 %5$s 400 %6$s 300 %6$s C 200 %6$s 200 %7$s 80 %7$s Q 20 %7$s 20 %1$s Z',
            $cy,
            $cy - $nh,
            $cy - $wh,
            $cy - $th,
            $cy + $th,
            $cy + $wh,
            $cy + $nh
        );

        return [
            'path'  => $path,
            'nose'  => ['x' => 80, 'y1' => $cy - $nh, 'y2' => $cy + $nh, 'value' => $nose],
            'waist' => ['x' => 300, 'y1' => $cy - $wh, 'y2' => $cy + $wh, 'value' => $waist],
            'tail'  => ['x' => 520, 'y1' => $cy - $th, 'y2' => $cy + $th, 'value' => $tail],
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
