<?php

namespace App\Form\Entity;

use App\Entity\Picture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType as ValidFileInputType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'image',
                'required' => false,
                'empty_data' => 'image du produit',
            ])
            ->add('alt', TextType::class, [
                'label' => 'Description de l\'image',
                'required' => false,
                'empty_data' => 'description de l\'image',
            ])
            // non mapped, juste pour créer un input récupérer le fichier dans le controller
            // https://symfonycasts.com/screencast/symfony-uploads/upload-in-form
            ->add('file', ValidFileInputType::class, [
                'label' => 'Image (JPG file)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '3M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/webp',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être une image au format JPG, PNG ou WEBP.',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
