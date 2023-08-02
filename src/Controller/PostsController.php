<?php

// src/Controller/PostsController.php

namespace App\Controller;

use App\Form\PostsType;
use App\Repository\PostsRepository;
use App\Services\PostFilesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Posts;
use Symfony\Component\Security\Core\Security;

class PostsController extends AbstractController
{
    #[Route('/posts/{slug}', name: 'app_posts')]
    public function index(Posts $posts): Response
    {
        return $this->render('posts/index.html.twig', [
            'post' => $posts,
        ]);
    }


    #[Route("/posts/user/{user}", name: 'app_posts_user')]
    public function postsByUser(Posts $posts): Response
    {


    }

    #[Route('gestions/add', name: 'app_add_post')]
    public function add(Request                $request,
                        PostFilesService       $postFileService,
                        Security $security,
                        EntityManagerInterface $em): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
                // Get the currently  user
                $post->setUserId($security->getUser()) ;

                try {
                    // processing the image
                    $imageFile = $form->get('image')->getData();
                    //crop adn save image
                    $filename = $postFileService->processFile($imageFile);
                    $post->setImage($filename);

                    //insert in db
                    $em->persist($post);
                    $em->flush();
                    //redirect to
                    return $this->redirectToRoute('app_gestions');

                } catch (FileException $e) {
                    $form['image']->addError(new FormError($e->getMessage()));
                }

        }


        return $this->render('gestions/add.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }


}
