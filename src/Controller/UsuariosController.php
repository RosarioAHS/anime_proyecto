<?php

namespace App\Controller;

use App\Entity\Usuarios;
use App\Enum\RolEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsuariosController extends AbstractController
{
    #[Route('/admin/panel', name: 'admin_panel')]
    public function adminPanel(): Response
    {
        /** @var Usuarios $usuario */
        $usuario = $this->getUser();

        // Verificar si es admin antes de mostrar el panel
        if (!$usuario->isAdmin()) {
            throw $this->createAccessDeniedException('No tienes permisos de administrador');
        }

        return $this->render('admin/panel.html.twig');
    }

    #[Route('/usuario/crear', name: 'crear_usuario')]
    public function crearUsuario(): Response
    {
        $usuario = new Usuarios();
        $usuario->setNombreUsuario('juan');
        $usuario->setCorreoElectronico('juan@example.com');

        // Asignar rol de usuario por defecto
        $usuario->setRol([RolEnum::USER]);

        // O añadir roles uno por uno
        $usuario->addRole(RolEnum::USER);

        // Si quieres que sea admin también
        $usuario->addRole(RolEnum::ADMIN);

        // Guardar en base de datos
        // $entityManager->persist($usuario);
        // $entityManager->flush();

        return new Response('Usuario creado');
    }
}
