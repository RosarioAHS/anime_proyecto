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

    #[Route('/', name: 'peliculas_index', methods: ['GET'])]
    public function index(
        PeliculasRepository $peliculasRepository,
        Request $request
    ): Response {
        $busqueda = $request->query->get('busqueda', '');
        $categoriaId = $request->query->get('categoria', null);

        // Obtener categorías para el filtro
        $categorias = $this->entityManager
            ->getRepository(\App\Entity\Categorias::class)
            ->findAll();

        // Construir consulta con filtros
        $qb = $peliculasRepository->createQueryBuilder('p')
            ->orderBy('p.promedioRating', 'DESC');

        if (!empty($busqueda)) {
            $qb->andWhere('p.titulo LIKE :busqueda OR p.director LIKE :busqueda')
                ->setParameter('busqueda', '%' . $busqueda . '%');
        }

        if (!empty($categoriaId)) {
            $qb->andWhere('p.categoria = :categoriaId')
                ->setParameter('categoriaId', $categoriaId);
        }

        $peliculas = $qb->getQuery()->getResult();

        return $this->render('peliculas/index.html.twig', [
            'peliculas' => $peliculas,
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'categoriaSeleccionada' => $categoriaId,
        ]);
    }

    #[Route('/{id}', name: 'peliculas_show', methods: ['GET'])]
    public function show(
        Peliculas $pelicula,
        ValoracionesRepository $valoracionesRepository
    ): Response {
        $valoraciones = $valoracionesRepository->findBy(
            ['pelicula' => $pelicula],
            ['creadoEn' => 'DESC']
        );

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

    #[Route('/admin/importar', name: 'peliculas_importar', methods: ['GET', 'POST'])]
    public function importarDesdeApi(PeliculasRepository $peliculasRepository): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            $this->addFlash('error', 'No tienes permisos para hacer esto');
            return $this->redirectToRoute('peliculas_index');
        }

        try {
            $response = $this->httpClient->request('GET', 'https://ghibliapi.vercel.app/films');
            $peliculasApi = $response->toArray();

            $importadas = 0;
            $actualizadas = 0;

            foreach ($peliculasApi as $peliculaData) {
                $pelicula = $peliculasRepository->findOneBy(['ghibliId' => $peliculaData['id']]);

                if (!$pelicula) {
                    $pelicula = new Peliculas();
                    $pelicula->setGhibliId($peliculaData['id']);
                    $importadas++;
                } else {
                    $actualizadas++;
                }

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
            $this->addFlash('error', 'Error al importar películas: ' . $e->getMessage());
        }

        return $this->redirectToRoute('peliculas_index');
    }
}
