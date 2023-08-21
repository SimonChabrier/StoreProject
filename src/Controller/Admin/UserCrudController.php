<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * Override the default CRUD configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        $this->setPageValues($crud, 'User');
        return parent::configureCrud($crud);
    }

    /**
     * Configure the page values for a specific page
     */
    public function setPageValues(Crud $crud, string $pageName)
    {
        // Change the title of the specified page
        if ($pageName === 'User') {
            $crud->setPageTitle('index', 'Liste des utilisateurs');
            $crud->setPageTitle('new', 'Ajouter un utilisateur');
            $crud->setPageTitle('edit', 'Modifier un utilisateur');
            $crud->setPageTitle('detail', 'Détails de l\'utilisateur');
            // tri par défaut sur la colonne id en ordre décroissant
            $crud->setDefaultSort(['id' => 'DESC']);
        }
    }

     /**
     * Configure fields for User entity
     *
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {   
        return [
            NumberField::new('id')
                ->setLabel('Id')
                ->setRequired(true)
                // ATTENTION : ne pas rendre le champ modifiable pour ne pas 
                // créer de bug car un id ne doit pas être modifié
                ->setFormTypeOption('disabled', true),
            TextField::new('email')
                ->setLabel('Identifiant')
                ->setRequired(true),
                DateField::new('createdAt')
                ->setLabel('Incrit le')
                ->setFormTypeOption('disabled', true),
            BooleanField::new('isVerified')
                ->setLabel('Compte vérifié')
                ->setRequired(true),
            // Pour afficher les rôles dans le formulaire utiliser un ArrayField sinon erreur    
            ArrayField::new('roles')
                ->hideOnIndex()
                ->setLabel('Role'),
        ];
    }

     /**
     * This crud Filters
     * @return Filters
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('email', 'text', [
                'label' => 'Identifiant',
            ])
            // ->add('firstName', 'text', [
            //     'label' => 'Prénom',
            // ])
            // ->add('username', 'text', [
            //     'label' => 'Nom d\'utilisateur',
            // ])
            // ->add('subCategories', 'entity', [
            //     'label' => 'Sous catégories',
            //     'class' => SubCategory::class,
            //     'choice_label' => 'name',
            // ])
        ;
    }

}
