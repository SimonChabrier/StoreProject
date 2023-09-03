<?php

namespace App\Controller\BackOffice;

use PharIo\Manifest\Email;
use App\Entity\Configuration;

use App\Form\Entity\ConfigurationType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }

    /**
     * Override the default CRUD configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        $this->setPageValues($crud, 'Configuration');
        return parent::configureCrud($crud);
    }

    /**
     * Configure the page values for a specific page
     */
    public function setPageValues(Crud $crud, string $pageName)
    {
        // Change the title of the specified page
        if ($pageName === 'Configuration') {
            $crud->setPageTitle('index', 'Liste des paramètres de configuration');
            $crud->setPageTitle('new', 'Ajouter un paramètre');
            $crud->setPageTitle('edit', 'Modifier un paramètre');
            $crud->setPageTitle('detail', 'Détails du paramètre');
            // tri par défaut sur la colonne id en ordre décroissant
            $crud->setDefaultSort(['id' => 'DESC']);
            $crud->setEntityPermission('ROLE_SUPER_ADMIN');
        }
    }

     /**
     * Configure fields for ProductType entity
     *
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [

            NumberField::new('id', 'ID')// get the id of the product
            ->setFormTypeOption('disabled', true),// disable the field in the form
            NumberField::new('cache_ttl', 'Durée du cache en secondes') ,
            EmailField::new('admin_mail', 'Email de l\'administrateur') ,
        ];
    }

    /**
     * This crud Actions (buttons) 
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new', 'delete')
        ;
    }



    
    /**
     * This crud Filters
     * @return Filters
     */
    // public function configureFilters(Filters $filters): Filters
    // {
    //     return $filters
    //         ->add('name', [
    //             'label' => 'Nom du type de produit',
    //         ])
    //     ;
    // }
}
