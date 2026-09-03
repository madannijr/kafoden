<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReservationController extends AbstractController
{
    #[Route('/trajet/{id}/reserver', name: 'app_reservation_nouvelle')]
    #[IsGranted('ROLE_USER')]
    public function nouvelle(Trajet $trajet, EntityManagerInterface $entityManager): Response
    {

        // Un conducteur ne peut pas réserver son propre trajet
        if ($trajet->getConducteur()->getId() === $this->getUser()->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas réserver votre propre trajet.');
            return $this->redirectToRoute('app_home');
        }

        // On vérifie si l'utilisateur a déjà réservé ce trajet
        $reservationExistante = $entityManager->getRepository(Reservation::class)->findOneBy([
            'trajet' => $trajet,
            'passager' => $this->getUser(),
            'statut' => 'confirmee'
        ]);

        if ($reservationExistante) {
            $this->addFlash('danger', 'Vous avez déjà réservé ce trajet.');
            return $this->redirectToRoute('app_home');
        }

        // Il doit rester au moins une place disponible
        if ($trajet->getPlacesDisponibles() === 0) {
            $this->addFlash('danger', 'Il n\'y a plus de places disponibles sur ce trajet.');
            return $this->redirectToRoute('app_home');
        }

        // Création de la réservation
        $reservation = new Reservation();
        $reservation->setPlacesReservees(1);
        $reservation->setStatut('confirmee');
        $reservation->setTrajet($trajet);
        $reservation->setPassager($this->getUser());

        // On décrémente le nombre de places disponibles sur le trajet
        $trajet->setPlacesDisponibles($trajet->getPlacesDisponibles() - 1);

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été confirmée !');

        return $this->redirectToRoute('app_home');
    }

    // Liste des réservations de l'utilisateur connecté
    #[Route('/mes-reservations', name: 'app_mes_reservations')]
    #[IsGranted('ROLE_USER')]
    public function mesReservations(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/mes_reservations.html.twig', [
            'reservations' => $reservationRepository->findByPassager($this->getUser()),
        ]);
    }


    // Annulation d'une réservation par le passager
    #[Route('/reservation/{id}/annuler', name: 'app_reservation_annuler')]
    #[IsGranted('ROLE_USER')]
    public function annuler(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : seul le passager propriétaire peut annuler
        if ($reservation->getPassager()->getId() !== $this->getUser()->getId()) {
            $this->addFlash('danger', 'Cette réservation n\'est pas la vôtre.');
            return $this->redirectToRoute('app_mes_reservations');
        }

        $reservation->setStatut('annulee');

        // On redonne la ou les places au trajet
        $trajet = $reservation->getTrajet();
        $trajet->setPlacesDisponibles($trajet->getPlacesDisponibles() + $reservation->getPlacesReservees());

        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été annulée.');

        return $this->redirectToRoute('app_mes_reservations');
    }

}