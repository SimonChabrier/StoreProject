<?php

namespace App\Controller\Admin;

use App\Entity\ProductType;
use App\Form\ProductDataType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\Mapping\Builder\AssociationBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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

    
    public function configureFields(string $pageName): iterable
    {
        return [

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

            TextField::new('name', 'Nom du type de produit'),
            

            AssociationField::new('products', 'Nombre de produits liés')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ]),
            
            CollectionField::new('typeData', 'Caractéristiques du type de produit')
            ->setEntryType(ProductDataType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(true),
            
        ];
    }
    
}
