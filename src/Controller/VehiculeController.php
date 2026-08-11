<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VehiculeController extends AbstractController
{
    // Formulaire d'ajout de véhicule, réservé aux utilisateurs connectés
    #[Route('/vehicule/nouveau', name: 'app_vehicule')]
    #[IsGranted('ROLE_USER')]
    public function nouveau(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le propriétaire est toujours l'utilisateur connecté,
            // jamais un champ rempli depuis le formulaire
            $vehicule->setProprietaire($this->getUser());

            $entityManager->persist($vehicule);
            $entityManager->flush();

            return $this->redirectToRoute('app_trajet_nouveau');
        }

        return $this->render('vehicule/index.html.twig', [
            'vehiculeForm' => $form,
        ]);
    }
}