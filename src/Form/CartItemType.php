<?php

namespace App\Form;

use App\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// Formulaire de la page panier pour modifier la quantité et supprimer un produit
class CartItemType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', null, [
                'label' => 'Quantité',
                // ne pas autoriser les valeurs négatives
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('remove', SubmitType::class, [
                'label' => 'Supprimer'
            ])
            // ->add('product')
            // ->add('orderRef')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
        ]);
    }
}
