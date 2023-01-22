<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Basket;
use Doctrine\ORM\Mapping\Id;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BasketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Basket::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->setLabel('Référence Panier')
            ->setFormTypeOption('disabled', true),

            AssociationField::new('user', 'Client lié')
            // pour accéder à la liste des utilisateurs et surtout l'id de l utilisateur lié au panier 
            // pour pouvoir le modifier sinon on ne peut pas assigner le panier à un autre utilisateur
            ->setCrudController(UserCrudController::class),

            AssociationField::new('products')
            ->setLabel('Produits liés'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            //->setSearchFields(['lastName', 'firstName', 'username'])
            ->setDefaultSort(['id' => 'ASC'])
            // display username in list
            ->setEntityLabelInSingular('Panier')
            ->setEntityLabelInPlural('Paniers')
            ->setPaginatorPageSize(10)

            ->setPageTitle('index', 'Liste des %entity_label_plural%')
            ->setPageTitle('new', 'Ajouter un %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier le %entity_label_singular%')
            ->setPageTitle('detail', 'Détail du %entity_label_singular%')

            ->setDateTimeFormat('short', 'short')
            ->setDateFormat('short')
            ->setTimeFormat('short')

            ->setNumberFormat('decimal', 2)
            ->setPaginatorRangeSize(4)
            ->setPaginatorUseOutputWalkers(true)
            ->setPaginatorFetchJoinCollection(true)

        ;
    }
    
}
