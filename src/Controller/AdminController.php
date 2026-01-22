<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/peliculas', name: 'admin_peliculas')]
    public function peliculas(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/peliculas.html.twig', [
            'message' => 'Próximamente: Gestión de Películas'
        ]);
    }

    #[Route('/estadisticas', name: 'admin_estadisticas')]
    public function estadisticas(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/estadisticas.html.twig', [
            'message' => 'Próximamente: Estadísticas'
        ]);
    }
}
