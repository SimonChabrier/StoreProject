<?php

namespace App\Controller\Admin;

use App\Entity\ProductAttribute;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductAttributeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductAttribute::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            // CollectionField::new('attributeValues', 'test')
            //     ->setEntryType(AttributeValueType::class)
            //     ->setFormTypeOptions([
            //         'label' => 'Valeurs',
            //         'allow_add' => true,
            //         'allow_delete' => true,
            //         'by_reference' => false,
            //         'label' => 'Valeurs',
            //         'attr' => ['placeholder' => 'Rouge ou 36 ou 1kg ou 10cm...'],
            //         // change accordion lable to "Valeurs" dosn't work


            //     ])
            //     ->setLabel('Valeurs')
            //     ->renderExpanded()
                //->setEntryIsComplex(),
        ];
    }
    
}
