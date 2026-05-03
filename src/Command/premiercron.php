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
use App\Entity\Avoir;
use App\Entity\Commande;
use App\Entity\Releve;

#[AsCommand(
    name: 'premierCron',
    description: 'releve premier quinzaine du mois',
)]
class premiercron extends Command
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
        // $date = new \DateTime();

        // $dernierJour = (clone $date)
        //     ->modify('last day of previous month');

        $mois = date('Y-m');
        
         $clientcoms = $this->entityManager->getRepository(Commande::Class)->commandepremiertranche($mois);
        foreach($clientcoms as $clientcom){
            $client = $clientcom->getUser();
        
            $montant = 0;
            $avance = 0;
            $total = 0;
            $tva = 0;
            $prelevement = 0;
            $commandes = $this->entityManager->getRepository(Commande::Class)->premiertranche($client->getId(), $mois);
            $com = [];
            foreach($commandes as $commande){
                $com[] = [
                    'date' => $commande->getDate()->format('d/m/Y'),
                    'datedue' => "26/".date("m/Y"),
                    'traitement' => $commande->getTraitement()->format('d/m/Y'),
                    'numerofacture' => $commande->getId()."-".$commande->getNumerofacture(),
                    'montant' => $commande->getMontant() - $commande->getTva() - $commande->getAcompte(),
                ];
                $montant += ($commande->getMontant() - $commande->getTva() - $commande->getAcompte());
                $avance += $commande->getVersement();
                $total += $commande->getMontant();
                $tva += $commande->getTva();
                $prelevement += $commande->getAcompte();

            }
           
            $avoirs = $this->entityManager->getRepository(Avoir::Class)->premiertranche($client->getId(), $mois);
            $av =[];
            foreach($avoirs as $avoir){
                $av[] = [
                    'date' => $avoir->getDate()->format('d/m/Y'),
                    'montant' => $avoir->getMontant(),
                ];
                $avance += $avoir->getMontant();
            }
            if(count($commandes) > 0 || count($avoirs) > 0){
             $releve = new Releve();
             $releve->setCommandes(json_encode($com));
             $releve->setQuinzaine(1);
             $releve->setClient($client);
             $releve->setAvoir(json_encode($av));
             $releve->setPeriode($mois);
             $releve->setAvantage(0);
             $releve->setAvance($avance);
             $releve->setPrelevement($prelevement);
             $releve->setTva($tva);
             $releve->setTotal($total);
             $releve->setReste($total - $avance);
             $releve->setHt($montant);
             $this->entityManager->persist($releve);
            }
        }

            $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
