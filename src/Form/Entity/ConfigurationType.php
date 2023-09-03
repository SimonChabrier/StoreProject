<?php

namespace App\Form\Entity;

use App\Entity\Configuration;
use Faker\Core\Number;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cacheTtl', NumberType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Configuration::class,
        ]);
    }
}
