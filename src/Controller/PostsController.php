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


    #[Route("/posts/user/{id}", name: 'app_posts_user')]
    public function postsByUser(User $user, PostsRepository $postsRepository, Request $request): Response
    {

        //$posts = $postsRepository->findByUser($userId);
        $limit = 8; // post par page
        $nPage = $request->get('nPage', 1);
        $nPage = max($nPage, 1);
        $offset = ($nPage - 1) * $limit;

        $posts = $postsRepository->findBy([
            "user" => $user
        ], ["id" => "DESC"], $limit, $offset);

        $totalPosts = $postsRepository->count(["user" => $user]); // obtenir le nombre total de posts
        $totalPages = ceil($totalPosts / $limit); // calculer le nombre total de pages

        // Générer la pagination
        $pagination = range(1, $totalPages);
        return $this->render('posts/posts.html.twig', [
            'posts' => $posts,
            'nameUser' => $user->getName(),
            'nPage' => $nPage,
            'pagination' => $pagination,
        ]);
    }

    #[Route('gestions/add', name: 'app_add_post')]
    public function add(
        Request                $request,
        PostFilesService       $postFileService,
        Security $security,
        EntityManagerInterface $em
    ): Response {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // Get the currently  user
            $post->setUserId($security->getUser());

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


        return $this->render(
            'gestions/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
