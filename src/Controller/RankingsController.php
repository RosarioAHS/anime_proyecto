<?php

namespace App\Controller;

use App\Entity\Rankings;
use App\Entity\RankingPeliculas;
use App\Form\RankingFormType;
use App\Repository\PeliculasRepository;
use App\Repository\RankingsRepository;
use App\Repository\RankingPeliculasRepository;
use App\Repository\ValoracionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rankings')]
class RankingsController extends AbstractController
{
    /**
     * Listado de mis rankings
     */
    #[Route('/', name: 'rankings_index', methods: ['GET'])]
    public function index(RankingsRepository $rankingsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $misRankings = $rankingsRepository->findBy(
            ['usuario' => $this->getUser()],
            ['creadoEn' => 'DESC']
        );

        return $this->render('rankings/index.html.twig', [
            'rankings' => $misRankings,
        ]);
    }

    /**
     * Ver rankings públicos de otros usuarios
     */
    #[Route('/publicos', name: 'rankings_publicos', methods: ['GET'])]
    public function publicos(RankingsRepository $rankingsRepository): Response
    {
        $rankingsPublicos = $rankingsRepository->findBy(
            ['publico' => true],
            ['creadoEn' => 'DESC']
        );

        return $this->render('rankings/publicos.html.twig', [
            'rankings' => $rankingsPublicos,
        ]);
    }

    /**
     * Crear nuevo ranking
     */
    #[Route('/nuevo', name: 'rankings_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $ranking = new Rankings();
        $ranking->setUsuario($this->getUser());

        $form = $this->createForm(RankingFormType::class, $ranking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ranking);
            $entityManager->flush();

