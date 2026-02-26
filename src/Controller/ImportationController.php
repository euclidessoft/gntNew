<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


#[Route("{_locale}/Importation") ]
class ImportationController extends AbstractController
{
    #[Route("/", name :"importation", methods : ["POST"]) ]
    public function importation(Request $request, SessionInterface $session)
    {
        $file = $request->files->get('import');

        if (!$file) {
            return new Response('Aucun fichier envoyé');
            //dd($request);
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            $session->set('sheet', $sheet);

            // $rows = [];

            // foreach ($sheet->getRowIterator() as $row) {

            //     $cellIterator = $row->getCellIterator();
            //     $cellIterator->setIterateOnlyExistingCells(false);

            //     $line = [];

            //     foreach ($cellIterator as $cell) {
            //         $line[] = $cell->getValue();
            //     }

            //     $rows[] = $line;
            // }

           // return $this->json($rows);
            $response = $this->redirectToRoute("commande_panier_panier");
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;

        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage());
        }
    }
}