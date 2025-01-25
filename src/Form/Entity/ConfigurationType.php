<?php

namespace App\Form\Entity;

use App\Entity\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cacheDuration', NumberType::class, [
                'label' => 'Durée du cache',
                'attr' => [
                    'placeholder' => 'Durée du cache en secondes',
                ],
            ])
            ->add('adminMail', EmailType::class, [
                'label' => 'Email de l\'administrateur',
                'attr' => [
                    'placeholder' => 'Email de l\'administrateur',
                ],
            ])
            ->add('useCache', ChoiceType::class, [
                'label' => 'Utiliser le cache',
                'choices' => [
                    'oui' => true,
                    'non' => false,
                ],
                'required' => true,
                'placeholder' => 'Choisir une option', // Option de choix par défaut
                'empty_data' => 1, // Valeur par défaut si aucun choix n'est fait
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
        ]);
    }
}
