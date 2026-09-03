<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PhotoProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photoFichier', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File(
                        maxSize: '3M',
                        mimeTypes: ['image/jpeg', 'image/png'],
                        mimeTypesMessage: 'Merci de déposer une image JPEG ou PNG valide.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}