<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Retourne toutes les réservations faites par un utilisateur donné,
     * pour la page "Mes réservations".
     */
    public function findByPassager(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.passager = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une réservation existe entre deux utilisateurs,
     * peu importe lequel est le passager ou le conducteur.
     * Utilisé pour décider si le téléphone peut être affiché sur un profil.
     */
    public function existeReservationEntre(Utilisateur $utilisateur1, Utilisateur $utilisateur2): bool
    {
        $resultat = $this->createQueryBuilder('r')
            ->join('r.trajet', 't')
            ->where('(r.passager = :u1 AND t.conducteur = :u2) OR (r.passager = :u2 AND t.conducteur = :u1)')
            ->setParameter('u1', $utilisateur1)
            ->setParameter('u2', $utilisateur2)
            ->getQuery()
            ->getResult();

        return count($resultat) > 0;
    }
}