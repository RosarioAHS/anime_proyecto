<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RankingPeliculasController extends AbstractController
{
    #[Route('/ranking-peliculas')]
    public function index(): Response
    {
        return $this->render('ranking_peliculas/index.html.twig');
    }
}
