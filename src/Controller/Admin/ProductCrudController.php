<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductDataType;
use Symfony\Component\Form\FormTypeInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use App\Controller\Admin\ProductTypeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
            ->setLabel('Nom')
            ->setRequired(true),
            
            TextField::new('buyPrice')
            ->setLabel('Prix d\'achat HT')
            ->setRequired(true),
            
            TextField::new('sellingPrice')
            ->setLabel('Prix de vente TTC')
            ->setRequired(true),
            
            TextField::new('catalogPrice')
            ->setLabel('Prix catalogue')
            ->setRequired(true),

            TextField::new('tauxMarque')
            ->setLabel('Taux de marque')
            ->setFormTypeOption('disabled', true),
            
            AssociationField::new('category')
            ->setLabel('Catégorie')
            ->setRequired(true),
            
            AssociationField::new('subCategory')
            ->setLabel('Sous-catégorie')
            ->setRequired(true),

            BooleanField::new('visibility')
            ->setLabel('Visible')
            ->setRequired(true),

            AssociationField::new('productType')
            ->setLabel('Types de produits')
            ->setRequired(true),

            // use ProdctDataType to manage prodcut data (attributes)
            CollectionField::new('productData', 'Données du produit')
            ->setEntryType(ProductDataType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(true)
         
        ];
    }

    // layout
    // public function configureCrud(Crud $crud): Crud
    // {
    //     return $crud
    //         // ...
    //     ;
    // }

    // filters
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name', 'text', [
                'label' => 'Nom',
            ])
            ->add('category', 'entity', [
                'label' => 'Catégorie parente',
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('subCategory', 'entity', [
                'label' => 'Sous-catégorie',
                'class' => SubCategory::class,
                'choice_label' => 'name',
            ])
        ;
    }

}
