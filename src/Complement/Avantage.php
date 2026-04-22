<?php


namespace App\Complement;


class Avantage
{
    public function commission($ca, $t1, $t2, $t3, $t4)
    {
        $t1= $t1/100;
        $t2= $t2/100;
        $t3= $t3/100;
        $t4= $t4/100;
        if ($ca < 25000000) {
            return $ca * $t1;
        } elseif ($ca < 50000000) {
            return 25000000 * $t1
                + ($ca - 25000000) * $t2;
        } elseif ($ca < 75000000) {
            return 25000000 * $t1
                + (50000000 - 25000000) * $t2
                + ($ca - 50000000) * $t3;
        } else {
            return 25000000 * $t1
                + (50000000 - 25000000) * $t2
                + (75000000 - 50000000) * $t3
                + ($ca - 75000000) * $t4;
        }
    }
    public function ristourne($ca, $t1, $t2, $t3)
    {
        $t1= $t1/100;
        $t2= $t2/100;
        $t3= $t3/100;
        if ($ca < 10000000) {
            return $ca * $t1;
        } elseif ($ca < 50000000) {
            return 10000000 * $t1
                + ($ca - 10000000) * $t2;
        } else {
            return 10000000 * $t1
                + (50000000 - 10000000) * $t2
                + ($ca - 50000000) * $t3;
        }
    }

    public function escompte($ca, $t1, $t2, $t3, $t4)
    {
        $t1= $t1/100;
        $t2= $t2/100;
        $t3= $t3/100;
        $t4= $t4/100;
        if ($ca < 10000000) {
            return $ca * $t1;
        } elseif ($ca < 30000000) {
            return 10000000 * $t1
                + ($ca - 10000000) * $t2;
        } elseif ($ca < 70000000) {
            return 10000000 * $t1
                + (30000000 - 10000000) * $t2
                + ($ca - 70000000) * $t3;
        } else {
            return 10000000 * $t1
                + (30000000 - 10000000) * $t2
                + (70000000 - 30000000) * $t3
                + ($ca - 70000000) * $t4;
        }
    }
}