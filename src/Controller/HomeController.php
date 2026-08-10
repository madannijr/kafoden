<?php

namespace App\Controller;

use App\Repository\TrajetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TrajetRepository $trajetRepository): Response
    {
        // Symfony injecte automatiquement le repository de Trajet (autowiring)
        // findAll() récupère tous les trajets enregistrés en base
        return $this->render('home/index.html.twig', [
            'trajets' => $trajetRepository->findAll(),
        ]);
    }
}
