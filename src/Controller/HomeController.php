<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class HomeController extends AbstractController
{
    #[Route('/', name: '_app_home')]
    #[Route('/home/{offset?}', name: 'app_home', requirements: ["offset" => "\d*"])]
    public function index(PostsRepository $postsRepository, Request $request): Response
    {
        $offset = $request->get('offset', 0);
        $limit = 7; // posts par page
        $posts = $postsRepository->findBy([], ["id" => "DESC"], $limit, $offset);

        $totalPosts = $postsRepository->count([]); // obtenir le nombre total de posts
        $totalPages = ceil($totalPosts / $limit); // calculer le nombre total de pages

        // Générer la pagination
        $pagination = range(1, $totalPages);

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'nPage' => $offset === 0 ? 1 : $offset,
            'pagination' => $pagination,
        ]);
    }
}
