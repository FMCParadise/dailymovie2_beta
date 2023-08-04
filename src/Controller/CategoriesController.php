<?php

namespace App\Controller;

use App\Entity\Categories;

use App\Entity\Posts;
use App\Form\CategoriesType;
use App\Form\PostsType;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        // dd($posts);
        return $this->render('categories/index.html.twig', [
            'posts' => $posts,
            'nPage' => $nPage,
            'slug' => $categories->getSlug(),
            'pagination' => [],

        ]);
    }

    #[Route("/gestions/add-categories", name: "app_add_categories")]
    public function add(Request $request): Response
    {
        $categories = new Categories();
        $form = $this->createForm(CategoriesType::class, $categories);
        $form->handleRequest($request);


        return $this->render('gestions/add-categories.html.twig', [
            "title" => "add categorie",
            'form' => $form->createView(),
        ]);
    }

}
