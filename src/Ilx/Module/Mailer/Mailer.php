<?php

namespace Ilx\Module\Mailer;


use InvalidArgumentException;
use Kodiak\Application;
use Kodiak\Exception\Http\HttpInternalServerErrorException;
use Kodiak\ServiceProvider\TwigProvider\Twig;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{
    const SUBJECT = "subject";
    const TEMPLATE_PATH = "path";


    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $sourceAddress;

    /**
     * @var string
     */
    private $sourceName;

    /**
     * @var array
     */
    private $templates;

    /**
     * Mailer constructor.
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $transport = new Swift_SmtpTransport($configuration["host"], $configuration["port"]);

        $transport->setUsername($configuration["username"]);
        $transport->setPassword($configuration["password"]);
        $transport->setAuthMode($configuration["auth_mode"]);
        $transport->setEncryption($configuration["encryption"]);

        $this->mailer = new Swift_Mailer($transport);

        $this->sourceAddress = $configuration["source"]["address"];
        $this->sourceName = $configuration["source"]["name"];

        $this->templates = $configuration["templates"];
    }


    /**
     * Levelet küld a paraméterben küldött címzettnek.
     *
     * @param string $template_name Template azonosító, pl.: Mailer::WELCOME
     * @param array $recipient Címzett adatainak tömbje (vagy objektum ami megvalósítja az ArrayAccess)
     * @param array $data
     * @return int
     * @throws HttpInternalServerErrorException
     */
    public function send($template_name, $recipient, $data) {
        $template = $this->dispatchTemplate($template_name);

        /** @var Twig $twig */
        $twig = Application::get("twig");

        $message = new Swift_Message($template[Mailer::SUBJECT]);
        $message
            ->setFrom([
                $this->sourceAddress => $this->sourceName
            ])->setTo([
                $recipient["email"] => $recipient["lastname"] . " " .$recipient["firstname"]
            ])->setBody($twig->render($template[Mailer::TEMPLATE_PATH], $data, true), 'text/html');
        // Send the message
        return $this->mailer->send($message);
    }


    private function dispatchTemplate($template_name) {
        if(!array_key_exists($template_name, $this->templates)) {
            throw new InvalidArgumentException("Unknown template name: $template_name");
        }
        return $this->templates[$template_name];
    }
}