<?php

namespace App\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('min', IntegerType::class, [
                'label' => 'Prix minimum',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('max', IntegerType::class, [
                'label' => 'Prix maximum',
                'required' => false,
                'constraints' => [   
                    // dans ce callback, on peut récupérer les données du formulaire pour les comparer entre elles et lever une violation de contrainte si nécessaire
                    new Callback(function ($value, ExecutionContextInterface $context) {
                            // dd($value); contient la valeur du champ max
                            // la méthode getRoot() de l'objet ExecutionContextInterface pour récupére l'objet formulaire parent. 
                            // Cela permet de récupérer les données de min et de max à chaque fois que le formulaire est soumis, 
                            // de sorte que si l'utilisateur modifie les valeurs de min et de max pour des valeurs valides, 
                            // la violation de contrainte ne sera plus levée.
                        $form = $context->getRoot();
                        $min = $form->get('min')->getData();
                        $max = $form->get('max')->getData();

                        // Si le prix maximum est inférieur au prix minimum, on leve une violation de contrainte 
                        if ($max <= $min && $max !== null && $min !== null) {
                            $context->buildViolation('Le prix maximum doit être supérieur au prix minimum')
                                ->atPath('max')
                                ->addViolation();
                        }
                        // Si le prix maximum est renseigné alors le prix minimum est obligatoire
                        if(isset($max) && $min === null) {
                            $context->buildViolation('Le prix minimum est requis si le prix maximum est renseigné')
                                ->atPath('min')
                                ->addViolation();
                        }
                        // Si le prix minimum est renseigné alors le prix maximum peut être renseigné on le signale à l'utilisateur
                        if (isset($min) && $max === null) {
                            $context->buildViolation('Vous pouvez renseigner un prix maximum pour une recherche plus précise !')
                                ->atPath('max')
                                ->addViolation();
                        // Si le prix minimum et le prix maximum ne sont pas renseignés, on leve une violation de contrainte
                        } elseif ($min === null && $max === null) {
                            $context->buildViolation('Vous devez renseigner un prix minimum ou maximum pour une recherche')
                                ->atPath('min')
                                ->addViolation();
                        }
                    }),
                ],
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
            ]);

        $formModifier = function (FormEvent $event) {
            $form = $event->getForm();

            $min = $form->get('min')->getData();
            $max = $form->get('max');

            if ($min !== null) {
                $max->isRequired(true);
            } else {
                $max->isRequired(false);
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $formModifier);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $formModifier);       
    }
}

// public function buildForm(FormBuilderInterface $builder, array $options)
//     {
//     $builder

//     ->add('min', IntegerType::class, [
//         'label' => 'Prix minimum',
//     ])
//     ->add('max', IntegerType::class, [
//         'label' => 'Prix maximum',
//         'constraints' => [
//             new Callback(function ($object, ExecutionContextInterface $context, $payload) use ($builder) {
//                 $min = $builder->get('min')->getData();
//                 $max = $builder->get('max')->getData();

//                 if ($max <= $min) {
//                     $context->buildViolation('Le prix maximum doit être supérieur au prix minimum')
//                         ->atPath('max')
//                         ->addViolation();
//                 }
//             }),
//         ],
//     ])
//     ->add ('submit', SubmitType::class, [
//         'label' => 'Rechercher',
//     ]);
// }
//}

