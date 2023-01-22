<?php

namespace App\Form;

use Doctrine\DBAL\Types\ArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDataType extends AbstractType
{   
    // FROM non mapé à une entité (pas de relation)
    // utilisé pour le formulaire d'ajout de caratériqtiques produit dans le CRUD Product
    // EassyAdmin
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('key', TextType::class, [
            'label' => 'Propriété :', 
        ])
        ->add('value', TextType::class, [
            'label' => 'Valeur',
        ]);
        // add choices to your form
        // ->add('value', ChoiceType::class, [
        //     'choices' => [
        //         'Choix 1' => 'Choix 1',
        //         'Choix 2' => 'Choix 2',
        //         'Choix 3' => 'Choix 3',
        //     ],
        //     'label' => 'Valeur',
        // ]);

            // ->add('key', CollectionType::class, [
            //     'entry_type' => TextType::class,
            //     'entry_options' => [
            //         'label' => false,
            //     ],
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'by_reference' => false,
            //     'label' => 'Clé',
            //     'required' => true,
            // ])
            // ->add('value', CollectionType::class, [
            //     'entry_type' => TextType::class,
            //     'entry_options' => [
            //         'label' => false,
            //     ],
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'by_reference' => false,
            //     'label' => 'Valeur',
            //     'required' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
