<?php

namespace App\Controller\Admin;

use App\Entity\Brand;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BrandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Brand::class;
    }

    /**
     * Override the default CRUD configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        $this->setPageValues($crud, 'Brand');
        return parent::configureCrud($crud);
    }

    /**
     * Configure the page values for a specific page
     */
    public function setPageValues(Crud $crud, string $pageName)
    {
        // Change the title of the specified page
        if ($pageName === 'Brand') {
            $crud->setPageTitle('index', 'Liste des marques');
            $crud->setPageTitle('new', 'Ajouter une marque');
            $crud->setPageTitle('edit', 'Modifier une marque');
            $crud->setPageTitle('detail', 'Détails de la marque');
            // tri par défaut sur la colonne id en ordre décroissant
            $crud->setDefaultSort(['id' => 'DESC']);
        }
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('id', 'ID')
            ->setFormTypeOption('disabled', true),
            TextField::new('name', 'Nom de la marque'),
        ];
    }
    
}
