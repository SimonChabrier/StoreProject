<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


// Formulaire de la page panier pour modifier la quantité et supprimer un produit
class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        // on ajoute le champ items qui est une collection de OrderItem (voir src/Entity/Order.php)
        // on imbrique le formulaire CartItemType pour chaque élément de la collection
            ->add('items', CollectionType::class, [
                'entry_type' => CartItemType::class
            ])
            // ici on va utiliser un subscriber pour évaluer si on sauvegarde ou si on vide le panier 
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour le panier'
            ])
            ->add('clear', SubmitType::class, [
                'label' => 'Vider le panier'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}