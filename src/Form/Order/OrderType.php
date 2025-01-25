<?php

namespace App\Form\Order;

use App\Entity\Order;
use App\Form\Order\OrderItemType;
use App\Service\Order\OrderManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\EventSubscriber\Order\ClearCartSubscriber;
use App\EventSubscriber\Order\UpdateCartSubscriber;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

// Formulaire général de la page panier 
// il imbrique le formulaire OrderItemType pour chaque élément du panier
// il utilise les EventSubscriber ClearCartSubscriber et UpdateCartSubscriber
// pour vider le panier ou supprimer un produit du panier

class OrderType extends AbstractType
{
    private $OrderManager;
    private $entityManager;

    public function __construct(OrderManager $OrderManager, EntityManagerInterface $entityManager)
    {
        $this->OrderManager = $OrderManager;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        // on ajoute le champ items qui est une collection de OrderItem (voir src/Entity/Order.php)
        // on imbrique le formulaire CartItemType pour chaque élément de la collection
            ->add('items', CollectionType::class, [
                'entry_type' => OrderItemType::class
            ])
            // ici on va utiliser un subscriber pour évaluer si on sauvegarde ou si on vide le panier 
            ->add('update', SubmitType::class, [
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
        $builder->addEventSubscriber(new UpdateCartSubscriber($this->OrderManager, $this->entityManager));
        $builder->addEventSubscriber(new ClearCartSubscriber($this->OrderManager));
    }
}
