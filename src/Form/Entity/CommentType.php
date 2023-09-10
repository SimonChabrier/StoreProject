<?php

namespace App\Form\Entity;

use App\Entity\Comment;
use App\Entity\Product;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('author', TextType::class, [
                'label' => 'Auteur',
                'disabled' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'disabled' => false,
            ])
            ->add('product', EntityType::class, [
                'label' => 'Produit associé',
                'class' => Product::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('text', CKEditorType::class, [
                'label' => 'Texte du commentaire',
                'disabled' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
