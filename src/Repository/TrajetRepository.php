<?php

namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trajet>
 */
class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    //    /**
    //     * @return Trajet[] Returns an array of Trajet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Trajet
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function rechercher(?string $depart, ?string $arrivee, ?string $date): array
    {
        // On commence une requête de base sur la table Trajet (alias "t")
        $queryBuilder = $this->createQueryBuilder('t');

        // Condition TOUJOURS appliquée : on ne montre jamais un trajet déjà passé
        $queryBuilder
            ->andWhere('t.dateDepart >= :aujourdhui')
            ->setParameter('aujourdhui', new \DateTime('today'));
        // les trajets annulés
        $queryBuilder
            ->andWhere('t.statut = :statut')
            ->setParameter('statut', 'active');

        // On ajoute la condition "départ" seulement si elle a été remplie
        if($depart){
            $queryBuilder
                ->andWhere('t.depart LIKE :depart')
                ->setParameter('depart', '%' . $depart . '%');
        }
        if($arrivee){
            $queryBuilder
                ->andWhere('t.arrivee LIKE :arrivee')
                ->setParameter('arrivee', '%' . $arrivee . '%');
        }
        if ($date) {
            $queryBuilder
                ->andWhere('t.dateDepart = :date')
                ->setParameter('date', $date);
        }
        // on execute finalement la requete construite
        return $queryBuilder->getQuery()->getResult();
    }
}
