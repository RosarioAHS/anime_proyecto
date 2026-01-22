<?php

namespace App\Form;

use App\Entity\Valoraciones;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ValoracionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('puntuacion', ChoiceType::class, [
                'label' => 'Puntuación',
                'choices' => [
                    '⭐ 1 - Muy mala' => '1.00',
                    '⭐⭐ 2 - Mala' => '2.00',
                    '⭐⭐⭐ 3 - Regular' => '3.00',
                    '⭐⭐⭐⭐ 4 - Buena' => '4.00',
                    '⭐⭐⭐⭐⭐ 5 - Excelente' => '5.00',
                ],
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Por favor selecciona una puntuación'
                    ),
                    new Range(
                        min: 1,
                        max: 5,
                        notInRangeMessage: 'La puntuación debe estar entre {{ min }} y {{ max }}'
                    ),
                ],
            ])
            ->add('comentario', TextareaType::class, [
                'label' => 'Comentario (opcional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Cuéntanos qué te pareció esta película...'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Valoraciones::class,
        ]);
    }
}
