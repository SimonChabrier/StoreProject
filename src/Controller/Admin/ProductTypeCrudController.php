<?php

namespace App\Controller\Admin;

use App\Entity\ProductType;
use App\Form\ProductDataType;
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
