<?php

namespace App\Controller\BackOffice;

use App\Entity\Comment;
use App\Entity\Product;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    // configure crud fields
    public function configureCrud(Crud $crud): Crud
    {
        return $crud

            ->setPageTitle('detail', 'Commentaire')
            ->setPageTitle('new', 'Créer un nouveau commentaire')
            ->setPageTitle('edit', 'Modifier un commentaire')
            ->setPageTitle('index', 'Commentaires')

            ->setEntityLabelInSingular('Commentaire')
            ->setEntityLabelInPlural('Commentaires')

            ->setSearchFields(['text', 'author', 'product.name'])
            ->setDefaultSort(['product.name' => 'ASC'])
            ->setPaginatorPageSize(20)
        ;
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('product')
            ->setLabel('Produit'),

            TextField::new('author')
            ->setLabel('Auteur'),
            TextField::new('text')
            ->setLabel('Commentaire'),
        ];
    }

    // configure filters

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            
            ->add('author', 'text', [
                'label' => 'Auteur',
            ])
            ->add('text', 'text', [
                'label' => 'Produit',
                'choice_label' => 'Commentaire',
            ])
            ->add('product', 'entity', [
                'label' => 'Produit',
                'class' => Product::class,
                'choice_label' => 'name',
            ])
        ;
    }
}
