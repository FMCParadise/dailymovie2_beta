<?php

// src/Controller/PostsController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Repository\PostsRepository;

class PostsController extends AbstractController
{
    #[Route('/posts/{slug}', name: 'app_posts')]
    public function index(PostsRepository $posts): Response
    {

        $post = $posts->findBySlug('uncharted-film-6');

        return $this->render('posts/index.html.twig', [
            'post' => $post,
        ]);
    }
}
