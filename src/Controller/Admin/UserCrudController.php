<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastName')
                ->setLabel('Nom')
                ->setRequired(true),  

            TextField::new('firstName')
             ->setLabel('Prénom')
             ->setRequired(true),

            TextField::new('username')
                ->setLabel('Nom d\'utilisateur')
                ->setRequired(true),
        ];
    }

    /**
     * This crud global config.
     * https://symfony.com/bundles/EasyAdminBundle/current/crud.html#search-order-and-pagination-options
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            ->setSearchFields(['lastName', 'firstName', 'username'])
            ->setDefaultSort(['lastName' => 'ASC'])
            // Si je n'ai pas fermé la visibilité dans la sidebar dans AdminController.php : configureMenuItems()
            // alors, ici je peux aussi décider de cacher le contenu de ce CRUD aux rôle non autorisés
            // ->setEntityPermission('ROLE_ADMIN')
            // ->setPaginatorPageSize(10)
            // ->setPaginatorRangeSize(4)
            // https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/pagination.html
            // ->setPaginatorUseOutputWalkers(true)
            // ->setPaginatorFetchJoinCollection(true)
        ;
    }

     /**
     * This crud Filters
     * @return Filters
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('lastName', 'text', [
                'label' => 'Nom',
            ])
            ->add('firstName', 'text', [
                'label' => 'Prénom',
            ])
            ->add('username', 'text', [
                'label' => 'Nom d\'utilisateur',
            ])
            // ->add('subCategories', 'entity', [
            //     'label' => 'Sous catégories',
            //     'class' => SubCategory::class,
            //     'choice_label' => 'name',
            // ])
        ;
    }

}
