<?php
// src/Controller/NotificationController.php
namespace App\Controller;

// use App\Entity\Notification;
// use App\Repository\NotificationRepository;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

use App\Service\WhatsAppNotifier;
use App\Service\SMSService;
use App\Service\LamService;
use App\Entity\Commande;
use App\Entity\Versement;
use App\Entity\Client;
use App\Entity\RetourProduit;
use App\Entity\Pharmacie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route("/{_locale}/notifications") ]
class NotificationController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/test/whatsapp', name: 'test_whatsapp')]
    public function testWhatsApp(WhatsAppNotifier $wa)
{
    $wa->send('Hello depuis Symfony via WhatsApp !');
    return new Response("Message WhatsApp envoyé !");
}

    #[Route('/test-whatsapp')]
public function test(ParameterBagInterface $params)
{
    // JAMBAAR_CORPORATION_01
    // kf10yY6Jx7F04fw
    // $ws = new SMSService($params);//connect number_format($livrer->getCommande()->getMontant(), 0, ',', ' ')
    // $ws->sendMessage("GNTPharma - sortie de stock\n Commande: 575\n Client: Client\n Montant:".number_format(13500));
    return new Response("GNTPharma - sortie de stock\n Commande: 575\n Client: Client\n Montant:".number_format(13500, 0, ',', ' '));
}

    #[Route('/webhook/whatsapp', name: 'whatsapp_webhook', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $from = $request->request->get('From');
        $body = $request->request->get('Body');

        // Log pour vérifier
        file_put_contents(__DIR__ . '/webhook.log', "FROM: $from | MESSAGE: $body\n", FILE_APPEND);

        // Réponse automatique
        return new Response("<Response><Message>Message reçu: $body</Message></Response>", 200, [
            'Content-Type' => 'text/xml'
        ]);
    }

       #[Route('/test-lam')]
    public function testlam(HttpClientInterface $client,ParameterBagInterface $params)
    {
        // $lam = new lamService("GNTPharma - sortie de stock\n Commande: 575\n Client: Client\n Montant:".number_format(13500, 2, ',', ' '), $client, $params);
        // $response = true;
        // try{
        //     $lam->send();
        // }
        // catch(throwable $e){
        //     $response = false;
        // }
        
        // // JAMBAAR_CORPORATION_01
        // // kf10yY6Jx7F04fw
        // // $ws = new SMSService($params);//connect number_format($livrer->getCommande()->getMontant(), 0, ',', ' ')
        // // $ws->sendMessage("GNTPharma - sortie de stock\n Commande: 575\n Client: Client\n Montant:".number_format(13500));
       // return new Response($response);
       
        // $retours = $this->entityManager->getRepository(RetourProduit::class)->findAll();
        // foreach($retours as $retour){
        //     $retour->setPrix($retour->getProduit()->getPrix());
        //     $retour->getProduit()->getTva() == true ? $retour->setTva($retour->getProduit()->getPrix() * 0.1925) : $retour->setTva(0);
        //     $retour->setPrixpublic($retour->getProduit()->getPrixpublic());
        //     $this->entityManager->persist($retour);
        //     $this->entityManager->flush();
        // }
        // return new Response("okay");
        
        
        // $clients = $this->entityManager->getRepository(Client::class)->findAll();
        // foreach($clients as $client){
        //     $pharmacie = new Pharmacie();
        //     $pharmacie->setNom($client->getNom()." ".$client->getPrenom());
        //     $this->entityManager->persist($pharmacie);
        //     $this->entityManager->flush();
        //     $client->setRoles(["ROLE_CLIENT_ADMIN"]);
        //     $client->setPharmacie($pharmacie);
        //     $this->entityManager->persist($client);
        //     $this->entityManager->flush();

        // }
        // return new Response("okay");

         $commandes =  $this->entityManager->getRepository(Commande::Class)->findBy(['traitement'=> null]);
        foreach($commandes as $commande){
            if($commande->getTraitement() == null){
            if($commande->getPaiement() !== null){
           $commande->setTraitement($commande->getDatelivrer());
            $this->entityManager->persist($commande);
            //$this->entityManager->flush();
            }else if($commande->getversement() != 0){
                $versement =  $this->entityManager->getRepository(Versement::Class)->findOneby(['commande' => $commande],['id'=> 'ASC']);
               $commande->setTraitement($commande->getDatelivrer());
                $this->entityManager->persist($commande);
                //$this->entityManager->flush();
            }else if($commande->getPaiement() === null && $commande->getversement() == 0 && $commande->getLivrer() == true){
                $commande->setTraitement($commande->getDatelivrer());
                $this->entityManager->persist($commande);
                //$this->entityManager->flush();
            }
        }
        }
        $this->entityManager->flush();
        return new Response("okay");
    }


//
//    /**
//     * @Route("/check", name :"notification_check")
//     */
//    public function notification(NotificationRepository $notificationRepository, Security $security): JsonResponse
//    {
//        // Vérifier si un utilisateur est connecté
//        $employe = $security->getUser();
//        if (!$employe) {
//            return $this->json(['error' => 'Utilisateur non connecté'], 401);
//        }
//
//        // Temps max d'attente pour le long polling (en secondes)
//        $timeOut = 30;
//        $startTime = time();
//
//        // Boucle infinie pour long polling
//        while (true) {
//            // Récupérer les notifications non lues pour l'utilisateur
//            $notifications = $notificationRepository->findUnReadByEmploye($employe);
//
//            // Si des notifications ont été trouvées, on les renvoie
//            if (!empty($notifications)) {
//                return $this->json([
//                    'count' => count($notifications),
//                    'notifications' => array_map(fn($n) => [
//                        'id' => $n->getId(),
//                        'message' => $n->getMessage(),
//                        'createdAt' => $n->getCreatedAt()->format('d/m/Y H:i'),
//                        'lien' => $n->getLien(),
//                    ], $notifications)
//                ]);
//            }
//
//            // Si le timeout est dépassé sans notifications, renvoyer une réponse vide
//            if (time() - $startTime > $timeOut) {
//                return $this->json(['count' => 0, 'notifications' => []]);
//            }
//
//            // Attente de 1 seconde avant de refaire une vérification
//            sleep(1);
//        }
//    }
//
//    /**
//     * @Route("/Read/{id}", name :"notification_read", methods : ["POST"})
//     */
//    public function read(Notification $notification): JsonResponse
//    {
//        $entityManager = $this->getDoctrine()->getManager();
//        if ($notification->getIsRead()) {
//            return $this->json(['message' => 'La notification est déjà lue'], 400);
//        }
//
//        $notification->setIsRead(true);
//        // Sauvegarder la notification mise à jour
//        $entityManager->persist($notification);
//        $entityManager->flush();
//        return $this->json(['message' => 'Notification marquée comme lue']);
//    }
}


?>