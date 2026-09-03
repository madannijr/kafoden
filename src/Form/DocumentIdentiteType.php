<?php

namespace App\Form;

use App\Entity\DocumentIdentite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentIdentiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de document',
                'choices' => [
                    'Carte d\'identité' => 'cni',
                    'Permis de conduire' => 'permis',
                    'Carte grise' => 'carte_grise',
                ],
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date d\'expiration (si applicable)',
                'widget' => 'single_text',
                'required' => false,
            ])
            // Champ de fichier : PAS lié directement à l'entité (mapped: false),
            // car l'entité attend une chaîne de caractères (cheminFichier),
            // pas un fichier. On fera le lien manuellement dans le contrôleur.
            ->add('fichier', FileType::class, [
                'label' => 'Photo ou scan du document',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ],
                        mimeTypesMessage: 'Merci de déposer un fichier JPEG, PNG ou PDF valide.',
                    ),
                ],
            ])
            // Pas de champ "utilisateur" : toujours l'utilisateur connecté
            // Pas de champ "statut" : toujours "en_attente" à la création,
            // seul un admin peut le faire évoluer
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentIdentite::class,
        ]);
    }
}