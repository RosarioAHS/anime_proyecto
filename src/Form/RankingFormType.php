<?php

namespace App\Form;

use App\Entity\Rankings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RankingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombreRanking', TextType::class, [
                'label' => 'Nombre del ranking',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Mis películas favoritas de Miyazaki'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Por favor ingresa un nombre para el ranking'
                    ),
                    new Length(
                        min: 3,
                        max: 100,
                        minMessage: 'El nombre debe tener al menos {{ limit }} caracteres',
                        maxMessage: 'El nombre no puede tener más de {{ limit }} caracteres'
                    ),
                ],
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción (opcional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Describe brevemente este ranking...'
                ],
            ])
            ->add('publico', CheckboxType::class, [
                'label' => 'Hacer público (otros usuarios podrán ver este ranking)',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rankings::class,
        ]);
    }
}
