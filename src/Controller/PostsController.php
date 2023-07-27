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

class PostsController extends AbstractController
{
    #[Route('/posts/{slug}', name: 'app_posts')]
    public function index(Posts $posts): Response
    {
        return $this->render('posts/index.html.twig', [
            'post' => $posts,
        ]);
    }

    #[Route('gestions/add', name: 'app_add_post')]
    public function add(Request $request , CategoriesRepository $categories): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);



        if ($form->isSubmitted()) {

            dd( $form->getErrors());



//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($post);
//            $entityManager->flush();
//            return $this->redirectToRoute('app_gestions');
        }

        return $this->render('gestions/add.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories->findAll()
        ]);
    }
}
