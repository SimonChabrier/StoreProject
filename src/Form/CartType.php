<?php

namespace App\Form;

use App\Entity\Order;
use App\Manager\CartManager;
use App\Storage\CartSessionStorage; 
use Symfony\Component\Form\AbstractType;
use App\Form\EventListener\ClearCartListener;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\EventListener\RemoveCartItemListener;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


// Formulaire de la page panier pour modifier la quantité et supprimer un produit
class CartType extends AbstractType
{
    private $cartManager;

    public function __construct(CartManager $cartManager)
    {
        $this->cartManager = $cartManager;
    }

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
        
        // Appel des EventSubscriber au formulaire 
        $this->addSubscribers($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }

    private function addSubscribers(FormBuilderInterface $builder): void
    {
        $builder->addEventSubscriber(new RemoveCartItemListener($this->cartManager));
        $builder->addEventSubscriber(new ClearCartListener($this->cartManager));
    }
}
