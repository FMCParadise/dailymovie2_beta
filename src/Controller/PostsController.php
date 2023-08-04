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


    /**
     * Methode to post article
     * @param Request $request
     * @param PostFilesService $postFileService
     * @param Security $security
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('gestions/add', name: 'app_add_post')]
    public function add(Request                $request,
                        PostFilesService       $postFileService,
                        Security               $security,
                        EntityManagerInterface $em,
                        Slugger                $slugger,): Response
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


        }


        return $this->render('gestions/add-categories.html.twig',
            [
                'form' => $form->createView(),
            ]);
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
    public function edit(Request                $request,
                         PostFilesService       $postFileService,
                         PostsRepository        $postsRepository,
                         Security               $security,
                         EntityManagerInterface $em,
                         Slugger                $slugger,
                         string                 $slug): Response
    {
        // If a slug is provided, load the post; otherwise, throw exception
        $post = $postsRepository->findOneBy(['slug' => $slug]);
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
                    $filename = $postFileService->processFile($imageFile);
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

        return $this->render('gestions/add-categories.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }

}
