<?php

namespace App\Controller;

use App\Entity\Peliculas;
use App\Form\PeliculaFormType;
use App\Repository\PeliculasRepository;
use App\Repository\UsuariosRepository;
use App\Repository\ValoracionesRepository;
use App\Repository\RankingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    /**
     * Dashboard principal del administrador
     */
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        PeliculasRepository $peliculasRepository,
        UsuariosRepository $usuariosRepository,
        ValoracionesRepository $valoracionesRepository,
        RankingsRepository $rankingsRepository
    ): Response {
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
     * Gestión de películas (CRUD)
     */
    #[Route('/peliculas', name: 'admin_peliculas')]
    public function peliculas(PeliculasRepository $peliculasRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $peliculas = $peliculasRepository->findAll();

        return $this->render('admin/peliculas.html.twig', [
            'peliculas' => $peliculas,
        ]);
    }

    /**
     * Crear nueva película manualmente
     */
    #[Route('/peliculas/nueva', name: 'admin_peliculas_nueva', methods: ['GET', 'POST'])]
    public function nuevaPelicula(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $pelicula = new Peliculas();
        $form = $this->createForm(PeliculaFormType::class, $pelicula);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pelicula->setGhibliId('manual-' . uniqid());
            $entityManager->persist($pelicula);
            $entityManager->flush();

            $this->addFlash('success', 'Película creada exitosamente');
            return $this->redirectToRoute('admin_peliculas');
        }

        return $this->render('admin/peliculas_form.html.twig', [
            'form' => $form,
            'pelicula' => null,
        ]);
    }

    /**
     * Editar película existente
     */
    #[Route('/peliculas/{id}/editar', name: 'admin_peliculas_editar', methods: ['GET', 'POST'])]
    public function editarPelicula(
        Peliculas $pelicula,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(PeliculaFormType::class, $pelicula);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pelicula->setActualizadoEn(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Película actualizada exitosamente');
            return $this->redirectToRoute('admin_peliculas');
        }

        return $this->render('admin/peliculas_form.html.twig', [
            'form' => $form,
            'pelicula' => $pelicula,
        ]);
    }

    /**
     * Eliminar película
     */
    #[Route('/peliculas/{id}/eliminar', name: 'admin_peliculas_eliminar', methods: ['POST'])]
    public function eliminarPelicula(
        Peliculas $pelicula,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete-pelicula-' . $pelicula->getId(), $request->request->get('_token'))) {
            $entityManager->remove($pelicula);
            $entityManager->flush();
            $this->addFlash('success', 'Película eliminada correctamente');
        }

        return $this->redirectToRoute('admin_peliculas');
    }

    /**
     * Estadísticas del sistema
     */
    #[Route('/estadisticas', name: 'admin_estadisticas')]
    public function estadisticas(
        PeliculasRepository $peliculasRepository,
        ValoracionesRepository $valoracionesRepository,
        UsuariosRepository $usuariosRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Top 10 películas más valoradas
        $topPeliculas = $peliculasRepository->findBy(
            [],
            ['contadorRating' => 'DESC'],
            10
        );

        // Top 10 películas mejor puntuadas (con al menos 3 valoraciones)
        $mejorPuntuadas = $peliculasRepository->createQueryBuilder('p')
            ->where('p.contadorRating >= 3')
            ->orderBy('p.promedioRating', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Usuarios más activos (que más han valorado)
        $usuariosActivos = $valoracionesRepository->createQueryBuilder('v')
            ->select('u.nombreUsuario, COUNT(v.id) as total_valoraciones')
            ->join('v.usuario', 'u')
            ->groupBy('u.id')
            ->orderBy('total_valoraciones', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Distribución de puntuaciones
        $distribucionPuntuaciones = $valoracionesRepository->createQueryBuilder('v')
            ->select('ROUND(v.puntuacion) as puntuacion, COUNT(v.id) as cantidad')
            ->groupBy('puntuacion')
            ->orderBy('puntuacion', 'ASC')
            ->getQuery()
            ->getResult();

        // Películas sin valoraciones
        $sinValoraciones = $peliculasRepository->createQueryBuilder('p')
            ->where('p.contadorRating = 0')
            ->getQuery()
            ->getResult();

        return $this->render('admin/estadisticas.html.twig', [
            'top_peliculas' => $topPeliculas,
            'mejor_puntuadas' => $mejorPuntuadas,
            'usuarios_activos' => $usuariosActivos,
            'distribucion_puntuaciones' => $distribucionPuntuaciones,
            'sin_valoraciones' => $sinValoraciones,
        ]);
    }

    /**
     * Gestión de usuarios
     */
    #[Route('/usuarios', name: 'admin_usuarios')]
    public function usuarios(UsuariosRepository $usuariosRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $usuarios = $usuariosRepository->findAll();

        return $this->render('admin/usuarios.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }
}
