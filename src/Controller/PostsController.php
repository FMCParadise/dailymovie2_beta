<?php

// src/Controller/PostsController.php

namespace App\Controller;

use App\Form\PostsType;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Posts;
use App\Repository\PostsRepository;
use App\Entity\User;

class PostsController extends AbstractController
{
    #[Route('/posts/{slug}', name: 'app_posts')]
    public function index(Posts $posts): Response
    {
        return $this->render('posts/index.html.twig', [
            'post' => $posts,
        ]);
    }


    #[Route("/posts/user/{userId}", name: 'app_posts_user')]
    public function postsByUser(int $userId, ManagerRegistry $registry): Response
    {
        $user = $registry->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Auteur non trouvé pour l\'ID ' . $userId);
        }

        $posts = $registry->getRepository(Posts::class)->findBy(['author' => $user]);

        return $this->render('posts_by_user.html.twig', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }


    #[Route('gestions/add', name: 'app_add_post')]
    public function add(Request $request , CategoriesRepository $categories): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);



//        if ($form->isSubmitted()) {
//
//            dd( $form->getErrors());
//
//
//
////            $entityManager = $this->getDoctrine()->getManager();
////            $entityManager->persist($post);
////            $entityManager->flush();
////            return $this->redirectToRoute('app_gestions');
//        }

        return $this->render('gestions/add.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories->findAll()
        ]);
    }





}
