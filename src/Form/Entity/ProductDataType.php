<?php

namespace App\Form\Entity;

use Symfony\Component\Form\AbstractType;
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
            'label' => 'Valeur :',
        ]);
        //$options['template'] = 'bundles/EasyAdminBundle/crud/field/collection.html.twig';
        //;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
