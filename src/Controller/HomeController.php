<?php

namespace App\Controller;

use App\Repository\TrajetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, TrajetRepository $trajetRepository): Response
    {
        // On récupère les valeurs saisies dans le formulaire de recherche
        $depart = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $date = $request->query->get('date');
        // On utilise la méthode de recherche filtrée plutôt que findAll()
        $trajets = $trajetRepository->rechercher($depart, $arrivee, $date);


        return $this->render('home/index.html.twig', [
            'trajets' => $trajets,
        ]);
    }
}
