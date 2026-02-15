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
    // Necesito estos servicios para trabajar con HTTP y Base de Datos
    // Los aprendí en la documentación de Symfony
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
     * Esta función muestra todas las películas en el catálogo
     *
     */
    #[Route('/', name: 'peliculas_index', methods: ['GET'])]
    public function index(PeliculasRepository $peliculasRepository): Response
    {
        // Traigo todas las películas ordenadas por rating (las mejores primero)
        // Al principio no sabía cómo ordenar, investigué en la documentación de Doctrine
        $peliculas = $peliculasRepository->findBy([], ['promedioRating' => 'DESC']);

        // Renderizo la plantilla Twig y le paso las películas
        return $this->render('peliculas/index.html.twig', [
            'peliculas' => $peliculas,
        ]);
    }

    /**
     * Ver el detalle de una película específica
     * Ruta: /peliculas/{id}
     */
    #[Route('/{id}', name: 'peliculas_show', methods: ['GET'])]
    public function show(
        Peliculas $pelicula,
        ValoracionesRepository $valoracionesRepository
    ): Response {
        // Obtengo todas las valoraciones de esta película
        // Las ordeno por fecha (las más recientes primero)
        $valoraciones = $valoracionesRepository->findBy(
            ['pelicula' => $pelicula],
            ['creadoEn' => 'DESC']
        );

        // Verifico si el usuario actual ya valoró esta película
        // Esto me costó entenderlo al principio, tuve que buscar en Stack Overflow
        $valoracionUsuario = null;
        if ($this->getUser()) {
            $valoracionUsuario = $valoracionesRepository->findOneBy([
                'pelicula' => $pelicula,
                'usuario' => $this->getUser()
            ]);
        }

        // Paso todo a la vista
        return $this->render('peliculas/show.html.twig', [
            'pelicula' => $pelicula,
            'valoraciones' => $valoraciones,
            'valoracion_usuario' => $valoracionUsuario,
        ]);
    }

    /**
     * Importar películas desde la API de Studio Ghibli
     * Solo el administrador puede hacer esto
     *
     */
    #[Route('/admin/importar', name: 'peliculas_importar', methods: ['GET', 'POST'])]
    public function importarDesdeApi(PeliculasRepository $peliculasRepository): Response
    {
        // Verifico que sea administrador
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            $this->addFlash('error', 'No tienes permisos para hacer esto');
            return $this->redirectToRoute('peliculas_index');
        }

        try {
            // Llamo a la API de Studio Ghibli
            $response = $this->httpClient->request(
                'GET',
                'https://ghibliapi.vercel.app/films'
            );

            // Convierto la respuesta JSON a un array de PHP
            $peliculasApi = $response->toArray();

            // Contadores para saber cuántas películas importé
            $importadas = 0;
            $actualizadas = 0;

            // Recorro cada película de la API
            foreach ($peliculasApi as $peliculaData) {
                // Busco si ya existe en mi base de datos por el ID de Ghibli
                $pelicula = $peliculasRepository->findOneBy(['ghibliId' => $peliculaData['id']]);

                if (!$pelicula) {
                    // Si no existe, creo una nueva
                    $pelicula = new Peliculas();
                    $pelicula->setGhibliId($peliculaData['id']);
                    $importadas++;
                } else {
                    // Si ya existe, la actualizo
                    $actualizadas++;
                }

                // Relleno todos los campos con los datos de la API
                // Uso el operador ?? para poner valores por defecto si no existen
                $pelicula->setTitulo($peliculaData['title'] ?? 'Sin título');
                $pelicula->setDirector($peliculaData['director'] ?? 'Desconocido');
                $pelicula->setProductor($peliculaData['producer'] ?? 'Desconocido');
                $pelicula->setAnoLanzamiento($peliculaData['release_date'] ?? null);
                $pelicula->setDuracion($peliculaData['running_time'] ?? 0);
                $pelicula->setDescripcion($peliculaData['description'] ?? '');
                $pelicula->setImagenUrl($peliculaData['image'] ?? '');

                // Guardo la película en la base de datos
                $this->entityManager->persist($pelicula);
            }

            // Ejecuto todas las inserciones/actualizaciones en la base de datos
            $this->entityManager->flush();

            // Muestro un mensaje de éxito al usuario
            $this->addFlash('success',
                "¡Importación exitosa! {$importadas} películas nuevas importadas, {$actualizadas} actualizadas."
            );

        } catch (\Exception $e) {
            // Si algo sale mal, muestro el error
            $this->addFlash('error',
                'Error al importar películas: ' . $e->getMessage()
            );
        }

        // Redirigo al catálogo de películas
        return $this->redirectToRoute('peliculas_index');
    }
}
