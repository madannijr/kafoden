<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Form\TrajetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TrajetController extends AbstractController
{
    // Formulaire de publication de trajet, réservé aux utilisateurs connectés
    #[Route('/trajet/nouveau', name: 'app_trajet_nouveau')]
    #[IsGranted('ROLE_USER')]
    public function nouveau(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();

        // On vérifie que l'utilisateur a au moins un véhicule
        // avant de le laisser publier un trajet
        if (count($utilisateur->getVehicules()) === 0) {
            $this->addFlash('warning', 'Vous devez d\'abord ajouter un véhicule avant de publier un trajet.');

            return $this->redirectToRoute('app_vehicule');
        }

        $trajet = new Trajet();

        // On passe l'utilisateur connecté au formulaire, pour qu'il puisse
        // filtrer la liste des véhicules proposés (voir TrajetType)
        $form = $this->createForm(TrajetType::class, $trajet, [
            'utilisateur' => $utilisateur,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le conducteur est toujours l'utilisateur connecté,
            // jamais un champ rempli depuis le formulaire
            $trajet->setConducteur($utilisateur);

            $entityManager->persist($trajet);
            $entityManager->flush();

            $this->addFlash('success', 'Votre trajet a été publié avec succès.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('trajet/index.html.twig', [
            'trajetForm' => $form,
        ]);
    }
}