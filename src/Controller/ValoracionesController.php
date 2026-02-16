<?php

namespace App\Controller;

use App\Entity\Peliculas;
use App\Entity\Valoraciones;
use App\Form\ValoracionFormType;
use App\Repository\PeliculasRepository;
use App\Repository\ValoracionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/valoraciones')]
class ValoracionesController extends AbstractController
{
    #[Route('/crear/{peliculaId}', name: 'valoraciones_crear', methods: ['GET', 'POST'])]
    public function crear(
        int $peliculaId,
        PeliculasRepository $peliculasRepository,
        ValoracionesRepository $valoracionesRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $pelicula = $peliculasRepository->find($peliculaId);
        if (!$pelicula) {
            throw $this->createNotFoundException('Película no encontrada');
        }

        $valoracionExistente = $valoracionesRepository->findOneBy([
            'pelicula' => $pelicula,
            'usuario' => $this->getUser()
        ]);

        if ($valoracionExistente) {
            $this->addFlash('warning', 'Ya has valorado esta película. Puedes editar tu valoración.');
            return $this->redirectToRoute('valoraciones_editar', ['id' => $valoracionExistente->getId()]);
        }

        $valoracion = new Valoraciones();
        $valoracion->setPelicula($pelicula);
        $valoracion->setUsuario($this->getUser());

        $form = $this->createForm(ValoracionFormType::class, $valoracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($valoracion);
            $entityManager->flush();

            // Actualizar estadísticas
            $this->actualizarEstadisticasPelicula($pelicula, $entityManager);

            $this->addFlash('success', '¡Valoración guardada exitosamente!');
            return $this->redirectToRoute('peliculas_show', ['id' => $pelicula->getId()]);
        }

        return $this->render('valoraciones/crear.html.twig', [
            'form' => $form,
            'pelicula' => $pelicula,
        ]);
    }

    #[Route('/editar/{id}', name: 'valoraciones_editar', methods: ['GET', 'POST'])]
    public function editar(
        Valoraciones $valoracion,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($valoracion->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes editar esta valoración');
        }

        $form = $this->createForm(ValoracionFormType::class, $valoracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $valoracion->setActualizadoEn(new \DateTimeImmutable());
            $entityManager->flush();

            // Actualizar estadísticas
            $this->actualizarEstadisticasPelicula($valoracion->getPelicula(), $entityManager);

            $this->addFlash('success', '¡Valoración actualizada exitosamente!');
            return $this->redirectToRoute('peliculas_show', ['id' => $valoracion->getPelicula()->getId()]);
        }

        return $this->render('valoraciones/editar.html.twig', [
            'form' => $form,
            'valoracion' => $valoracion,
        ]);
    }

    #[Route('/eliminar/{id}', name: 'valoraciones_eliminar', methods: ['POST'])]
    public function eliminar(
        Valoraciones $valoracion,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($valoracion->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes eliminar esta valoración');
        }

        if ($this->isCsrfTokenValid('delete' . $valoracion->getId(), $request->request->get('_token'))) {
            $peliculaId = $valoracion->getPelicula()->getId();
            $pelicula = $valoracion->getPelicula();

            $entityManager->remove($valoracion);
            $entityManager->flush();

            // Actualizar estadísticas
            $this->actualizarEstadisticasPelicula($pelicula, $entityManager);

            $this->addFlash('success', 'Valoración eliminada correctamente');
            return $this->redirectToRoute('peliculas_show', ['id' => $peliculaId]);
        }

        $this->addFlash('error', 'Error al eliminar la valoración');
        return $this->redirectToRoute('peliculas_show', ['id' => $valoracion->getPelicula()->getId()]);
    }

    #[Route('/mis-valoraciones', name: 'valoraciones_mis_valoraciones', methods: ['GET'])]
    public function misValoraciones(ValoracionesRepository $valoracionesRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $valoraciones = $valoracionesRepository->findBy(
            ['usuario' => $this->getUser()],
            ['creadoEn' => 'DESC']
        );

        return $this->render('valoraciones/mis_valoraciones.html.twig', [
            'valoraciones' => $valoraciones,
        ]);
    }

    /**
     * Actualizar contadorRating y promedioRating de una película
     */
    private function actualizarEstadisticasPelicula(Peliculas $pelicula, EntityManagerInterface $entityManager): void
    {
        $connection = $entityManager->getConnection();
        $peliculaId = $pelicula->getId();

        // Tengo que usar wl SQL porque de otra manera no se me actualizan las películas sino
        $sql = "
        UPDATE peliculas p
        SET
            p.contador_rating = (
                SELECT COUNT(*)
                FROM valoraciones v
                WHERE v.id_pelicula = :peliculaId),
            p.promedio_rating = (
                SELECT COALESCE(AVG(CAST(v.puntuacion AS DECIMAL(10,2))), 0)
                FROM valoraciones v
                WHERE v.id_pelicula = :peliculaId)
                WHERE p.id = :peliculaId
    ";

        $connection->executeStatement($sql, ['peliculaId' => $peliculaId]);
    }
}
