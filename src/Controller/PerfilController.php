<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/perfil')]
class PerfilController extends AbstractController
{
    #[Route('/', name: 'perfil_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('perfil/index.html.twig', [
            'message' => 'Próximamente: Mi Perfil'
        ]);
    }
}
