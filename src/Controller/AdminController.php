<?php

namespace App\Controller;

use App\Entity\Peliculas;
use App\Repository\PeliculasRepository;
use App\Repository\RankingsRepository;
use App\Repository\UsuariosRepository;
use App\Repository\ValoracionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Dashboard principal del administrador
     */
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(PeliculasRepository $peliculasRepository, UsuariosRepository $usuariosRepository, ValoracionesRepository $valoracionesRepository, RankingsRepository $rankingsRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Estadísticas básicas
        $totalPeliculas = $peliculasRepository->count([]);
        $totalUsuarios = $usuariosRepository->count([]);
        $totalValoraciones = $valoracionesRepository->count([]);
        $totalRankings = $rankingsRepository->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'total_peliculas' => $totalPeliculas,
            'total_usuarios' => $totalUsuarios,
            'total_valoraciones' => $totalValoraciones,
            'total_rankings' => $totalRankings,
        ]);
    }

    /**
     * Gestión de peliculas (un CRUD)
     */
    #[Route('peliculas', name: 'admin_peliculas')]
    public function peliculas(PeliculasRepository $peliculasRepository): Response{
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $peliculas = $peliculasRepository->findAll();
        return $this->render('admin/peliculas.html.twig', ['peliculas' => $peliculas]);
    }

    /**
     * Crear peli nueva de manera manual
     */
    #[Route('/peliculas/nueva', name: 'admin_peliculas_nueva', methods: ['GET', 'POST'])]
    public function nuevaPelicula(Request, $request, EntityManagerInterface $entityManager): Response{

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $pelicula = new Peliculas();
        $form = $this->createForm(PeliculaFormType::class, $pelicula);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pelicula->setGhibliId('manual-'.uniqid());
            $entityManager->persist($pelicula);
            $entityManager->flush();

            $this->addFlash('Exitoso', 'La pelicula ha sido creada exitosamente.');
            return $this->redirectToRoute('admin_peliculas');
        }

        return $this->render('admin/peliculas_form.html.twig', [
            'form' => $form,
            'pelicula' => null,
        ]);
    }
}
