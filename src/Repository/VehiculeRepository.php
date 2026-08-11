<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * Retourne une requête (non exécutée) pour récupérer
     * uniquement les véhicules appartenant à un utilisateur donné.
     * Utilisée notamment dans TrajetType pour filtrer le menu déroulant.
     */
    public function findByProprietaire(Utilisateur $utilisateur): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->where('v.proprietaire = :utilisateur')
            ->setParameter('utilisateur', $utilisateur);
    }
}