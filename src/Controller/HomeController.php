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
        $posts = $postsRepository->findBy([] , ["id"=>"DESC"] , 7, $offset) ;
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
