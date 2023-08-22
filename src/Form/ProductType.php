<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;


use App\Repository\ProductTypeRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Entity\ProductType as ProductTypeEntity;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use tidy;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'disabled' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nom du produit',
                ],

            ])    
            ->add('buyPrice', TextType::class, [
                'label' => 'Prix achat',
                'disabled' => false,
                'required' => false,
                // placeholder
                'attr' => [
                    'placeholder' => 'Prix achat HT',
                ],
            ])
            ->add('sellingPrice', TextType::class, [
                'label' => 'Prix de vente',
                'disabled' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Prix de vente TTC',
                ],
            ])
            ->add('catalogPrice', TextType::class, [
                'label' => 'Prix public',
                'disabled' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Prix public TTC',
                ],
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Choisir une option' => null,
                    'En ligne' => 1,
                    'Hors ligne' => 0,
                ],
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                // valeur par défaut si null
                // 'data' => 1,
            ])
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Choisir une marque',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                // non requis 
                'required' => false,
                // empty start value
                'placeholder' => 'Choisir une catégorie',
            ])
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'getSubCategoryName',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Choisir une sous-catégorie',
            ])
            // A utiliser si produit et sous catégories sont liés par une ManyToMany
            // ->add('subCategories', CollectionType::class, [
            //     'entry_type' => EntityType::class,
            //     'entry_options' => [
            //         'class' => SubCategory::class,
            //         'choice_label' => 'name',
            //         'by_reference' => false,
            //         'multiple' => false,
            //         'expanded' => true,
            //     ],
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'by_reference' => false,
            // ])
            ->add('productType', EntityType::class, [
                'class' => ProductTypeEntity::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,     
                'placeholder' => 'Choisir un type de produit',          
            ])
            // description textarea en utilisant le composant CKEditor
            ->add('description', CKEditorType::class, [
                'label' => 'Description',
                'required' => false,
                'config' => [
                    'toolbar' => 'full', // Configure CKEditor toolbar options
                ],
            ])
            // on set le form type à Ck editor
            ->add('description', CKEditorType::class, [
                'label' => 'Description',
                'required' => false,
                // 'config' => [
                //     'toolbar' => 'full', // Configure CKEditor toolbar options
                // ],
            ])
            
            ->add('productData', CollectionType::class, [
                    'entry_type' => ProductDataType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'label' => false,
            ])
            // multiple file upload (works good)
            // ->add('pictures', FileType::class,[
            //     'label' => false,
            //     'multiple' => true,
            //     'mapped' => false,
            //     'required' => false
            // ])
            // embed here PictureType using CollectionType
            ->add('pictures', CollectionType::class, [
                    'entry_type' => PictureType::class,
                    // 'entry_options' => ['label' => false],
                    'allow_add' => true,
                    'allow_delete' => true,                    
                    'by_reference' => false,
                    'prototype' => true,
                    'label' => false,
                    'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
