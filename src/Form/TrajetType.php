<?php

namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\VehiculeRepository;

class TrajetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère l'utilisateur connecté, passé depuis le contrôleur
        // via les "options" du formulaire (voir configureOptions ci-dessous)
        $utilisateur = $options['utilisateur'];

        $builder
            ->add('depart', TextType::class, [
                'label' => 'Ville de départ',
            ])
            ->add('arrivee', TextType::class, [
                'label' => 'Ville d\'arrivée',
            ])
            ->add('dateDepart', DateType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
            ])
            ->add('heureDepart', TimeType::class, [
                'label' => 'Heure de départ',
                'widget' => 'single_text',
            ])
            ->add('placesDisponibles', NumberType::class, [
                'label' => 'Places disponibles',
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (GNF)',
            ])
            // Pas de champ "conducteur" : toujours l'utilisateur connecté,
            // assigné dans le contrôleur
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => function (Vehicule $vehicule) {
                    return $vehicule->getMarque() . ' ' . $vehicule->getModele() . ' (' . $vehicule->getImmatriculation() . ')';
                },
                // On ne propose que les véhicules appartenant à l'utilisateur connecté
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($utilisateur) {
                    return $er->findByProprietaire($utilisateur);
                },
                'label' => 'Véhicule utilisé',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);

        // On déclare une nouvelle option "utilisateur", obligatoire,
        // que le contrôleur devra fournir à la création du formulaire
        $resolver->setRequired('utilisateur');
    }
}