<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LamService
{
    private $accountid;

    private $password;
    
    private $urlapi;
    
    private $to;
    
    private $sender;
    
    private $text;
    // private HttpClientInterface $client;
    
    public function __construct($text,private HttpClientInterface $client, ParameterBagInterface $params)
    {
       
        $this->to = $params->get('TWILIO_TO');
        // $this->to = '00221'.preg_replace('/\s/','',$to);
        $this->sender = "JambaarCorp";
        $this->text = urlencode($text);
        $this->accountid ='JAMBAAR_CORPORATION_01';
        $this->password ='kf10yY6Jx7F04fw';
        $this->urlapi ='https://lamsms.lafricamobile.com/api';
        
    }
# (the usage may be limited to allowed ippaddress, you should have given #your own
#server address to be authorized to use this url)
    public function send()
    {
        $full_url_called=$this->urlapi.'?'."accountid=$this->accountid&password=$this->password"."&text=$this->text"."&to=$this->to"."&sender=$this->sender" ;
       // print "$full_url_called\n";
        $result= 0;
      
        // try {
            $response = $this->client->request('GET', $full_url_called);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception('Erreur HTTP : '.$statusCode);
            }
            $result = $response->getContent(false); // exception auto si erreur HTTP
        // }
        // catch(throwable $e){
        //     $result = false;
        // }
        /*print "\nresult=";
        print_r($result);
        print "\n----\n";*/
        return $result;
        
    }
}


?>