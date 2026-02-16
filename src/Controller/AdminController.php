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
        // Verifico que sea admin
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            $this->addFlash('error', 'No tienes permisos para acceder aquí');
            return $this->redirectToRoute('peliculas_index');
        }

        // Cuento totales de forma simple
        $totalPeliculas = count($peliculasRepository->findAll());
        $totalUsuarios = count($usuariosRepository->findAll());
        $totalValoraciones = count($valoracionesRepository->findAll());
        $totalRankings = count($rankingsRepository->findAll());

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
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            $this->addFlash('error', 'No tienes permisos');
            return $this->redirectToRoute('peliculas_index');
        }

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
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

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
        // Top 10 películas más valoradas
        // Lo hago simple: traigo todas y las ordeno con PHP
        $todasPeliculas = $peliculasRepository->findAll();
        usort($todasPeliculas, function($a, $b) {
            return $b->getContadorRating() <=> $a->getContadorRating();
        });
        $topPeliculas = array_slice($todasPeliculas, 0, 10);

        // Top 10 mejor puntuadas (con al menos 3 valoraciones)
        $mejorPuntuadas = [];
        foreach ($todasPeliculas as $pelicula) {
            if ($pelicula->getContadorRating() >= 3) {
                $mejorPuntuadas[] = $pelicula;
            }
        }
        usort($mejorPuntuadas, function($a, $b) {
            return $b->getPromedioRating() <=> $a->getPromedioRating();
        });
        $mejorPuntuadas = array_slice($mejorPuntuadas, 0, 10);

        // Usuarios más activos
        // Lo hago contando manualmente
        $todosUsuarios = $usuariosRepository->findAll();
        $usuariosActivos = [];

        foreach ($todosUsuarios as $usuario) {
            $cantidadValoraciones = count($usuario->getValoraciones());
            if ($cantidadValoraciones > 0) {
                $usuariosActivos[] = [
                    'nombreUsuario' => $usuario->getNombreUsuario(),
                    'total_valoraciones' => $cantidadValoraciones
                ];
            }
        }

        // Ordeno por cantidad de valoraciones
        usort($usuariosActivos, function($a, $b) {
            return $b['total_valoraciones'] <=> $a['total_valoraciones'];
        });
        $usuariosActivos = array_slice($usuariosActivos, 0, 10);

        // Distribución de puntuaciones
        // Traigo todas las valoraciones y las cuento con PHP
        $todasValoraciones = $valoracionesRepository->findAll();

        // Array para contar cada puntuación
        $distribucionPuntuaciones = [
            ['puntuacion' => '1', 'cantidad' => 0],
            ['puntuacion' => '2', 'cantidad' => 0],
            ['puntuacion' => '3', 'cantidad' => 0],
            ['puntuacion' => '4', 'cantidad' => 0],
            ['puntuacion' => '5', 'cantidad' => 0],
        ];

        // Cuento las valoraciones
        foreach ($todasValoraciones as $valoracion) {
            $puntuacion = (int)round($valoracion->getPuntuacion());
            if ($puntuacion >= 1 && $puntuacion <= 5) {
                $distribucionPuntuaciones[$puntuacion - 1]['cantidad']++;
            }
        }

        // Películas sin valoraciones
        $sinValoraciones = [];
        foreach ($todasPeliculas as $pelicula) {
            if ($pelicula->getContadorRating() == 0) {
                $sinValoraciones[] = $pelicula;
            }
        }

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
    public function usuarios(UsuariosRepository $usuariosRepository): Response {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $usuarios = $usuariosRepository->findAll();

        return $this->render('admin/usuarios.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }
}
