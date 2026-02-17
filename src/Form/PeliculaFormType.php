<?php

namespace App\Form;

use App\Entity\Peliculas;
use App\Entity\Categorias;  // 👈 AGREGAR ESTA LÍNEA
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PeliculaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categoria', EntityType::class, [
                'class' => Categorias::class,
                'choice_label' => 'nombre',
                'label' => 'Categoría',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(message: 'Selecciona una categoría')
                ],
            ])
            ->add('titulo', TextType::class, ['label' => 'Título',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Título de la película'
                ],
                'constraints' => [
                    new NotBlank(message: 'El título es obligatorio'),
                    new Length(max: 255, maxMessage: 'El título no puede tener más de {{ limit }} caracteres')
                ],
            ])
            ->add('director', TextType::class, [
                'label' => 'Director',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre del director'
                ],
                'constraints' => [
                    new NotBlank(message: 'El director es obligatorio'),
                ],
            ])
            ->add('productor', TextType::class, [
                'label' => 'Productor',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre del productor'
                ],
            ])
            ->add('anoLanzamiento', IntegerType::class, [
                'label' => 'Año de lanzamiento',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '2024'
                ],
                'constraints' => [
                    new Range(
                        min: 1900,
                        max: 2100,
                        notInRangeMessage: 'El año debe estar entre {{ min }} y {{ max }}'
                    )
                ],
            ])
            ->add('duracion', IntegerType::class, [
                'label' => 'Duración (minutos)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '120'
                ],
                'constraints' => [
                    new Positive(message: 'La duración debe ser un número positivo')
                ],
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Sinopsis de la película...'
                ],
            ])
            ->add('imagenUrl', UrlType::class, [
                'label' => 'URL de la imagen',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://ejemplo.com/imagen.jpg'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Peliculas::class,
        ]);
    }
}
