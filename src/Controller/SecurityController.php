<?php

namespace App\Controller;

use App\Entity\Usuarios;
use App\Enum\RolEnum;
use App\Form\RegistroFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si ya está autenticado, redirigir a películas
        if ($this->getUser()) {
            return $this->redirectToRoute('peliculas_index');
        }

        // Obtener el error de login si existe
        $error = $authenticationUtils->getLastAuthenticationError();

        // Último nombre de usuario ingresado
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony maneja esto automáticamente
        throw new \LogicException('Este método puede estar vacío - será interceptado por la llave de logout en security.yaml');
    }

    #[Route('/registro', name: 'app_registro')]
    public function registro(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Si ya está autenticado, redirigir a películas
        if ($this->getUser()) {
            return $this->redirectToRoute('peliculas_index');
        }

        $usuario = new Usuarios();
        $form = $this->createForm(RegistroFormType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hashear la contraseña
            $hashedPassword = $passwordHasher->hashPassword(
                $usuario,
                $form->get('plainPassword')->getData()
            );
            $usuario->setContrasena($hashedPassword);

            // Asignar rol USER por defecto
            $usuario->setRol(RolEnum::USER);

            // Guardar usuario
            $entityManager->persist($usuario);
            $entityManager->flush();

            // Mensaje de éxito
            $this->addFlash('success', '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/registro.html.twig', [
            'registroForm' => $form,
        ]);
    }
}
