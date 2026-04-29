<?php


namespace App\Complement;


use App\Entity\Promotion as Promo;
use App\Entity\Produit;
class Promotion
{
   
    public function ug(Produit $produit, int $quantite)
    {
        $suite = 0;
        $ug = 0;
        $promo = $produit->getPromotion();

        if ($quantite / $promo->getCinquieme() >= 1) {

            $unite = floor($quantite / $promo->getCinquieme());
            $ug += $unite * $promo->getUgcinquieme();
            $suite = $quantite - $unite * $promo->getCinquieme();

            if ($suite / $promo->getQuatrieme() >= 1) {

                $unite = floor($suite / $promo->getQuatrieme());
                $ug += $unite * $promo->getUgquatrieme();
                $suite -= $unite * $promo->getQuatrieme();

                if ($suite / $promo->getTroisieme() >= 1) {

                    $unite = floor($suite / $promo->getTroisieme());
                    $ug += $unite * $promo->getUgtroisieme();
                    $suite -= $unite * $promo->getTroisieme();

                    if ($suite / $promo->getDeuxieme() >= 1) {

                        $unite = floor($suite / $promo->getDeuxieme());
                        $ug += $unite * $promo->getUgdeuxieme();
                        $suite -= $unite * $promo->getDeuxieme();

                        if ($suite / $promo->getPremier() >= 1) {
                            $unite = floor($suite / $promo->getPremier());
                            $ug += $unite * $promo->getUgpremier();
                        }

                    } elseif ($suite / $promo->getPremier() >= 1) {

                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getDeuxieme() >= 1) {

                    $unite = floor($suite / $promo->getDeuxieme());
                    $ug += $unite * $promo->getUgdeuxieme();
                    $suite -= $unite * $promo->getDeuxieme();

                    if ($suite / $promo->getPremier() >= 1) {
                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getPremier() >= 1) {

                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getTroisieme() >= 1) {

                $unite = floor($suite / $promo->getTroisieme());
                $ug += $unite * $promo->getUgtroisieme();
                $suite -= $unite * $promo->getTroisieme();

                if ($suite / $promo->getDeuxieme() >= 1) {

                    $unite = floor($suite / $promo->getDeuxieme());
                    $ug += $unite * $promo->getUgdeuxieme();
                    $suite -= $unite * $promo->getDeuxieme();

                    if ($suite / $promo->getPremier() >= 1) {
                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getPremier() >= 1) {

                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getDeuxieme() >= 1) {

                $unite = floor($suite / $promo->getDeuxieme());
                $ug += $unite * $promo->getUgdeuxieme();
                $suite -= $unite * $promo->getDeuxieme();

                if ($suite / $promo->getPremier() >= 1) {
                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getPremier() >= 1) {

                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getQuatrieme() >= 1) {

            $unite = floor($quantite / $promo->getQuatrieme());
            $ug += $unite * $promo->getUgquatrieme();
            $suite = $quantite - $unite * $promo->getQuatrieme();

            if ($suite / $promo->getTroisieme() >= 1) {

                $unite = floor($suite / $promo->getTroisieme());
                $ug += $unite * $promo->getUgtroisieme();
                $suite -= $unite * $promo->getTroisieme();

                if ($suite / $promo->getDeuxieme() >= 1) {

                    $unite = floor($suite / $promo->getDeuxieme());
                    $ug += $unite * $promo->getUgdeuxieme();
                    $suite -= $unite * $promo->getDeuxieme();

                    if ($suite / $promo->getPremier() >= 1) {
                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getPremier() >= 1) {

                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getDeuxieme() >= 1) {

                $unite = floor($suite / $promo->getDeuxieme());
                $ug += $unite * $promo->getUgdeuxieme();
                $suite -= $unite * $promo->getDeuxieme();

                if ($suite / $promo->getPremier() >= 1) {
                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getPremier() >= 1) {

                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getTroisieme() >= 1) {

            $unite = floor($quantite / $promo->getTroisieme());
            $ug += $unite * $promo->getUgtroisieme();
            $suite = $quantite - $unite * $promo->getTroisieme();

            if ($suite / $promo->getDeuxieme() >= 1) {

                $unite = floor($suite / $promo->getDeuxieme());
                $ug += $unite * $promo->getUgdeuxieme();
                $suite -= $unite * $promo->getDeuxieme();

                if ($suite / $promo->getPremier() >= 1) {
                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getPremier() >= 1) {

                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getDeuxieme() >= 1) {

            $unite = floor($quantite / $promo->getDeuxieme());
            $ug += $unite * $promo->getUgdeuxieme();
            $suite = $quantite - $unite * $promo->getDeuxieme();

            if ($suite / $promo->getPremier() >= 1) {
                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getPremier() >= 1) {

            $unite = floor($quantite / $promo->getPremier());
            $ug += $unite * $promo->getUgpremier();
        }
        return $ug;
    }

     public function promo(Promo $promo, int $quantite)
    {
        $suite = 0;
        $ug = 0;

        if ($quantite / $promo->getCinquieme() >= 1) {

            $unite = floor($quantite / $promo->getCinquieme());
            $ug += $unite * $promo->getUgcinquieme();
            $suite = $quantite - $unite * $promo->getCinquieme();

            if ($suite / $promo->getQuatrieme() >= 1) {

                $unite = floor($suite / $promo->getQuatrieme());
                $ug += $unite * $promo->getUgquatrieme();
                $suite -= $unite * $promo->getQuatrieme();

                if ($suite / $promo->getTroisieme() >= 1) {

                    $unite = floor($suite / $promo->getTroisieme());
                    $ug += $unite * $promo->getUgtroisieme();
                    $suite -= $unite * $promo->getTroisieme();

                    if ($suite / $promo->getDeuxieme() >= 1) {

                        $unite = floor($suite / $promo->getDeuxieme());
                        $ug += $unite * $promo->getUgdeuxieme();
                        $suite -= $unite * $promo->getDeuxieme();

                        if ($suite / $promo->getPremier() >= 1) {
                            $unite = floor($suite / $promo->getPremier());
                            $ug += $unite * $promo->getUgpremier();
                        }

                    } elseif ($suite / $promo->getPremier() >= 1) {

                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getDeuxieme() >= 1) {

                    $unite = floor($suite / $promo->getDeuxieme());
                    $ug += $unite * $promo->getUgdeuxieme();
                    $suite -= $unite * $promo->getDeuxieme();

                    if ($suite / $promo->getPremier() >= 1) {
                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getPremier() >= 1) {

                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getTroisieme() >= 1) {

                $unite = floor($suite / $promo->getTroisieme());
                $ug += $unite * $promo->getUgtroisieme();
                $suite -= $unite * $promo->getTroisieme();

                if ($suite / $promo->getDeuxieme() >= 1) {

                    $unite = floor($suite / $promo->getDeuxieme());
                    $ug += $unite * $promo->getUgdeuxieme();
                    $suite -= $unite * $promo->getDeuxieme();

                    if ($suite / $promo->getPremier() >= 1) {
                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getPremier() >= 1) {

                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getDeuxieme() >= 1) {

                $unite = floor($suite / $promo->getDeuxieme());
                $ug += $unite * $promo->getUgdeuxieme();
                $suite -= $unite * $promo->getDeuxieme();

                if ($suite / $promo->getPremier() >= 1) {
                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getPremier() >= 1) {

                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getQuatrieme() >= 1) {

            $unite = floor($quantite / $promo->getQuatrieme());
            $ug += $unite * $promo->getUgquatrieme();
            $suite = $quantite - $unite * $promo->getQuatrieme();

            if ($suite / $promo->getTroisieme() >= 1) {

                $unite = floor($suite / $promo->getTroisieme());
                $ug += $unite * $promo->getUgtroisieme();
                $suite -= $unite * $promo->getTroisieme();

                if ($suite / $promo->getDeuxieme() >= 1) {

                    $unite = floor($suite / $promo->getDeuxieme());
                    $ug += $unite * $promo->getUgdeuxieme();
                    $suite -= $unite * $promo->getDeuxieme();

                    if ($suite / $promo->getPremier() >= 1) {
                        $unite = floor($suite / $promo->getPremier());
                        $ug += $unite * $promo->getUgpremier();
                    }

                } elseif ($suite / $promo->getPremier() >= 1) {

                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getDeuxieme() >= 1) {

                $unite = floor($suite / $promo->getDeuxieme());
                $ug += $unite * $promo->getUgdeuxieme();
                $suite -= $unite * $promo->getDeuxieme();

                if ($suite / $promo->getPremier() >= 1) {
                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getPremier() >= 1) {

                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getTroisieme() >= 1) {

            $unite = floor($quantite / $promo->getTroisieme());
            $ug += $unite * $promo->getUgtroisieme();
            $suite = $quantite - $unite * $promo->getTroisieme();

            if ($suite / $promo->getDeuxieme() >= 1) {

                $unite = floor($suite / $promo->getDeuxieme());
                $ug += $unite * $promo->getUgdeuxieme();
                $suite -= $unite * $promo->getDeuxieme();

                if ($suite / $promo->getPremier() >= 1) {
                    $unite = floor($suite / $promo->getPremier());
                    $ug += $unite * $promo->getUgpremier();
                }

            } elseif ($suite / $promo->getPremier() >= 1) {

                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getDeuxieme() >= 1) {

            $unite = floor($quantite / $promo->getDeuxieme());
            $ug += $unite * $promo->getUgdeuxieme();
            $suite = $quantite - $unite * $promo->getDeuxieme();

            if ($suite / $promo->getPremier() >= 1) {
                $unite = floor($suite / $promo->getPremier());
                $ug += $unite * $promo->getUgpremier();
            }

        } elseif ($quantite / $promo->getPremier() >= 1) {

            $unite = floor($quantite / $promo->getPremier());
            $ug += $unite * $promo->getUgpremier();
        }
        return $ug;
    }
}
