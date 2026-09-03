<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Paiement;
use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Entity\Vehicule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // Symfony injecte automatiquement le service de hashage de mot de passe
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Un conducteur
        $conducteur1 = new Utilisateur();
        $conducteur1->setEmail('abassdiallo@gmail.com');
        $conducteur1->setNom('Diallo');
        $conducteur1->setPrenom('Abass');
        $conducteur1->setTelephone('6200000000');
        $conducteur1->setEstVerifie(true);
        $conducteur1->setAdresse('Minière, Carrefour Chinois C/Dixinn');
        $conducteur1->setRoles(['ROLE_ADMIN']);
        $motDePasse = $this->passwordHasher->hashPassword($conducteur1, 'abassdiallo');
        $conducteur1->setPassword($motDePasse);
        $manager->persist($conducteur1);

        // Un passager
        $passager1 = new Utilisateur();
        $passager1->setEmail('alimou.conde@gmail.com');
        $passager1->setNom('Conde');
        $passager1->setPrenom('Alimou');
        $passager1->setTelephone('6255555555');
        $passager1->setAdresse('Carrefour Chinois');
        $passager1->setEstVerifie(true);
        $passager1->setRoles(['ROLE_PASSAGER']);
        $motDePasse = $this->passwordHasher->hashPassword($passager1, 'alimou123');
        $passager1->setPassword($motDePasse);
        $manager->persist($passager1);

        // Un deuxième conducteur : Kalil Dioubate
        $conducteur2 = new Utilisateur();
        $conducteur2->setEmail('kalilDioubate@gmail.com');
        $conducteur2->setNom('Dioubate');
        $conducteur2->setPrenom('Kalil');
        $conducteur2->setTelephone('6266666666');
        $conducteur2->setAdresse('Carrefour Chinois');
        $conducteur2->setEstVerifie(true);
        $conducteur2->setRoles(['ROLE_CONDUCTEUR']);
        $motDePasse = $this->passwordHasher->hashPassword($conducteur2, 'kalil123');
        $conducteur2->setPassword($motDePasse);
        $manager->persist($conducteur2);

        // Un véhicule appartenant au premier conducteur
        $vehicule1 = new Vehicule();
        $vehicule1->setMarque('Toyota');
        $vehicule1->setModele('Corolla');
        $vehicule1->setImmatriculation('RC-1234-A');
        $vehicule1->setNombreDePlaces(4);
        $vehicule1->setProprietaire($conducteur1);
        $manager->persist($vehicule1);

        // Un véhicule appartenant au deuxième conducteur
        $vehicule2 = new Vehicule();
        $vehicule2->setMarque('Hyundai');
        $vehicule2->setModele('Elantra');
        $vehicule2->setImmatriculation('RC-9999-C');
        $vehicule2->setNombreDePlaces(4);
        $vehicule2->setProprietaire($conducteur2);
        $manager->persist($vehicule2);

        // Un trajet publié par le premier conducteur, avec son véhicule
        $trajet1 = new Trajet();
        $trajet1->setDepart('Conakry, Minière');
        $trajet1->setArrivee('KM36');
        $trajet1->setDateDepart(new \DateTime());
        $trajet1->setHeureDepart(new \DateTime('07:30'));
        $trajet1->setPlacesDisponibles(4);
        $trajet1->setPrix('20000');
        $trajet1->setConducteur($conducteur1);
        $trajet1->setVehicule($vehicule1);
        $trajet1->setStatut('active');
        $manager->persist($trajet1);

        // Un deuxième trajet, publié par Kalil
        $trajet2 = new Trajet();
        $trajet2->setDepart('Kaloum');
        $trajet2->setArrivee('Kindia');
        $trajet2->setDateDepart(new \DateTime('+3 days'));
        $trajet2->setHeureDepart(new \DateTime('10:00'));
        $trajet2->setPlacesDisponibles(4);
        $trajet2->setPrix('15000');
        $trajet2->setConducteur($conducteur2);
        $trajet2->setVehicule($vehicule2);
        $trajet2->setStatut('active');
        $manager->persist($trajet2);

        // Une réservation faite par le passager sur le premier trajet
        $reservation1 = new Reservation();
        $reservation1->setPlacesReservees(1);
        $reservation1->setStatut('confirmee');
        $reservation1->setTrajet($trajet1);
        $reservation1->setPassager($passager1);
        $manager->persist($reservation1);

        // Le paiement lié à cette réservation
        $paiement1 = new Paiement();
        $paiement1->setFournisseur('orange_money');
        $paiement1->setReferenceTransaction('TX-2026-001');
        $paiement1->setMontant('20000');
        $paiement1->setStatut('paye');
        $paiement1->setReservation($reservation1);
        $manager->persist($paiement1);

        // L'avis laissé par le passager après le trajet
        $avis1 = new Avis();
        $avis1->setNote(5);
        $avis1->setCommentaire('Trajet agréable, conducteur ponctuel');
        $avis1->setReservation($reservation1);
        $avis1->setAuteur($passager1);
        $manager->persist($avis1);

        // Écrit réellement toutes les données en base, en une seule fois
        $manager->flush();
    }
}