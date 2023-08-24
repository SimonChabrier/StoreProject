<?php

namespace App\Controller\BackOffice;

use App\Entity\Category;
use App\Entity\SubCategory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SubCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubCategory::class;
    }

      /**
     * This crud global config.
     * https://symfony.com/bundles/EasyAdminBundle/current/crud.html#search-order-and-pagination-options
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            ->setPageTitle('index', 'Gestion des sous-catégories')
            ->setPageTitle('new', 'Créer une nouvelle sous-catégorie')
            ->setPageTitle('edit', 'Editer une sous-catégorie')
            ->setEntityLabelInSingular('Sous categorie')
            ->setEntityLabelInPlural('Sous categories')
            ->setSearchFields(['name', 'listOrder'])
           // ->setDefaultSort(['listOrder' => 'ASC'])
            ->setPaginatorPageSize(20)

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
     * This crud Fields
     * @return Fields
     */
    public function configureFields(string $pageName): iterable
    {
        return [

            AssociationField::new('categories', 'Catégorie parente')
            ->setRequired(true)
            // format value using return of getSubCategoryName() method from src/Entity/SubCategory.php
            ->formatValue(function ($value, $entity) {
                return $entity->getCategoryName();
            })
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ]),

            TextField::new('name', 'Nom de la sous-catégorie')
            ->setRequired(true),

            TextField::new('listOrder', 'Ordre d\'affichage')
            ->setRequired(true),

            AssociationField::new('products')
            ->setLabel('Produits liés')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ])
            ->hideOnForm(),

            AssociationField::new('productType', 'Type de produit')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ])
            ->hideOnForm(),
        ];
    }

     /**
     * This crud Filters
     * @return Filters
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('name', 'text', [
            'label' => 'Nom',
        ])
        ->add('listOrder', 'text', [
            'label' => 'Ordre',
        ])
        ->add('categories', 'entity', [
            'label' => 'Catégories',
            'class' => Category::class,
            'choice_label' => 'name',
        ])
        ;
    }


}
