<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class, [
                'label' => 'Marque',
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
            ])
            ->add('immatriculation', TextType::class, [
                'label' => 'Immatriculation',
            ])
            ->add('nombreDePlaces', IntegerType::class, [
                'label' => 'Nombre de places disponibles',
            ])
            // Pas de champ "proprietaire" ici : il sera assigné
            // automatiquement dans le contrôleur, à l'utilisateur connecté
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}