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

        $dispatcher->hookInto(\HOOK_ARTIKEL_PAGE, function (array $args): void {
            $this->handleCheaperForm();
        });
    }

    private function handleCheaperForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['adp_cheaper_submit'])) {
            return;
        }

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

        $email       = filter_var(trim($_POST['adp_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $url         = filter_var(trim($_POST['adp_url'] ?? ''), FILTER_VALIDATE_URL);
        $nachricht   = strip_tags(trim($_POST['adp_nachricht'] ?? ''));
        $artikelName = strip_tags(trim($_POST['adp_artikel_name'] ?? ''));

        if (!$email || !$url) {
            $redirectError('validation');
        }

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
            $mailer->send($mailObj);
        } catch (\Exception $e) {
            Shop::Container()->getLogService()->error(
                'ArtikelDetailsPlus cheaper form: ' . $e->getMessage()
            );
            $redirectError('mail');
        }

        header('Location: ' . $uri . $sep . 'adp_cheaper=success&adp_ka=' . $kArtikel, true, 303);
        exit;
    }
}
