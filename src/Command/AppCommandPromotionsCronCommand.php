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
use App\Entity\Promotion;

#[AsCommand(
    name: 'AppCommandPromotionsCron',
    description: 'Commande d activation et de deactivation des promos',
)]
class AppCommandPromotionsCronCommand extends Command
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
        // $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

        // $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
         $datedebut = new \DateTime();
         $datefin = date_sub($datedebut, date_interval_create_from_date_string("1 day"));
        $em = $this->entityManager;
       // $start = $em->getRepository(Promotion::class)->findAll();
        $start = $em->getRepository(Promotion::class)->findBy(['debut' => $datedebut]);
        $end = $em->getRepository(Promotion::class)->findBy(['fin' => $datefin]);


        foreach ($start as $promotion) {
            foreach($promotion->getProduits() as $produit){
                $produit->setPromotion($promotion);
                $em->persist($produit);
            }
           
        }
        
        foreach ($end as $promo) {
            foreach($promo->getProduits() as $produit){
                $produit->setPromotion(null);
            
                $em->persist($produit);
            }
           
        }
         $em->flush();
        $heure = date("d/m/Y H:i:s");
         file_put_contents(__DIR__ . '/webhook.log', $heure."\n", FILE_APPEND);
        return Command::SUCCESS;
    }
}
