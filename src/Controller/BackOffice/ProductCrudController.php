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
use PhpParser\Node\Expr\Cast\Bool_;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
        return parent::configureCrud(
            $crud->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
        );
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

            //TODO ajuster ça avec le listener pour pouvoir utiliser sur l'index
            // BooleanField::new('visibility', 'Visible')
            // ->hideOnIndex(),
            
            ChoiceField::new('visibility', 'Visible')
            ->setChoices([
                'En ligne' => 1,
                'Hors ligne' => 0,
            ])
            ->setRequired(true)
            ->setFormTypeOption('disabled', false),
            
            //TODO ajuster ça avec le listener pour pouvoir utiliser sur l'index
            // BooleanField::new('inStock', 'Disponible')
            // ->hideOnIndex(),

            ChoiceField::new('inStock', 'Disponible')
            ->setChoices([
                'Disponible' => 1,
                'Indisponible' => 0,
            ])
            ->setRequired(true)
            ->setFormTypeOption('disabled', false),
                    
            
            NumberField::new('inStockQuantity', 'Quantité en stock')
            ->setRequired(true)
            ->hideOnIndex(),

            NumberField::new('reservedQuantity', 'Quantité en commande client')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),

            NumberField::new('inSupplierOrderQuantity', 'Quantité en commande fournisseur')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),

            TextField::new('name', 'Nom')
            ->setRequired(true),
            
            TextField::new('buyPrice', 'Prix d\'achat HT')
            ->setRequired(false)
            ->hideOnIndex(),
            
            TextField::new('sellingPrice', 'Prix de vente TTC')
            ->setRequired(true)
            ->hideOnIndex(),
            
            TextField::new('catalogPrice', 'Prix catalogue TTC')
            ->setRequired(true)
            ->hideOnIndex(),

            TextField::new('margeBrute', 'Marge brute %')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),

            TextField::new('margeNette', 'Marge nette %')
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex(),
            
            IntegerField::new('coefficientMarge', 'Coefficient de marge')
            ->setFormTypeOption('disabled', true),

            AssociationField::new('category', 'Catégorie')
            ->setRequired(false),
            
            AssociationField::new('subCategory', 'Sous-Catégorie')
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
            // on n'affiche pas la colonne description dans la liste des produits
            ->hideOnIndex(),

            // use ProdctDataType to manage prodcut data (attributes)
            CollectionField::new('productData', 'Infos')
            ->setEntryType(ProductDataType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(false),

            // use PicturesType to manage pictures
            CollectionField::new('pictures', 'Img')
            ->setEntryType(PictureType::class, [])
            ->setCustomOption('allow_add', true)
            ->renderExpanded(false)
            // upload pictures addes in input file to the server
            ->setFormTypeOption('by_reference', false)
            // ne pas autoriser le tri sur cette colonne pour le moment j'ai un bug
            // TODO à débugger plus tard
            ->setSortable(false),
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
