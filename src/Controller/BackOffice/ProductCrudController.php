<?php

namespace App\Controller\BackOffice;

use App\Entity\Brand;
use App\Entity\Product;
use App\Form\Entity\PictureType;
use App\Form\Entity\ProductDataType;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class ProductCrudController extends AbstractCrudController
{   
    /**
     * Configure the entity which will be managed by this controller
     *
     * @return string
     */
    public static function getEntityFqcn(): string
    {   
        return Product::class;
    }

    /**
     * Override the default CRUD configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        $this->setPageValues($crud, 'Product');
        return $crud;
    }

    /**
     * Configure the page values for a specific page
     */
    public function setPageValues(Crud $crud, string $pageName)
    {
        // Change the title of the specified page
        if ($pageName === 'Product') {
            $crud->setPageTitle('index', 'Liste des produits');
            $crud->setPageTitle('new', 'Ajouter un produit');
            $crud->setPageTitle('edit', 'Modifier un produit');
            $crud->setPageTitle('detail', 'Détails du produit');
            // tri par défaut sur la colonne id en ordre décroissant
            $crud->setDefaultSort(['id' => 'DESC']);
            $crud->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
        }
    }


    /**
     * Configure fields for Product entity
     *
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [

            NumberField::new('id', 'ID')
            ->setFormTypeOption('disabled', true),

            TextField::new('name', 'Nom')
            ->setRequired(true),

            AssociationField::new('category', 'Catégorie')
            // set label for form
            ->setFormTypeOption('label', 'Catégorie du produit (place le produit à la racine d\'une catégorie de premier niveau)')
            ->setRequired(false),
            
            AssociationField::new('subCategory', 'Sous-Catégorie')
            // set label for form
            ->setFormTypeOption('label', 'Sous-Catégorie (Eg / Homme : Runnning = la sous catégorie "Running" est liée à la catégorie racine "Homme"')
            // display only subcategory linked to the slectecd category
            // set choice on getSubCategoryName method from SubCategory entity
            ->setFormTypeOption('choice_label', 'getSubCategoryName')
            // display only subcategory linked to the slectecd category
            ->setRequired(true),

            AssociationField::new('productType', 'Type')
            ->setFormTypeOption('choice_label', 'name')
            ->setRequired(true),

            AssociationField::new('brand', 'Marque')
            ->setFormTypeOption('choice_label', 'name')
            ->setRequired(true),

            BooleanField::new('visibility', 'Visible'),

            BooleanField::new('isInStock', 'Disponible'),

            // ChoiceField::new('visibility', 'Visible')
            // ->setChoices([
            //     'En ligne' => 1,
            //     'Hors ligne' => 0,
            // ])
            // ->setRequired(true)
            // ->setFormTypeOption('disabled', false),

            // ChoiceField::new('isInStock', 'Disponible')
            // ->setChoices([
            //     'Disponible' => 1,
            //     'Indisponible' => 0,
            // ])
            // ->setRequired(true)
            // ->setFormTypeOption('disabled', false),
            
            NumberField::new('inStockQuantity', 'Qte en stock')
            ->setRequired(true),

            NumberField::new('onOrderQuantity', 'Qte en cmd client')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),

            NumberField::new('inSupplierOrderQuantity', 'Qte en cmd fournisseur')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),
            
            NumberField::new('buyPrice', 'Prix d\'achat HT')
            ->setRequired(false)
            ->setNumDecimals(2)
            ->hideOnIndex(),
            
            NumberField::new('sellingPrice', 'Prix de vente TTC')
            ->setNumDecimals(2)
            ->setRequired(true)
            ->hideOnIndex(),
            
            NumberField::new('catalogPrice', 'Prix catalogue TTC')
            ->setRequired(false)
            ->setNumDecimals(2)
            ->hideOnIndex(),

            TextField::new('margeBrute', 'Marge brute %')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),

            TextField::new('margeNette', 'Marge nette %')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),
            
            NumberField::new('coefficientMarge', 'Coeff de marge')
            ->setNumDecimals(2)
            ->setFormTypeOption('disabled', true),

            // description textarea en utilisant le composant CKEditor
            TextareaField::new('description', 'Description')
            ->setRequired(false)
            ->setFormType(CKEditorType::class)
            ->setFormTypeOptions([
                // 'config' => [
                //     'toolbar' => 'full', // Configure CKEditor toolbar options
                // ],
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->hideOnIndex(),

            // use ProdctDataType to manage product data (attributes)
            CollectionField::new('productData', 'Infos')
            ->setEntryType(ProductDataType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(false),
            
            // use PicturesType to manage pictures
            CollectionField::new('pictures', 'Img')
            ->setEntryType(PictureType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(false)
            ->setFormTypeOption('by_reference', false)
            // ne pas autoriser le tri sur cette colonne pour le moment j'ai un bug à cause de la relation avec product
            // TODO à débugger plus tard
            ->setSortable(false)
            //  gérer le tri sur le relation avec product
            // ->setCustomOption('sortable', function ($associatedEntity) {
            //     return $associatedEntity->getProduct()->getId();
            // })
            // gèrer le tri sur la relation avec product            
        ];
    }

    /**
     * Configure filters for Product entity
     *
     * @param Filters $filters
     * @return Filters
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name', 'text', [
                'label' => 'Nom',
            ])
            ->add('category', 'entity', [
                'label' => 'Catégorie parente',
                'class' => Category::class,
                'choice_label' => 'name',
            ])
            ->add('subCategory', 'entity', [
                'label' => 'Sous-catégorie',
                'class' => SubCategory::class,
                'choice_label' => 'name',
            ])
            ->add('productType', 'entity', [
                'label' => 'Type de produit',
                'class' => ProductType::class,
                'choice_label' => 'name',
            ])
            ->add('brand', 'entity', [
                'label' => 'Marque',
                'class' => Brand::class,
                'choice_label' => 'name',
            ])
            ->add('visibility', 'boolean', [
                'label' => 'Visible',
            ])
            ->add('id', 'number', [
                'label' => 'ID',
            ])
        ;
    }

}
