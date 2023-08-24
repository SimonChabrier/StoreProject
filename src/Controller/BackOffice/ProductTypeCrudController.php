<?php

namespace App\Controller\BackOffice;

use App\Entity\ProductType;
use App\Form\Entity\ProductDataType;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductType::class;
    }

    /**
     * Override the default CRUD configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        $this->setPageValues($crud, 'ProductType');
        return parent::configureCrud($crud);
    }

    /**
     * Configure the page values for a specific page
     */
    public function setPageValues(Crud $crud, string $pageName)
    {
        // Change the title of the specified page
        if ($pageName === 'ProductType') {
            $crud->setPageTitle('index', 'Liste des types produits');
            $crud->setPageTitle('new', 'Ajouter un type produit');
            $crud->setPageTitle('edit', 'Modifier un type produit');
            $crud->setPageTitle('detail', 'Détails du type produit');
            // tri par défaut sur la colonne id en ordre décroissant
            $crud->setDefaultSort(['id' => 'DESC']);
        }
    }

     /**
     * Configure fields for ProductType entity
     *
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [

            NumberField::new('id', 'ID')// get the id of the product
            ->setFormTypeOption('disabled', true),// disable the field in the form

            TextField::new('name', 'Nom du type de produit'),

            AssociationField::new('subCategories', 'Sous catégrories liées')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => false,
                'choice_label' => 'getSubCategoryName',
            ]),
            // ->formatValue(function ($value, $entity) {
            //     return $entity->getSubCategories()->first()->getName();
            // }),
    
            AssociationField::new('products', 'Nombre de produits liés')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ])
            // ne pas afficher dans le formulaire de création
            ->hideOnForm(),
            
            CollectionField::new('typeData', 'Caractéristiques du type de produit')
            ->setEntryType(ProductDataType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(false),
            
        ];
    }
    
    /**
     * This crud Filters
     * @return Filters
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name', [
                'label' => 'Nom du type de produit',
            ])
        ;
    }
}
