<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for Category entity.
 */
class CategoryType extends AbstractType
{
    /**
     * Build the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a category name.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Category name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Category name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter category name',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Description cannot be longer than {{ limit }} characters.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Optional description (max 1000 characters)',
                ],
            ]);
    }

    /**
     * Configure form options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'csrf_protection' => true,
        ]);
    }
} 