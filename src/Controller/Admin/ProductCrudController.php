<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
            ->setLabel('Prix d\'achat')
            ->setRequired(true),
            
            TextField::new('sellingPrice')
            ->setLabel('Prix de vente')
            ->setRequired(true),
            
            TextField::new('catalogPrice')
            ->setLabel('Prix catalogue')
            ->setRequired(true),
            
            AssociationField::new('category')
            ->setLabel('Catégorie')
            ->setRequired(true),
            
            AssociationField::new('subCategory')
            ->setLabel('Sous-catégorie')
            ->setRequired(true),
        ];
    }

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
