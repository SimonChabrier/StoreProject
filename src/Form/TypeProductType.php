<?php

namespace App\Form;

use App\Entity\ProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TypeProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Type de produit',
                'attr' => [
                    'placeholder' => 'Eg: chaussure, vêtement, accessoire, etc.',
                    'class' => 'form-control',
                ],
            ])
            // ->add('typeData', CollectionType::class, [
            //     'entry_type' => ProductDataType::class,
            //     'entry_options' => ['label' => false],
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'by_reference' => false,
            //     'label' => 'Caractéristiques du produit',
            //     'attr' => [
            //         'class' => 'form-control',
            //     ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductType::class,
        ]);
    }
}
