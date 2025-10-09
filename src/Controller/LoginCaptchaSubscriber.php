<?php
namespace App\Controller;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpClient\HttpClient;

class LoginCaptchaSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urls) {}

    public static function getSubscribedEvents(): array
    {
        
        return ['kernel.request' => ['onKernelRequest', 8]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $req = $event->getRequest();

        if ($req->getPathInfo() !== '/login' || $req->getMethod() !== 'POST') {
            return;
        }

        $captcha = (string) $req->request->get('g-recaptcha-response');
        if (!$captcha) {
            $req->getSession()?->getFlashBag()->add('error', 'Captcha requerido.');
            $event->setResponse(new RedirectResponse($this->urls->generate('app_login')));
            return;
        }

        $client = HttpClient::create();
        $verify = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret'   => $_ENV['RECAPTCHA_SECRET'] ?? '',
                'response' => $captcha,
                'remoteip' => $req->getClientIp(),
            ],
        ]);
        $data = $verify->toArray(false);

        if (empty($data['success'])) {
            $req->getSession()?->getFlashBag()->add('error', 'Captcha inválido.');
            $event->setResponse(new RedirectResponse($this->urls->generate('app_login')));
        }
    }
}
