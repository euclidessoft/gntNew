<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Avantage;
use App\Entity\Avoir;
use App\Entity\Commande;
use App\Entity\Releve;

#[AsCommand(
    name: 'deuxiemeCron',
    description: 'releve premier quinzaine du mois',
)]
class deuxiemecron extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    // protected function configure(): void
    // {
    //     $this
    //         ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
    //         ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
    //     ;
    // }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //  $mois =date("Y-m");
        $date = new \DateTime();

        $dernierJour = (clone $date)
            ->modify('last day of previous month');

        $mois = $dernierJour->format('Y-m');
        
         $clientcoms = $this->entityManager->getRepository(Commande::Class)->commandedeuxiemetranche($mois);
        foreach($clientcoms as $clientcom){
            $client = $clientcom->getUser();
        
            $montant = 0;
            $montantavoir = 0;
            $avance = 0;
            $total = 0;
            $tva = 0;
            $tvaavoir = 0;
            $avoir = 0;
            $prelevement = 0;
            $prelevementavoir = 0;
            $avant = [];
            $commandes = $this->entityManager->getRepository(Commande::Class)->deuxiemetranche($client->getId(), $mois);
            $avoirs = $this->entityManager->getRepository(Avoir::Class)->deuxiemetranche($client->getId(), $mois);
            $avantage = $this->entityManager->getRepository(Avantage::Class)->findOneby(["payer" => false, 'client' => $client->getId()]);
            if($avantage !== null){
                $avantage->setPayer(true);
                $this->entityManager->persist($avantage);
                $avant[]= [
                    'commission' => $avantage->getCommission(),
                    'ristourne' => $avantage->getRistourne(),
                    'escompte' => $avantage->getEscompte(),
                    'tva' => $avantage->getTva(),
                ];

            }
            $result = [];
        
            foreach ([$commandes,$avoirs] as $tableau) {
            foreach ($tableau as $row) {
                if($row instanceof Commande)
                 $date = $row->getDate()->format('Y-m-d');
                else $date = $row->getDate()->format('Y-m-d');
                // dd($date);
                // On regroupe les lignes par date
                $result[$date][] = $row;
                }
            }
            ksort($result);
            // dd($result);
            $flat = [];

            foreach ($result as $date => $rows) {
                foreach ($rows as $row) {
                    $flat[] = $row;
                }
            } 

         
            $com = [];
            foreach($flat as $commande){
                 if($commande instanceof Commande){
                 $com[] = [
                    'date' => $commande->getDate()->format('d/m/Y'),
                    'datedue' => "11/".date("m/Y"),
                    'traitement' => $commande->getTraitement()->format('d/m/Y'),
                    'numerofacture' => $commande->getId()."-".$commande->getNumerofacture(),
                    // 'montant' => $commande->getMontant(),
                    'montant' => $commande->getMontant() - $commande->getTva() - $commande->getAcompte(),
                ];
                
                // $montant += $comme->getMontant();
                $montant += ($commande->getMontant() - $commande->getTva() - $commande->getAcompte());
                $avance += $commande->getVersement();
                $total += $commande->getMontant();
                $tva += $commande->getTva();
                $prelevement += $commande->getAcompte();
                }else{ 
                    $com[] = [
                    'date' => $commande->getDate()->format('d/m/Y'),
                    'datedue' => '',
                    'traitement' => $commande->getDate()->format('d/m/Y'),
                    'numerofacture' => $commande->getCommande()->getId()."-".$commande->getCommande()->getNumerofacture()."-".$commande->getId(),
                    // 'montant' => $commande->getMontant() - $commande->getPrelevement(),
                    'prelement' => $commande->getPrelevement(),
                    'montant' => $commande->getMontant() - $commande->getTva() - $commande->getPrelevement(),
                ];
                
                // $montant -= $commande->getMontant();
                $montantavoir += ($commande->getMontant() - $commande->getTva() - $commande->getPrelevement());
                // $avance += $commande->getVersement();
                $avoir += $commande->getMontant();
                $tvaavoir += $commande->getTva();
                $prelevementavoir += $commande->getPrelevement();
                }

            }
            if(count($commandes) > 0){
                $numero =  $this->entityManager->getRepository(Releve::Class)->findBy([ 'client' => $client->getId()]);
             $releve = new Releve();
             $releve->setCommandes(json_encode($com));
             $releve->setNumero(count($numero)+1);
             $releve->setQuinzaine(2);
             $releve->setClient($client);
             $releve->setAvoir($avoir);
             $releve->setPeriode($mois);
             $releve->setAvantage(json_encode($avant));
             $releve->setAvance($avance);
             $releve->setPrelevement($prelevement);
             $releve->setPrelevementavoir($prelevementavoir);
             $releve->setTva($tva);
             $releve->setTvaavoir($tva);
             $releve->setTotal($total);
             $releve->setReste($total - $avance - $avoir);
             $releve->setHt($montant);
             $releve->setHtavoir($montantavoir);
             $this->entityManager->persist($releve);
            }
        }

            $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
