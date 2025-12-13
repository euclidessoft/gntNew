<?php
namespace App\Service;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SMSService
{
    private Client $client;
    private string $sid;
    private string $token;
    private  string $from;
    private  string $to;
     public function __construct(ParameterBagInterface $params)
    {
          $this->sid = $params->get('TWILIO_SID');
        $this->token = $params->get('TWILIO_TOKEN');
        
         // Création du client CURL Twilio
        $curlClient = new CurlClient([
            CURLOPT_SSL_VERIFYPEER => false,  // désactive vérification SSL
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        // Injecter dans Twilio
        $this->client = new Client($this->sid, $this->token, null, null, $curlClient);
        $this->from = $params->get('TWILIO_FROM');
        $this->to = $params->get('TWILIO_TO');
    }

    public function sendMessage(string $message): void
    {
        $this->client->messages->create(
            // "whatsapp:" . $to,
            $this->to,
            [
                // "from" => "whatsapp:" . $this->from,
                "from" =>  $this->from,
                "body" => $message,
            ]
        );
    }
}
