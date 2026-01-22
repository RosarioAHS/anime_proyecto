<?php

namespace App\Controller;

use App\Entity\Peliculas;
use App\Repository\PeliculasRepository;
use App\Repository\ValoracionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/peliculas')]
class PeliculasController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    /**
     * Listado de todas las películas
     */
    #[Route('/', name: 'peliculas_index', methods: ['GET'])]
    public function index(PeliculasRepository $peliculasRepository): Response
    {
        // Obtener todas las películas ordenadas por rating
        $peliculas = $peliculasRepository->findBy([], ['promedioRating' => 'DESC']);

        return $this->render('peliculas/index.html.twig', [
            'peliculas' => $peliculas,
        ]);
    }

    /**
     * Ver detalle de una película
     */
    #[Route('/{id}', name: 'peliculas_show', methods: ['GET'])]
    public function show(
        Peliculas $pelicula,
        ValoracionesRepository $valoracionesRepository
    ): Response {
        // Obtener las valoraciones de esta película
        $valoraciones = $valoracionesRepository->findBy(
            ['pelicula' => $pelicula],
            ['creadoEn' => 'DESC']
        );

        // Verificar si el usuario actual ya valoró esta película
        $valoracionUsuario = null;
        if ($this->getUser()) {
            $valoracionUsuario = $valoracionesRepository->findOneBy([
                'pelicula' => $pelicula,
                'usuario' => $this->getUser()
            ]);
        }

        return $this->render('peliculas/show.html.twig', [
            'pelicula' => $pelicula,
            'valoraciones' => $valoraciones,
            'valoracion_usuario' => $valoracionUsuario,
        ]);
    }

    /**
     * Importar películas desde la API de Studio Ghibli
     * Solo accesible por administradores
     */
    #[Route('/admin/importar', name: 'peliculas_importar', methods: ['GET', 'POST'])]
    public function importarDesdeApi(PeliculasRepository $peliculasRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            // Llamar a la API de Studio Ghibli
            $response = $this->httpClient->request(
                'GET',
                'https://ghibliapi.vercel.app/films'
            );

            $peliculasApi = $response->toArray();
            $importadas = 0;
            $actualizadas = 0;

            foreach ($peliculasApi as $peliculaData) {
                // Buscar si ya existe por ghibli_id
                $pelicula = $peliculasRepository->findOneBy(['ghibliId' => $peliculaData['id']]);

                if (!$pelicula) {
                    // Crear nueva película
                    $pelicula = new Peliculas();
                    $pelicula->setGhibliId($peliculaData['id']);
                    $importadas++;
                } else {
                    $actualizadas++;
                }

                // Actualizar datos
                $pelicula->setTitulo($peliculaData['title'] ?? 'Sin título');
                $pelicula->setDirector($peliculaData['director'] ?? 'Desconocido');
                $pelicula->setProductor($peliculaData['producer'] ?? 'Desconocido');
                $pelicula->setAnoLanzamiento($peliculaData['release_date'] ?? null);
                $pelicula->setDuracion($peliculaData['running_time'] ?? 0);
                $pelicula->setDescripcion($peliculaData['description'] ?? '');
                $pelicula->setImagenUrl($peliculaData['image'] ?? '');

                $this->entityManager->persist($pelicula);
            }

            $this->entityManager->flush();

            $this->addFlash('success',
                "¡Importación exitosa! {$importadas} películas nuevas importadas, {$actualizadas} actualizadas."
            );

        } catch (\Exception $e) {
            $this->addFlash('error',
                'Error al importar películas: ' . $e->getMessage()
            );
        }

        return $this->redirectToRoute('peliculas_index');
    }
}
