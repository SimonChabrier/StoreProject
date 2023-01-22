<?php

namespace App\Controller\Admin;

use App\Entity\ProductType;
use App\Form\SetProductAttributeType;
use Symfony\Component\Form\FormTypeInterface;
use App\Repository\ProductAttributeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Controller\Admin\ProductAttributeCrudController;
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
            TextField::new('name'),
            AssociationField::new('attributes')
            // link to the related ProductAttribute entities
            ->setFormTypeOption('by_reference', false)
            // allow to edit the related ProductAttribute entities
            ->setFormTypeOption('multiple', true)
            // allow to select multiple ProductAttribute entities
            ->setFormTypeOption('expanded', true),
            // display the ProductAttribute entities as checkboxes
            
            // add a collection field on ProdcutType to manage the related ProductAttribute entities
            // CollectionField::new('attributes')
            // ->setFormType(SetProductAttributeType::class)
            // ->setFormTypeOption('entry_type', ProductAttributeCrudController::class)



            //->setEntryType(SetProductAttributeType::class)
            // add a new form to create new ProductAttribute entities
            //->setFormTypeOption('entry_type', ProductAttributeCrudController::class)
            // use other CrudController to manage the related entities
            //->setFormTypeOption('entry_type', ProductAttributeCrudController::class),

            

            

        ];
    }
    
}
