<?php

// src/Controller/PostsController.php

namespace App\Controller;

use App\Form\PostsType;
use App\Repository\PostsRepository;
use App\Services\PostFilesService;
use App\Services\Slugger;
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
    public function index(PostsRepository $postsRepository , Request $request): Response
    {

        $post = $postsRepository->findOneBySlug($request->get('slug')) ;


        return $this->render('posts/index.html.twig', [
            'post' => $post,
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
            'user' => $user,
            'nPage' => $nPage,
            'pagination' => $pagination,
        ]);
    }


    /**
     * Methode to post article
     * @param Request $request
     * @param PostFilesService $postFileService
     * @param Security $security
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('gestions/add', name: 'app_add_post')]
    public function add(
        Request                $request,
        PostFilesService       $postFileService,
        Security               $security,
        EntityManagerInterface $em,
        Slugger                $slugger,
    ): Response
    {

        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {

            //set slug with unique syntaxe
            $post->setSlug($slugger->checkSlug($form["slug"]->getData()));

            if ($form->isValid()) {
                // Get the currently  user
                $post->setUserId($security->getUser());

                try {
                    // processing the image
                    $imageFile = $form->get('image')->getData();
                    //crop adn save image
                    $filename = $postFileService->processFile($imageFile, 'POST');
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
        }


        return $this->render('gestions/add.html.twig',

            [
                'title' => "Ajouter un article",
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * Methode to edit article
     * @param Request $request
     * @param PostFilesService $postFileService
     * @param PostsRepository $postsRepository
     * @param Security $security
     * @param EntityManagerInterface $em
     * @param string $slug
     * @return Response
     */
    #[Route('/gestions/edit/{slug}', name: 'app_edit_post')]
    public function edit(
        Request                $request,
        PostFilesService       $postFileService,
        PostsRepository        $postsRepository,
        Security               $security,
        EntityManagerInterface $em,
        Slugger                $slugger,
        string                 $slug
    ): Response
    {
        // If a slug is provided, load the post; otherwise, throw exception
        $post = $postsRepository->findOneBySlug($slug);
        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        $user = $security->getUser();

        //check autorisation
        if (!$user || ($post->getUserId()->getId() !== $user->getId()
                && !in_array('ROLE_ADMIN', $user->getRoles()))) {
            $this->addFlash('access_denied', "Accès refusé");
            return $this->render('gestions/edit.html.twig', ['post' => []]);
        }

        $form = $this->createForm(PostsType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form['slug']->getData() !== $post->getSlug()) {
                //set slug with unique syntaxe
                $post->setSlug($slugger->checkSlug($form["slug"]->getData()));
            }

            try {
                // processing the image
                $imageFile = $form->get('image')->getData();

                if ($imageFile) {
                    //crop and save image only if a new image was uploaded
                    $filename = $postFileService->processFile($imageFile, 'POST');

                    $post->setImage($filename);
                }

                //update in db
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


    /**
     * @param Request $request
     * @param Security $security
     * @param EntityManagerInterface $em
     * @param string $slug
     * @return Response
     */
    #[Route('/gestions/delete/{slug}', name: "app_delete_post")]
    public function delete(Request                $request,
                           PostsRepository        $postsRepository,
                           Security               $security,
                           EntityManagerInterface $em,
                           string                 $slug): Response
    {

        $post = $postsRepository->findOneBySlug($slug);
        //if article not found
        if (!$post) {
            $this->addFlash('failed', 'cette article n\'existe pas ');
            return $this->redirectToRoute('app_gestions');
        }

        $user = $security->getUser();

        //check autorisation
        if (!$user || ($post->getUserId()->getId() !== $user->getId()
                && !in_array('ROLE_ADMIN', $user->getRoles()))) {
            $this->addFlash('denied', "Accès refusé");
            return $this->redirectToRoute('app_gestions');
        }

        $em->remove($post);
        $em->flush();


        $this->addFlash('success', "Article effacée avec success");
        return $this->redirectToRoute('app_gestions');

    }


}
