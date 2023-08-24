<?php

namespace App\Controller\BackOffice;

use App\Entity\Category;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
     * Override the default CRUD configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        $this->setPageValues($crud, 'Category');
        return parent::configureCrud($crud);
    }

    /**
     * Configure the page values for a specific page
     */
    public function setPageValues(Crud $crud, string $pageName)
    {
        // Change the title of the specified page
        if ($pageName === 'Category') {
            $crud->setPageTitle('index', 'Liste des categories');
            $crud->setPageTitle('new', 'Ajouter une categorie');
            $crud->setPageTitle('edit', 'Modifier une categorie');
            $crud->setPageTitle('detail', 'Détails de la categorie');
            // tri par défaut sur la colonne id en ordre décroissant
            $crud->setDefaultSort(['id' => 'DESC']);
        }
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
