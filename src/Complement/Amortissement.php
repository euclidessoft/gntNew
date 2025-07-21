<?php


namespace App\Complement;


use App\Entity\Ecriture;

class Amortissement
{
    public function amortissement($depense){


                    $valeurAquisition = $depense->getMontant(); // en FCFA
                    $duree = $depense->getCategorie()->getAmortissement(); // en années
                    $dateAcquisition = $depense->getDate();
                    $firstyear = new \DateTime($dateAcquisition->format("Y").'-12-31');

                    // Calcul de la dotation annuelle
                    $dotationAnnuelle = $valeurAquisition / $duree;
                    $dotationMensuelle = $valeurAquisition / ($duree * 12);

                    // // Affichage du plan d'amortissement
                    // echo "Plan d’amortissement de l’immobilisation : $description\n";
                    // echo "Valeur d’acquisition : " . number_format($valeurAquisition, 0, ',', ' ') . " FCFA\n";
                    // echo "Durée : $duree ans\n";
                    // echo "Dotation annuelle : " . number_format($dotationAnnuelle, 0, ',', ' ') . " FCFA\n\n";

                    // echo "Année\tDotation\tCumul amortissement\n";
                    $now = new \Datetime();
                    $interval = $now->diff($dateAcquisition);
                    $inter = $firstyear->diff($dateAcquisition);
                    $moisfirstyear = $inter->m;

                    $yearDiff = $interval->y;
                    $monthDiff = $interval->m +1; 
                    $yearDiff >= $duree ? $yearDiff = $duree : null ;
                    $cumul = 0;

                    for ($i = 0; $i <= $yearDiff; $i++) {
                        $currentYear = $dateAcquisition->format("Y") + $i;

                        if ($yearDiff == 0) { // Moins dun an
                            if ($currentYear == (int)date('Y')) { // Même année
                               
                                $cumul += $dotationMensuelle * $monthDiff;
                            
                            } else { // Deux années différentes mais moins dun an
                              
                                $cumul += $dotationMensuelle * (int)date('m');
                            
                            }

                        }elseif ($i == $yearDiff) { // Dernière boucle mais amortissement en cours
                            if ($currentYear == (int)date('Y')) {
                                $nbrMois = (int)date('m');
                                $cumul += $dotationMensuelle * $nbrMois;
                            
                            }

                        }
                    }
        return $cumul;

    }


}