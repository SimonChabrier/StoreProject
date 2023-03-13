<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Faker\Core\Number;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // TextField::new('lastName')
            //     ->setLabel('Nom')
            //     ->setRequired(true),  

            // TextField::new('firstName')
            //  ->setLabel('Prénom')
            //  ->setRequired(true),

            // add id
            NumberField::new('id')
                ->setLabel('Id')
                ->setRequired(true),

            TextField::new('email')
                ->setLabel('Nom d\'utilisateur')
                ->setRequired(true),
            BooleanField::new('isVerified')
                ->setLabel('Compte vérifié')
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

            ->setSearchFields(['email', 'email', 'email'])
            ->setDefaultSort(['email' => 'ASC'])
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
            ->add('email', 'text', [
                'label' => 'Nom',
            ])
            // ->add('firstName', 'text', [
            //     'label' => 'Prénom',
            // ])
            // ->add('username', 'text', [
            //     'label' => 'Nom d\'utilisateur',
            // ])
            // ->add('subCategories', 'entity', [
            //     'label' => 'Sous catégories',
            //     'class' => SubCategory::class,
            //     'choice_label' => 'name',
            // ])
        ;
    }

}
