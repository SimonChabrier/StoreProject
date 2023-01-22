<?php

namespace App\Controller\Admin;

use App\Entity\TypeFamilly;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TypeFamillyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeFamilly::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
                ->setLabel('Nom')
                ->setRequired(true),
            AssociationField::new('types')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
            ])
        ];
    }
    
}
