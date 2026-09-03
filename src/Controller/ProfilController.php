<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\PhotoProfilType;
use App\Form\ModifierProfilType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProfilController extends AbstractController
{
    // Affiche le profil PUBLIC d'un utilisateur (accessible à tous, même non connecté)
    #[Route('/utilisateur/{id}', name: 'app_profil')]
    public function voirProfil(Utilisateur $utilisateur, ReservationRepository $reservationRepository): Response
    {
        // Par défaut, on cache le téléphone
        $peutVoirTelephone = false;

        // Si l'utilisateur visite son propre profil public, on le redirige
        // vers "Mon profil", qui est la vraie page adaptée à ce cas
        if ($this->getUser() && $this->getUser()->getId() === $utilisateur->getId()) {
            return $this->redirectToRoute('app_mon_profil');
        }

        // On ne vérifie la réservation que si quelqu'un est connecté
        if ($this->getUser()) {
            $peutVoirTelephone = $reservationRepository->existeReservationEntre(
                $this->getUser(),
                $utilisateur,
            );
        }

        return $this->render('profil/index.html.twig', [
            'utilisateur' => $utilisateur,
            'peutVoirTelephone' => $peutVoirTelephone,
        ]);
    }

    // Affiche le profil PRIVÉ de l'utilisateur connecté (ses propres infos, sans restriction)
    #[Route('/mon-profil', name: 'app_mon_profil')]
    #[IsGranted('ROLE_USER')]
    public function monProfil(): Response
    {
        return $this->render('profil/mon_profil.html.twig');
    }

    // Upload/changement de la photo de profil de l'utilisateur connecté
    #[Route('/profil/photo', name: 'app_profil_photo')]
    #[IsGranted('ROLE_USER')]
    public function changerPhoto(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        // On récupère le dossier de destination défini dans services.yaml
        #[Autowire('%photos_directory%')]
        string $photosDirectory,
    ): Response {
        // On crée le formulaire directement sur l'utilisateur connecté
        $form = $this->createForm(PhotoProfilType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère le fichier envoyé (champ non lié directement à l'entité)
            /** @var UploadedFile $fichier */
            $fichier = $form->get('photoFichier')->getData();

            if ($fichier) {
                // On nettoie le nom du fichier et on le rend unique
                $nomOriginal = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $nomSecurise = $slugger->slug($nomOriginal);
                $nomFichier = $nomSecurise . '-' . uniqid() . '.' . $fichier->guessExtension();

                try {
                    // On déplace le fichier vers le dossier permanent
                    $fichier->move($photosDirectory, $nomFichier);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de la photo.');
                    return $this->redirectToRoute('app_mon_profil');
                }

                // On enregistre le nom du fichier sur l'utilisateur connecté
                $this->getUser()->setPhoto($nomFichier);
            }

            // Pas besoin de persist() : l'utilisateur existe déjà en base
            $entityManager->flush();

            $this->addFlash('success', 'Votre photo de profil a été mise à jour.');

            return $this->redirectToRoute('app_mon_profil');
        }

        return $this->render('profil/changer_photo.html.twig', [
            'photoForm' => $form,
        ]);
    }

    // fonction de modification de profil
    #[Route('/profil/modifier', name: 'app_profil_modifier')]
    #[isGranted('ROLE_USER')]
    public function modifierProfil(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response {
        // on recupére l'utilisateur connecté
        $utilisateur = $this->getUser();
        $form = $this->createForm(ModifierProfilType::class, $utilisateur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Les champs nom, prenom, telephone ont déjà été appliqués
            // automatiquement sur l'utilisateur par Symfony (formulaire lié à l'entité)
            // flush() écrit réellement ces changements en base de données
            $entityManager->flush();
            $this->addFlash('success', "Votre profil a été mis à jour .");
            return $this->redirectToRoute('app_mon_profil');
        }
        return $this->render('profil/modifier_profil.html.twig', [
            'modifierForm' => $form,
        ]);
    }
}