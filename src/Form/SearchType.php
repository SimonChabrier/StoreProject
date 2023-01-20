<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('min', TextType::class, [
                'constraints' => [
                    //new NotBlank(),
                    //new Length(['min' => 3]),
                ],
                'label' => 'Prix minimum',
            ])
            ->add('max', TextType::class, [
                'constraints' => [
                    //new NotBlank(),
                    //new Length(['min' => 3]),
                ],
                'label' => 'Prix maximum',
            ])
            ->add('search', TextType::class, [
                'constraints' => [
                    //new NotBlank(),
                    //new Length(['min' => 3]),
                ],
                'label' => 'Recherche',
            ])
            // add submit button
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
            ])
        ;
    }
}

