<?php

namespace App\Controller\Admin;

use App\Entity\AttributeValue;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class AttributeValueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AttributeValue::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
        ];
    }
    
}
