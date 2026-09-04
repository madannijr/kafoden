<?php

namespace App\Controller;

use App\Repository\DocumentIdentiteRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DocumentIdentite;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TrajetRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Trajet;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(DocumentIdentiteRepository $documentIdentiteRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'documents' => $documentIdentiteRepository->findBy(['statut'=> 'en_attente'])
        ]);
    }

    #[Route('/admin/document/{id}/valider', name: 'app_admin_document_valider')]
    #[IsGranted('ROLE_ADMIN')]
    public function validerDocument(DocumentIdentite $document, EntityManagerInterface $entityManager): Response
    {
        $document->setStatut('valide');

        // Si le document est validé, on marque aussi l'utilisateur comme vérifié
        $utilisateur = $document->getUtilisateur();
        $utilisateur->setEstVerifie(true);

        // On débloque automatiquement tous les trajets de ce conducteur
        // qui attendaient sa vérification d'identité
        foreach ($utilisateur->getTrajetsPublies() as $trajet) {
            if ($trajet->getStatut() === 'en_attente_verification') {
                $trajet->setStatut('active');
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Le document a été validé.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/document/{id}/refuser', name: 'app_admin_document_refuser')]
    #[IsGranted('ROLE_ADMIN')]
    public function refuserDocument(DocumentIdentite $document, EntityManagerInterface $entityManager): Response
    {
        $document->setStatut('refuse');

        $entityManager->flush();

        $this->addFlash('warning', 'Le document a été refusé.');

        return $this->redirectToRoute('app_admin');
    }

    // Liste de tous les trajets de la plateforme
    #[Route('/admin/trajets', name: 'app_admin_trajets')]
    #[IsGranted('ROLE_ADMIN')]
    public function trajets(TrajetRepository $trajetRepository): Response
    {
        return $this->render('admin/trajets.html.twig', [
            'trajets' => $trajetRepository->findAll(),
        ]);
    }

    // Liste de toutes les réservations de la plateforme
    #[Route('/admin/reservations', name: 'app_admin_reservations')]
    #[IsGranted('ROLE_ADMIN')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        return $this->render('admin/reservations.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }


    // suppression des trajets
    #[Route('/admin/trajet/{id}/supprimer', name: 'app_admin_trajet_supprimer')]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimerTrajet(Trajet $trajet, EntityManagerInterface $entityManager): Response
    {

        // On empêche la suppression si des réservations existent déjà sur ce trajet
        if(count($trajet->getReservations()) > 0) {
            $this->addFlash('danger', 'Impossible de supprimer ce trajet : des réservations existent déjà. ');
            return $this->redirectToRoute('app_admin_trajets');
        }

        $entityManager->remove($trajet);
        $entityManager->flush();

        $this->addFlash('success', 'Le trajet a été supprimé.');

        return $this->redirectToRoute('app_admin_trajets');
    }

    // Listes de tous les utilisateurs
    #[Route('/admin/utilisateurs', name: 'app_admin_utilisateurs')]
    #[IsGranted('ROLE_ADMIN')]
     public function utilisateurs(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('admin/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll()
        ]);
    }


    // Sert un document d'identité de façon sécurisée (admin uniquement)
    #[Route('/admin/document/{id}/voir', name: 'app_admin_document_voir')]
    #[IsGranted('ROLE_ADMIN')]
    public function voirDocument(DocumentIdentite $document, #[Autowire('%documents_directory%')]
        string $documentsDirectory,
    ): BinaryFileResponse {
        $response = new BinaryFileResponse($documentsDirectory . '/' . $document->getCheminFichier());

        // "inline" = ouvre dans le navigateur, plutôt que de forcer un téléchargement
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }

    // Permet à un utilisateur de consulter SON PROPRE document
    #[Route('/document/{id}/voir', name: 'app_document_voir')]
    #[IsGranted('ROLE_USER')]
    public function voir(DocumentIdentite $document,
        #[Autowire('%documents_directory%')]
        string $documentsDirectory,
    ): BinaryFileResponse {
        // Sécurité : seul le propriétaire du document peut le consulter
        if ($document->getUtilisateur()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Ce document ne vous appartient pas.');
        }

        $response = new BinaryFileResponse($documentsDirectory . '/' . $document->getCheminFichier());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }

}