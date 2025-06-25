<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\Issue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for Issue entity.
 */
class IssueType extends AbstractType
{
    /**
     * Build the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter issue title',
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Describe the issue in detail',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Issue::getStatusChoices(),
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => Issue::getPriorityChoices(),
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    /**
     * Configure form options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Issue::class,
            'csrf_protection' => true,
        ]);
    }
} 