<?php

namespace App\Form\Order;

use App\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Formulaire de la page produit pour ajouter un produit au panier
// rendu par le ProductController sur la route /product/{id}

class AddProductToOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', null, [
            'label' => 'Quantité',
        ]);
        $builder->add('add', SubmitType::class, [
            'label' => 'Ajouter au panier'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
        ]);
    }
}