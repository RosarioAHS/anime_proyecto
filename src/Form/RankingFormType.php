<?php

namespace App\Form;

use App\Entity\Categorias;
use App\Entity\Rankings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RankingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categoria', EntityType::class, [
                'class' => Categorias::class,
                'choice_label' => 'nombre',
                'label' => 'Categoría',
                'placeholder' => 'Selecciona una categoría',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(message: 'Selecciona una categoría')
                ],
                // Si estamos editando, deshabilitar el campo categoría
                'disabled' => $options['editar'],
            ])
            ->add('nombreRanking', TextType::class, [
                'label' => 'Nombre del ranking',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Mis películas favoritas de acción'
                ],
                'constraints' => [
                    new NotBlank(message: 'El nombre es obligatorio')
                ],
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción (opcional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Describe tu ranking...'
                ],
            ])
            ->add('publico', CheckboxType::class, [
                'label' => 'Hacer público este ranking',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rankings::class,
            'editar' => false, // 👈 opción para saber si estamos editando
        ]);
    }
}
