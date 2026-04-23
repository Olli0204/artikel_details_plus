<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Mail\Mail\Mail;
use JTL\Shop;

/** @var \JTL\Plugin\PluginInterface $oPlugin */
/** @var \Smarty $smarty */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['adp_cheaper_submit'])) {
    return;
}

$kArtikel = (int)($_POST['adp_artikel_id'] ?? 0);

// PRG: Basis-URL ohne eigene GET-Params bauen
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = preg_replace('/([?&])adp_cheaper=[^&]*/', '', $uri);
$uri = preg_replace('/([?&])adp_(ka|err)=[^&]*/', '', $uri);
$uri = rtrim($uri, '?&');
$sep = str_contains($uri, '?') ? '&' : '?';

$redirectError = function (string $code) use ($uri, $sep, $kArtikel): void {
    header('Location: ' . $uri . $sep . 'adp_cheaper=error&adp_err=' . $code . '&adp_ka=' . $kArtikel, true, 303);
    exit;
};

// CSRF-Prüfung
if (!Form::validateToken()) {
    $redirectError('csrf');
}

// Honeypot: Bots still-fail mit gefälschtem Erfolg
if (Form::honeypotWasFilledOut($_POST)) {
    header('Location: ' . $uri . $sep . 'adp_cheaper=success&adp_ka=' . $kArtikel, true, 303);
    exit;
}

// Felder validieren
$email     = filter_var(trim($_POST['adp_email'] ?? ''), FILTER_VALIDATE_EMAIL);
$url       = filter_var(trim($_POST['adp_url'] ?? ''), FILTER_VALIDATE_URL);
$nachricht = strip_tags(trim($_POST['adp_nachricht'] ?? ''));
$artikelName = strip_tags(trim($_POST['adp_artikel_name'] ?? ''));

if (!$email || !$url) {
    $redirectError('validation');
}

// Shop-Empfänger-E-Mail ermitteln
$config  = Shop::getSettings([\CONF_EMAILS]);
$toEmail = $config['emails']['email_master_absender'] ?? '';

if (empty($toEmail)) {
    $redirectError('config');
}

// E-Mail-Daten aufbauen
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
        'kPlugin_' . $oPlugin->getID() . '_guenstigergesehen',
        $data
    );
    $mailer->send($mailObj);
} catch (\Exception $e) {
    Shop::Container()->getLogService()->error(
        'ArtikelDetailsPlus cheaper form: ' . $e->getMessage()
    );
    $redirectError('mail');
}

header('Location: ' . $uri . $sep . 'adp_cheaper=success&adp_ka=' . $kArtikel, true, 303);
exit;
