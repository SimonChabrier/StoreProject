<?php

namespace App\Controller\Admin;

use App\Entity\Category;


use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;


use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    /**
     * This crud hide actions
     * @return Actions
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom')
            ->setRequired(true),

            TextField::new('listOrder', 'Ordre d\'affichage')
            ->setRequired(true),

            AssociationField::new('subCategories', 'Sous-catégories liés')
            ->setFormTypeOptions(
                [
                    'by_reference' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choice_label' => 'getSubCategoryName',
                ]
                ),
            BooleanField::new('showOnHome', 'Afficher sur la page d\'accueil')
            ->setRequired(true),
            // AssociationField::new('products')
            // ->setLabel('Produits liés')
            // ->setFormTypeOptions([
            //     'by_reference' => false,
            //     'multiple' => true,
            //     'expanded' => true,
            //     'choice_label' => 'name',
            // ])
            // // display products count or 'Aucun produit' if no product in those categories
            // ->formatValue(function ($value, $entity) {
            //     $products = $entity->getProducts();
            //     foreach ($products as $product) {
            //         $productNames[] = $product->getName();
            //     }
            //     if (empty($productNames)) {
            //         return 'Aucun produit';
            //     }
            //     return count($products);
            // }),
        ];
    }

    /**
     * This crud global config.
     * https://symfony.com/bundles/EasyAdminBundle/current/crud.html#search-order-and-pagination-options
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            ->setSearchFields(['name', 'listOrder'])
            ->setDefaultSort(['listOrder' => 'ASC'])
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
            ->add('name', 'text', [
                'label' => 'Nom',
            ])
            ->add('listOrder', 'text', [
                'label' => 'Ordre',
            ])
            ->add('subCategories', 'entity', [
                'label' => 'Sous catégories',
                'class' => SubCategory::class,
                'choice_label' => 'name',
            ])
        ;
    }

}
