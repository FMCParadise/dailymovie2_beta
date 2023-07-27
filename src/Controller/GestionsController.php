<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Posts;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


class GestionsController extends AbstractController
{

    #[Route('/gestions/{nPage?}', name: 'app_gestions', requirements: ["nPage" => "\d*"])]
    public function index(PostsRepository $postsRepository, Security $security, Request $request): Response
    {
        //title
        $title = "Liste des articles de tous les Authors";

        $user = $security->getUser();
        $criteria = [];
        //if user isn't admin
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $criteria['user'] = $user;
            $title = "Liste de vos articles";
        }

                $limit = 8; // post par page
        $nPage = $request->get('nPage', 1);
        $nPage = max($nPage, 1);
        $offset = ($nPage - 1) * $limit;
        $posts = $postsRepository->findBy($criteria, ["id" => "DESC"], $limit, $offset);
        $totalPosts = $postsRepository->count($criteria); // obtenir le nombre total de posts spécifiques à l'utilisateur
        $totalPages = ceil($totalPosts / $limit); // calculer le nombre total de pages

        // Générer la pagination
        $pagination = range(1, $totalPages);

        return $this->render('gestions/index.html.twig', [
            'posts' => $posts,
            'title' => $title,
            'nPage' => $nPage,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/gestions/edit/{slug}', name: 'app_edit_post')]
    public function edit(Posts $posts, Security $security): Response
    {
        // récupérer l'utilisateur actuellement connecté
        $user = $security->getUser();

        //check autorisation
        if (!$user || ($posts->getUserId()->getId() !== $user->getId()
                && !in_array('ROLE_ADMIN', $user->getRoles()))) {
            $this->addFlash('access_denied', "Accès refusé");
            return $this->render('gestions/edit.html.twig', ['post' => []]);
        }


        return $this->render('gestions/edit.html.twig', [
            'post' => $posts
        ]);
    }

}
