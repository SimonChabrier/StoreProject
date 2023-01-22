<?php

namespace App\Form;

use App\Entity\AttributeValue;
use App\Entity\ProductAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AttributeValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                // 'attr' => [
                //     'placeholder' => 'Rouge',
                // ],
                // 'block_prefix' => 'attribute_value',
                // 'label_attr' => ['class' => 'col-sm-2 col-form-label'],
                // 'attr' => ['class' => 'form-control'],
                // 'row_attr' => ['class' => 'form-group row'],
                // 'help' => 'Rouge ou 36 ou 1kg ou 10cm...',
                // 'help_attr' => ['class' => 'form-text text-muted'],
                // 'help_html' => true,
                // 'required' => false,
                // 'disabled' => false,
                // 'empty_data' => '0',
                // 'error_bubbling' => false,
                // 'error_mapping' => [],
                // 'invalid_message' => 'The value is not valid.',
                // 'invalid_message_parameters' => [],
                // 'mapped' => true,
                // 'trim' => true,
                // 'constraints' => [],
                // 'data' => null,
                // 'property_path' => null,

            ])
            // ->add('listOrder', TextType::class, [
            //     'label' => 'Ordre',
            //     'attr' => [
            //         'placeholder' => '1',
            //     ],
            // ])
            // ->add('productAttribute', EntityType::class, [
            //     'class' => ProductAttribute::class,
            //     'choice_label' => 'name',
            //     'multiple' => false,
            //     'expanded' => false,
            // ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AttributeValue::class,
        ]);
    }
}
