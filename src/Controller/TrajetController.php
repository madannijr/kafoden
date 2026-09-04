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

            // Le statut dépend de si l'identité du conducteur est déjà vérifiée
            if ($utilisateur->isEstVerifie()) {
                $trajet->setStatut('active');
                $this->addFlash('success', 'Votre trajet a été publié avec succès.');
            } else {
                $trajet->setStatut('en_attente_verification');
                $this->addFlash('warning', 'Votre trajet a été enregistré. Il sera visible publiquement une fois votre identité vérifiée.');
            }

            $entityManager->persist($trajet);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('trajet/index.html.twig', [
            'trajetForm' => $form,
        ]);
    }

    // Liste des trajets publiés par l'utilisateur connecté, avec leurs réservations
    #[Route('/mes-trajets', name: 'app_mes_trajets')]
    #[IsGranted('ROLE_USER')]
    public function mesTrajets(): Response
    {
        return $this->render('trajet/mes_trajets.html.twig', [
            'trajets' => $this->getUser()->getTrajetsPublies(),
        ]);
    }

    // Modification d'un trajet
    #[Route('/trajet/{id}/modifier', name: 'app_trajet_modifier')]
    #[IsGranted('ROLE_USER')]
public function modifierTrajet(Trajet $trajet, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : seul le conducteur propriétaire peut modifier
        if($trajet->getConducteur()->getId() !== $this->getUser()->getId()){
            $this->addFlash('danger', 'Ce trajet n\'est pas le vôtre.');
            return $this->redirectToRoute('app_mes_trajets');
        }
        $form = $this->createForm(TrajetType::class, $trajet, [
            'utilisateur' => $this->getUser(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le trajet a été modifié avec succès.');
            return $this->redirectToRoute('app_mes_trajets');

        }
        return $this->render('trajet/index.html.twig', [
            'trajetForm' => $form,
        ]);
    }

    // Annulation d'un trajet par son conducteur
    #[Route('/trajet/{id}/annuler', name: 'app_trajet_annuler')]
    #[IsGranted('ROLE_USER')]
    public function annulerTrajet(Trajet $trajet, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : seul le conducteur propriétaire peut annuler
        if ($trajet->getConducteur()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('danger', 'Ce trajet n\'est pas le vôtre.');
            return $this->redirectToRoute('app_mes_trajets');
        }

        // On annule le trajet lui-même
        $trajet->setStatut('annule');

        // On annule en cascade toutes les réservations liées à ce trajet
        foreach ($trajet->getReservations() as $reservation) {
            $reservation->setStatut('annulee');
        }

        // Un seul flush() pour écrire tous les changements en une fois
        $entityManager->flush();

        $this->addFlash('success', 'Le trajet a été annulé. Les passagers concernés verront leur réservation annulée.');

        return $this->redirectToRoute('app_mes_trajets');
    }
}