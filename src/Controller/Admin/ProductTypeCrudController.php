<?php

namespace App\Controller\Admin;

use App\Entity\ProductType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
        ];
    }
    
}