            $this->addFlash('success', '¡Ranking creado exitosamente! Ahora puedes añadir películas.');
            return $this->redirectToRoute('rankings_gestionar', ['id' => $ranking->getId()]);
        }

        return $this->render('rankings/nuevo.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Ver detalle de un ranking
     */
    #[Route('/{id}', name: 'rankings_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Rankings $ranking): Response
    {
        // Si el ranking no es público, solo el dueño puede verlo
        if (!$ranking->isPublico() && $ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Este ranking es privado');
        }

        return $this->render('rankings/show.html.twig', [
            'ranking' => $ranking,
        ]);
    }

    /**
     * Editar un ranking
     */
    #[Route('/{id}/editar', name: 'rankings_editar', methods: ['GET', 'POST'])]
    public function editar(
        Rankings $ranking,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes editar este ranking');
        }

        $form = $this->createForm(RankingFormType::class, $ranking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ranking->setActualizadoEn(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Ranking actualizado correctamente');
            return $this->redirectToRoute('rankings_show', ['id' => $ranking->getId()]);
        }

        return $this->render('rankings/editar.html.twig', [
            'form' => $form,
            'ranking' => $ranking,
        ]);
    }

    /**
     * Gestionar películas de un ranking (añadir, ordenar, eliminar)
     */
    #[Route('/{id}/gestionar', name: 'rankings_gestionar', methods: ['GET', 'POST'])]
    public function gestionar(
        Rankings $ranking,
        Request $request,
        PeliculasRepository $peliculasRepository,
        ValoracionesRepository $valoracionesRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes gestionar este ranking');
        }

        // Obtener películas valoradas por el usuario
        $valoraciones = $valoracionesRepository->findBy(['usuario' => $this->getUser()]);
        $peliculasValoradas = array_map(fn($v) => $v->getPelicula(), $valoraciones);

        // Películas ya en el ranking
        $peliculasEnRanking = [];
        foreach ($ranking->getRankingPeliculas() as $rp) {
            $peliculasEnRanking[] = $rp->getPelicula()->getId();
        }

        return $this->render('rankings/gestionar.html.twig', [
            'ranking' => $ranking,
            'peliculas_valoradas' => $peliculasValoradas,
            'peliculas_en_ranking' => $peliculasEnRanking,
        ]);
    }

    /**
     * Añadir película a un ranking
     */
    #[Route('/{id}/anadir-pelicula/{peliculaId}', name: 'rankings_anadir_pelicula', methods: ['POST'])]
    public function anadirPelicula(
        Rankings $ranking,
        int $peliculaId,
        PeliculasRepository $peliculasRepository,
        RankingPeliculasRepository $rankingPeliculasRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Verificar CSRF
        if (!$this->isCsrfTokenValid('add-movie-' . $ranking->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido');
            return $this->redirectToRoute('rankings_gestionar', ['id' => $ranking->getId()]);
        }

        $pelicula = $peliculasRepository->find($peliculaId);
        if (!$pelicula) {
            throw $this->createNotFoundException('Película no encontrada');
        }

        // Verificar si ya existe
        $existe = $rankingPeliculasRepository->findOneBy([
            'ranking' => $ranking,
            'pelicula' => $pelicula
        ]);

        if ($existe) {
            $this->addFlash('warning', 'Esta película ya está en el ranking');
            return $this->redirectToRoute('rankings_gestionar', ['id' => $ranking->getId()]);
        }

        // Calcular siguiente posición
        $ultimaPosicion = 0;
        foreach ($ranking->getRankingPeliculas() as $rp) {
            if ($rp->getPosicion() > $ultimaPosicion) {
                $ultimaPosicion = $rp->getPosicion();
            }
        }

        // Crear relación
        $rankingPelicula = new RankingPeliculas();
        $rankingPelicula->setRanking($ranking);
        $rankingPelicula->setPelicula($pelicula);
        $rankingPelicula->setPosicion($ultimaPosicion + 1);

        $entityManager->persist($rankingPelicula);
        $entityManager->flush();

        $this->addFlash('success', 'Película añadida al ranking');
        return $this->redirectToRoute('rankings_gestionar', ['id' => $ranking->getId()]);
    }

    /**
     * Eliminar película de un ranking
     */
    #[Route('/{id}/eliminar-pelicula/{rankingPeliculaId}', name: 'rankings_eliminar_pelicula', methods: ['POST'])]
    public function eliminarPelicula(
        Rankings $ranking,
        int $rankingPeliculaId,
        RankingPeliculasRepository $rankingPeliculasRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Verificar CSRF
        if (!$this->isCsrfTokenValid('remove-movie-' . $rankingPeliculaId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido');
            return $this->redirectToRoute('rankings_gestionar', ['id' => $ranking->getId()]);
        }

        $rankingPelicula = $rankingPeliculasRepository->find($rankingPeliculaId);

        if ($rankingPelicula && $rankingPelicula->getRanking() === $ranking) {
            $entityManager->remove($rankingPelicula);
            $entityManager->flush();
            $this->addFlash('success', 'Película eliminada del ranking');
        }

        return $this->redirectToRoute('rankings_gestionar', ['id' => $ranking->getId()]);
    }

    /**
     * Cambiar posición de película en ranking
     */
    #[Route('/{id}/reordenar', name: 'rankings_reordenar', methods: ['POST'])]
    public function reordenar(
        Rankings $ranking,
        Request $request,
        RankingPeliculasRepository $rankingPeliculasRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $orden = json_decode($request->getContent(), true);

        if ($orden && isset($orden['orden'])) {
            foreach ($orden['orden'] as $posicion => $rankingPeliculaId) {
                $rankingPelicula = $rankingPeliculasRepository->find($rankingPeliculaId);
                if ($rankingPelicula && $rankingPelicula->getRanking() === $ranking) {
                    $rankingPelicula->setPosicion($posicion + 1);
                }
            }
            $entityManager->flush();
            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false], 400);
    }

    /**
     * Eliminar ranking completo
     */
    #[Route('/{id}/eliminar', name: 'rankings_eliminar', methods: ['POST'])]
    public function eliminar(
        Rankings $ranking,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($ranking->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-' . $ranking->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ranking);
            $entityManager->flush();
            $this->addFlash('success', 'Ranking eliminado correctamente');
        }

        return $this->redirectToRoute('rankings_index');
    }
}
