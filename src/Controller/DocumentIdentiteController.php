<?php

namespace App\Controller;

use App\Entity\DocumentIdentite;
use App\Form\DocumentIdentiteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

final class DocumentIdentiteController extends AbstractController
{
    #[Route('/document/identite', name: 'app_document_nouveau')]
    #[IsGranted("ROLE_USER")]
    public function nouveau(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        // On récupère ici le paramètre qu'on a défini dans services.yaml
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%documents_directory%')]
        string $documentsDirectory,
    ): Response {
        $document = new DocumentIdentite();
        $form = $this->createForm(DocumentIdentiteType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère le fichier envoyé (champ "mapped: false",
            // donc on va le chercher manuellement dans le formulaire)
            /** @var UploadedFile $fichier */
            $fichier = $form->get('fichier')->getData();

            if ($fichier) {
                // On récupère juste le nom du fichier, sans son extension
                $nomOriginal = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);

                // On "nettoie" ce nom pour qu'il soit sûr à utiliser dans une URL
                // (retire les accents, espaces, caractères spéciaux)
                $nomSecurise = $slugger->slug($nomOriginal);

                // On construit un nom de fichier unique, pour éviter que deux
                // utilisateurs qui uploadent "cni.jpg" ne s'écrasent l'un l'autre
                $nomFichier = $nomSecurise . '-' . uniqid() . '.' . $fichier->guessExtension();

                try {
                    // On déplace réellement le fichier depuis son emplacement
                    // temporaire vers notre dossier permanent
                    $fichier->move($documentsDirectory, $nomFichier);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi du fichier.');
                    return $this->redirectToRoute('app_home');
                }

                // On enregistre uniquement le NOM du fichier (pas le chemin complet)
                // dans l'entité — c'est notre "pointeur" vers le vrai fichier
                $document->setCheminFichier($nomFichier);
            }

            // Champs toujours définis par le serveur, jamais par l'utilisateur
            $document->setUtilisateur($this->getUser());
            $document->setStatut('en_attente');

            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Votre document a été envoyé et est en attente de validation.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('document_identite/index.html.twig', [
            'documentForm' => $form,
        ]);
    }
}