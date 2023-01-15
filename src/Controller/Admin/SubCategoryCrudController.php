<?php

namespace App\Controller\Admin;

use App\Entity\Category;

use App\Entity\SubCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\MakerBundle\Doctrine\EntityDetails;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SubCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubCategory::class;
    }

      /**
     * This crud global config.
     * https://symfony.com/bundles/EasyAdminBundle/current/crud.html#search-order-and-pagination-options
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            ->setPageTitle('index', 'Gestion des sous-catégories')
            ->setPageTitle('new', 'Créer une nouvelle sous-catégorie')
            ->setPageTitle('edit', 'Editer une sous-catégorie')
            ->setEntityLabelInSingular('Sous categorie')
            ->setEntityLabelInPlural('Sous categories')
            ->setSearchFields(['name', 'listOrder'])
            ->setDefaultSort(['listOrder' => 'ASC'])
            ->setPaginatorPageSize(20)

            // Si je n'ai pas fermé la visibilité dans la sidebar dans AdminController.php : configureMenuItems()
            // alors, ici je peux aussi décider de cacher le contenu de ce CRUD aux rôle non autorisés
            // ->setEntityPermission('ROLE_ADMIN')
            // ->setPaginatorPageSize(10)
            // ->setPaginatorRangeSize(4)
            // https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/pagination.html
            // ->setPaginatorUseOutputWalkers(true)
            // ->setPaginatorFetchJoinCollection(true)
        ;
    }

     /**
     * This crud Fields
     * @return Fields
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
            // traduce the label
            ->setLabel('Nom')
            ->setRequired(true),
            TextField::new('listOrder')
            ->setLabel('Ordre d\'affichage'),
            AssociationField::new('categories')
            ->setLabel('Categories liés')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ]),
            AssociationField::new('products')
            ->setLabel('Produits liés')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
            ]),
        ];
    }

     /**
     * This crud Filters
     * @return Filters
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('name', 'text', [
            'label' => 'Nom',
        ])
        ->add('listOrder', 'text', [
            'label' => 'Ordre',
        ])
            ->add('categories', 'entity', [
                'label' => 'Catégories',
                'class' => Category::class,
                'choice_label' => 'name',
            ])
    
        ;
    }


}
