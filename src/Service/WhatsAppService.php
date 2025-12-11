<?php
namespace App\Service;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

class WhatsAppService
{
    private Client $client;

    public function __construct(string $sid, string $token, string $from)
    {
         // Création du client CURL Twilio
        $curlClient = new CurlClient([
            CURLOPT_SSL_VERIFYPEER => false,  // désactive vérification SSL
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        // Injecter dans Twilio
        $this->client = new Client($sid, $token, null, null, $curlClient);
        $this->from = $from;
    }

    public function sendMessage(string $to, string $message): void
    {
        $this->client->messages->create(
            // "whatsapp:" . $to,
            $to,
            [
                // "from" => "whatsapp:" . $this->from,
                "from" =>  $this->from,
                "body" => $message,
            ]
        );
    }
}
