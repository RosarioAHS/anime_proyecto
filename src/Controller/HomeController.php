<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // Si el usuario está autenticado, enviarlo a películas
        // Si no, enviarlo a login
        if ($this->getUser()) {
            return $this->redirectToRoute('peliculas_index');
        }

        return $this->redirectToRoute('app_login');
    }
}
