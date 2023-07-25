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
    #[Route('/home/{nPage?}', name: 'app_home', requirements: ["nPage" => "\d*"])]
    public function index(PostsRepository $postsRepository, Request $request): Response
    {

        $limit = 7; // post par page
        $nPage = $request->get('nPage', 1);
        $nPage = max($nPage, 1);
        $offset = ($nPage - 1) * $limit;

        $posts = $postsRepository->findBy([], ["id" => "DESC"], $limit, $offset);

        $totalPosts = $postsRepository->count([]); // obtenir le nombre total de posts
        $totalPages = ceil($totalPosts / $limit); // calculer le nombre total de pages

        // Générer la pagination
        $pagination = range(1, $totalPages);

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'nPage' => $nPage,
            'pagination' => $pagination,
        ]);
    }
}
