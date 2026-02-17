<?php

namespace App\Form;

use App\Entity\Usuarios;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class RegistroFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombreUsuario', TextType::class, [
                'label' => 'Nombre de usuario',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Elige un nombre de usuario'],
                'constraints' => [
                    new NotBlank(message: 'Por favor ingresa un nombre de usuario'),
                    new Length(
                        min: 3,
                        max: 50,
                        minMessage: 'El nombre debe tener al menos {{ limit }} caracteres',
                        maxMessage: 'El nombre no puede tener más de {{ limit }} caracteres'),],])

            ->add('correoElectronico', EmailType::class, ['label' => 'Correo electrónico', 'attr' => ['class' => 'form-control', 'placeholder' => 'tu@email.com'],
                'constraints' => [
                    new NotBlank(message: 'Por favor ingresa tu correo electrónico'),
                    new Email(message: 'Por favor ingresa un correo válido'),],])

            ->add('plainPassword', RepeatedType::class, ['type' => PasswordType::class, 'mapped' => false,
                'first_options' => [
                    'label' => 'Contraseña',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Mínimo 6 caracteres'],
                ],
                'second_options' => [
                    'label' => 'Confirmar contraseña',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Repite la contraseña'
                    ],
                ],
                'invalid_message' => 'Las contraseñas deben coincidir',
                'constraints' => [
                    new NotBlank(message: 'Por favor ingresa una contraseña'),
                    new Length(min: 6, minMessage: 'Tu contraseña debe tener al menos {{ limit }} caracteres', max: 4096),],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,]);
    }
}
