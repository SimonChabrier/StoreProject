<?php

namespace App\Form\Order;

use App\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


// Formulaire utilisé sur la page de la command en cours pour modifier la quantité et supprimer un produit
// présent dans le panier de cette commande. On l'imbrique dans le formulaire OrderType

class OrderItemType extends AbstractType
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
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
        ]);
    }
}