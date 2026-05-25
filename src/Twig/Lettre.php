<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Lettre extends AbstractExtension
{
     public function getFunctions(): array
    {
        return [
            new TwigFunction('lettre', [$this, 'Montant']),
        ];
    }

    public function Montant($total){
        $nombre = (float) $total;

        $totalEntier = floor($nombre);

        $decimal = round(($nombre - $totalEntier) * 100);

        $totalEnLettres = $this->numberToLetter($totalEntier);

        $virgule = $this->numberToLetter($decimal);
        return $totalEnLettres." virgule ".$virgule;
    }

    public function unite($nombre)
    {
        switch ($nombre) {

            case 0:
                return 'zéro';

            case 1:
                return 'un';

            case 2:
                return 'deux';

            case 3:
                return 'trois';

            case 4:
                return 'quatre';

            case 5:
                return 'cinq';

            case 6:
                return 'six';

            case 7:
                return 'sept';

            case 8:
                return 'huit';

            case 9:
                return 'neuf';
        }

        return '';
    }

    public function dizaine($nombre)
    {
        switch ($nombre) {

            case 10:
                return 'dix';

            case 11:
                return 'onze';

            case 12:
                return 'douze';

            case 13:
                return 'treize';

            case 14:
                return 'quatorze';

            case 15:
                return 'quinze';

            case 16:
                return 'seize';

            case 17:
                return 'dix-sept';

            case 18:
                return 'dix-huit';

            case 19:
                return 'dix-neuf';

            case 20:
                return 'vingt';

            case 30:
                return 'trente';

            case 40:
                return 'quarante';

            case 50:
                return 'cinquante';

            case 60:
                return 'soixante';

            case 70:
                return 'soixante-dix';

            case 71:
                return 'soixante-onze';

            case 72:
                return 'soixante-douze';

            case 73:
                return 'soixante-treize';

            case 74:
                return 'soixante-quatorze';

            case 75:
                return 'soixante-quinze';

            case 76:
                return 'soixante-seize';

            case 77:
                return 'soixante-dix-sept';

            case 78:
                return 'soixante-dix-huit';

            case 79:
                return 'soixante-dix-neuf';

            case 80:
                return 'quatre-vingt';

            case 90:
                return 'quatre-vingt-dix';

            case 91:
                return 'quatre-vingt-onze';

            case 92:
                return 'quatre-vingt-douze';

            case 93:
                return 'quatre-vingt-treize';

            case 94:
                return 'quatre-vingt-quatorze';

            case 95:
                return 'quatre-vingt-quinze';

            case 96:
                return 'quatre-vingt-seize';

            case 97:
                return 'quatre-vingt-dix-sept';

            case 98:
                return 'quatre-vingt-dix-huit';

            case 99:
                return 'quatre-vingt-dix-neuf';
        }

        return '';
    }
    public function numberToLetter($nombre)
    {
        if (strlen(str_replace(' ', '', $nombre)) > 15) {
            return 'dépassement de capacité';
        }

        if (!is_numeric(str_replace(' ', '', $nombre))) {
            return 'Nombre non valide';
        }

        $nb = (int) str_replace(' ', '', $nombre);

        if (ceil($nb) != $nb) {
            return 'Nombre avec virgule non géré.';
        }

        $n = strlen((string) $nb);
        $numberToLetter = '';

        switch ($n) {

            case 1:
                $numberToLetter = $this->unite($nb);
                break;

            case 2:

                if ($nb > 19) {

                    $quotient = floor($nb / 10);
                    $reste = $nb % 10;

                    if ($nb < 71 || ($nb > 79 && $nb < 91)) {

                        if ($reste == 0)
                            $numberToLetter = $this->dizaine($quotient * 10);

                        if ($reste == 1)
                            $numberToLetter = $this->dizaine($quotient * 10) . '-et-' . $this->unite($reste);

                        if ($reste > 1)
                            $numberToLetter = $this->dizaine($quotient * 10) . '-' . $this->unite($reste);

                    } else {

                        $numberToLetter = $this->dizaine($nb);
                    }

                } else {

                    $numberToLetter = $this->dizaine($nb);
                }

                break;

            case 3:

                $quotient = floor($nb / 100);
                $reste = $nb % 100;

                if ($quotient == 1 && $reste == 0)
                    $numberToLetter = 'cent';

                if ($quotient == 1 && $reste != 0)
                    $numberToLetter = 'cent ' . $this->numberToLetter($reste);

                if ($quotient > 1 && $reste == 0)
                    $numberToLetter = $this->unite($quotient) . ' cents';

                if ($quotient > 1 && $reste != 0)
                    $numberToLetter = $this->unite($quotient) . ' cent ' . $this->numberToLetter($reste);

                break;

            case 4:
            case 5:
            case 6:

                $quotient = floor($nb / 1000);
                $reste = $nb % 1000;

                if ($quotient == 1 && $reste == 0)
                    $numberToLetter = 'mille';

                if ($quotient == 1 && $reste != 0)
                    $numberToLetter = 'mille ' . $this->numberToLetter($reste);

                if ($quotient > 1 && $reste == 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' mille';

                if ($quotient > 1 && $reste != 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' mille ' . $this->numberToLetter($reste);

                break;

            case 7:
            case 8:
            case 9:

                $quotient = floor($nb / 1000000);
                $reste = $nb % 1000000;

                if ($quotient == 1 && $reste == 0)
                    $numberToLetter = 'un million';

                if ($quotient == 1 && $reste != 0)
                    $numberToLetter = 'un million ' . $this->numberToLetter($reste);

                if ($quotient > 1 && $reste == 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' millions';

                if ($quotient > 1 && $reste != 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' millions ' . $this->numberToLetter($reste);

                break;

            case 10:
            case 11:
            case 12:

                $quotient = floor($nb / 1000000000);
                $reste = $nb % 1000000000;

                if ($quotient == 1 && $reste == 0)
                    $numberToLetter = 'un milliard';

                if ($quotient == 1 && $reste != 0)
                    $numberToLetter = 'un milliard ' . $this->numberToLetter($reste);

                if ($quotient > 1 && $reste == 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' milliards';

                if ($quotient > 1 && $reste != 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' milliards ' . $this->numberToLetter($reste);

                break;

            case 13:
            case 14:
            case 15:

                $quotient = floor($nb / 1000000000000);
                $reste = $nb % 1000000000000;

                if ($quotient == 1 && $reste == 0)
                    $numberToLetter = 'un billion';

                if ($quotient == 1 && $reste != 0)
                    $numberToLetter = 'un billion ' . $this->numberToLetter($reste);

                if ($quotient > 1 && $reste == 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' billions';

                if ($quotient > 1 && $reste != 0)
                    $numberToLetter = $this->numberToLetter($quotient) . ' billions ' . $this->numberToLetter($reste);

                break;
        }

        if (substr($numberToLetter, -strlen('quatre-vingt')) == 'quatre-vingt') {
            $numberToLetter .= 's';
        }

        return $numberToLetter;
    }
}