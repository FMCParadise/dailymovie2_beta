<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Posts;
use App\Form\CategoriesType;
use App\Form\DeleteCategoriesType;
use App\Form\PostsType;
use App\Repository;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use App\Services\Slugger;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class CategoriesController extends AbstractController
{
    #[Route('/categories/{slug}/{nPage?}', name: 'app_categories', requirements: ["nPage" => "\d*"])]
    public function index(Categories $categories, PostsRepository $postsRepository, Request $request): Response
    {
        $limit = 4; // post par page
        $nPage = $request->get('nPage', 1);
        $nPage = max($nPage, 1);
        $offset = ($nPage - 1) * $limit;

        $posts = $postsRepository->getPostsByCategories($categories);


        $posts = $postsRepository->getPostsByCategories($categories, $limit, $offset);
        // dd(count($posts));
        $totalPosts = $posts["total"]; // obtenir le nombre total de posts

        $totalPages = ceil($totalPosts / $limit); // calculate total pages

        // Générer la pagination
        $pagination = range(1, $totalPages);


        // dd($posts);
        return $this->render('categories/index.html.twig', [
            'posts' => $posts["data"],
            'nPage' => $nPage,
            'slug' => $categories->getSlug(),
            'pagination' => $pagination,

        ]);
    }

    #[Route("/gestions/add-categories", name: "app_add_categories")]
    public function add(Request                $request,
                        Slugger                $slugger,
                        Security               $security,
                        EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        //check autorisation
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('access_denied', "Vous n'avez pas l'autorisation pour ajouter des catégories");
            return $this->render('gestions/edit.html.twig', ['post' => []]);
        }

        $categories = new Categories();
        $form = $this->createForm(CategoriesType::class, $categories);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $slugger->checkSlugCategorie($form['slug']->getData());
            $categories->setSlug($slug);

            try {
                //update in db
                $em->persist($categories);
                $em->flush();
                //redirect to
                return $this->redirectToRoute('app_gestions');

            } catch (UniqueConstraintViolationException $e) {
                $form['name']->addError(new FormError('Le nom de cette catégorie existe déja : merci de choisir un autre.'));
            }
        }


        return $this->render('gestions/add-categories.html.twig', [
            "title" => "Ajouter Une Catégorie",
            'form' => $form->createView(),
        ]);
    }

    #[Route("/gestions/delete-categories", name: "app_delete_categories")]
    public function delete(Request              $request,
                           CategoriesRepository $categoriesRepository,
                           PostsRepository      $postsRepository,
                           Security             $security, EntityManagerInterface $em): Response
    {

        $user = $security->getUser();

        //check autorisation
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('access_denied', "Vous n'avez pas l'autorisation pour ajouter des catégories");
            return $this->render('gestions/edit.html.twig', ['post' => []]);
        }

        $form = $this->createForm(DeleteCategoriesType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            //check if catégorie dont have
            foreach ($form['name']->getData() as $category) {
                $category = $categoriesRepository->findOneByName($category->getName());
                if (count($category->getPosts()) > 0) {
                    $form['name']->addError(new FormError("Vous ne pouvez pas effacer la Catégorie \"{$category->getName()}\" qui contient des articles"));
                }
            }

            if (count($form->getErrors()) === 0) {
                foreach ($form['name']->getData() as $category) {
                    $em->remove($category);
                    $em->flush();
                    $this->addFlash('success', "Suppression Effectué pour la catégorie ;)");
                }
                return $this->redirectToRoute('app_delete_categories');
            }


        }

        return $this->render('gestions/delete-categories.html.twig', [
            "title" => "Supprimer  Une ou Des Catégorie(s)",
            'form' => $form->createView(),
        ]);

    }
}

