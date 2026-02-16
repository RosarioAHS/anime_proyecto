<?php

namespace App\Controller;

use App\Entity\Categorias;
use App\Form\CategoriaFormType;
use App\Repository\CategoriasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categorias')]
class CategoriasController extends AbstractController
{
    /**
     * Listar todas las categorías
     */
    #[Route('/', name: 'admin_categorias_index')]
    public function index(CategoriasRepository $categoriasRepository): Response
    {
        // Verifico que sea admin
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $categorias = $categoriasRepository->findAll();

        return $this->render('admin/categorias/index.html.twig', [
            'categorias' => $categorias,
        ]);
    }

    /**
     * Crear nueva categoría
     */
    #[Route('/nueva', name: 'admin_categorias_nueva')]
    public function nueva(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $categoria = new Categorias();
        $form = $this->createForm(CategoriaFormType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoria);
            $entityManager->flush();

            $this->addFlash('success', 'Categoría creada exitosamente');
            return $this->redirectToRoute('admin_categorias_index');
        }

        return $this->render('admin/categorias/form.html.twig', [
            'form' => $form,
            'categoria' => null,
        ]);
    }

    /**
     * Editar categoría
     */
    #[Route('/{id}/editar', name: 'admin_categorias_editar')]
    public function editar(
        Categorias $categoria,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(CategoriaFormType::class, $categoria);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoria->setActualizadoEn(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Categoría actualizada');
            return $this->redirectToRoute('admin_categorias_index');
        }

        return $this->render('admin/categorias/form.html.twig', [
            'form' => $form,
            'categoria' => $categoria,
        ]);
    }

    /**
     * Eliminar categoría
     */
    #[Route('/{id}/eliminar', name: 'admin_categorias_eliminar', methods: ['POST'])]
    public function eliminar(
        Categorias $categoria,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        // Verifico que no tenga películas
        if (count($categoria->getPeliculas()) > 0) {
            $this->addFlash('error', 'No se puede eliminar una categoría que tiene películas');
            return $this->redirectToRoute('admin_categorias_index');
        }

        if ($this->isCsrfTokenValid('delete-categoria-' . $categoria->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categoria);
            $entityManager->flush();
            $this->addFlash('success', 'Categoría eliminada');
        }

        return $this->redirectToRoute('admin_categorias_index');
    }

    /**
     * Ver elementos de una categoría
     */
    #[Route('/{id}/elementos', name: 'admin_categorias_elementos')]
    public function elementos(Categorias $categoria): Response
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('admin/categorias/elementos.html.twig', [
            'categoria' => $categoria,
        ]);
    }
}
