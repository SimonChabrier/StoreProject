<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\ProductType as ProductTypeEntity;

use App\Form\SetProductType;
use App\Form\SetProductAttributeType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product name',
                'disabled' => false,
            ])    
            ->add('buyPrice', TextType::class, [
                'label' => 'Prix achat',
                'disabled' => false,
            ])
            ->add('sellingPrice', TextType::class, [
                'label' => 'Prix de vente',
                'disabled' => false,
            ])
            ->add('catalogPrice', TextType::class, [
                'label' => 'Prix catalogue',
                'disabled' => false,
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'En ligne' => 1,
                    'Hors ligne' => 0,
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('subCategory', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('productType', EntityType::class, [
                'class' => ProductTypeEntity::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('productData', CollectionType::class, [
                'entry_type' => ProductDataType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
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
