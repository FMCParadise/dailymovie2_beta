<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{
    #[Route('/categories/{slug}', name: 'app_categories')]
    public function index(Categories  $categories, PostsRepository $postsRepository ): Response
    {
       $posts = $categories -> getPosts();
        return $this->render('categories/index.html.twig', [
            'posts' => $posts,
            'slug' => $categories->getSlug()
        ]);
    }
}
