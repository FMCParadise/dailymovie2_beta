<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Posts;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\CategoriesRepository;
use App\Repository\PostsRepository;
use App\Repository\UserRepository;
use App\Services\PostFilesService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;



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


    #[Route('/gestions/users', name: 'app_gestions_user')]
    public function listUser(Security       $security,
                             Request        $request,
                             UserRepository $userRepository
    ): Response
    {

        $user = $security->getUser();

        //check autorisation
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('denied', "Accès refusé");
            return $this->redirectToRoute('app_gestions');
        }
        $users = $userRepository->findBy([], ['created_at' => 'DESC']);


        return $this->render('gestions/users.html.twig', [
            "title" => "Liste des Auteurs",
            "users" => $users
        ]);


    }


    #[Route('/gestions/add-user', name: 'app_gestions_add_user')]
    public function addUser(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $em,
        PostFilesService            $postFileService,
        ParameterBagInterface       $params,
        Security $security

    ): Response
    {


        $userAdmin = $security->getUser();

        //check autorisation
        if (!$userAdmin || !in_array('ROLE_ADMIN', $userAdmin->getRoles())) {
            $this->addFlash('denied', "Accès refusé");
            return $this->redirectToRoute('app_gestions');
        }


        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $roles = $form['roles']->getData();
            $allowedRoles = [];
            foreach ($params->get('roles_app') as $roleArray) {
                $allowedRoles[] = reset($roleArray);
            }

            if (empty($roles) || !in_array($roles, $allowedRoles)) {
                $form['roles']->addError(new FormError('Veuillez sélectionner un rôle pour cet utilisateur'));
            }
            $role = [];
            $role[] = $roles;
            $user->setRoles($role);

            if ($form->isValid()) {
                // encode the plain password
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                try {

                    // processing the image
                    $imageFile = $form->get('image')->getData();
                    //crop adn save image
                    $filename = $postFileService->processFile($imageFile, "AVATAR");
                    $user->setAvatar($filename);

                    $em->persist($user);
                    $em->flush();

                    //redirect user  after login
                    return $this->redirectToRoute('app_gestions_user');

                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée. Veuillez en choisir une autre.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur technique est survenue, merci de réessayer ');
                    dd($e->getMessage());
                }
            }


        }

        return $this->render('registration/index.html.twig', [
            'title' => "Ajouter Un Utilisateur",
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/gestions/edit-user/{id}', name: 'app_gestions_edit_user')]
    public function editUser(
        User                        $user,
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $em,
        PostFilesService            $postFileService,
        ParameterBagInterface       $params,
        Security $security
    ): Response
    {



        $userAdmin = $security->getUser();

        //check autorisation
        if (!$userAdmin || !in_array('ROLE_ADMIN', $userAdmin->getRoles())) {
            $this->addFlash('denied', "Accès refusé");
            return $this->redirectToRoute('app_gestions');
        }




        $defaultRoles = $user->getRoles();
        $defaultRoleValue = null;
        foreach ($params->get('roles_app') as $k => $ARRoles) {
            foreach ($ARRoles as $key => $role) {
                if (in_array($role, $defaultRoles)) {
                    $defaultRoleValue = $role;
                    break;
                }
            }
        }

        $form = $this->createForm(RegistrationFormType::class, $user, [
            "edit" => true,
            "defaultRole" => $defaultRoleValue
        ]);


        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $roles = $form['roles']->getData();
            $allowedRoles = [];
            foreach ($params->get('roles_app') as $roleArray) {
                $allowedRoles[] = reset($roleArray);
            }

            if (empty($roles) || !in_array($roles, $allowedRoles)) {
                $form['roles']->addError(new FormError('Veuillez sélectionner un rôle pour cet utilisateur'));
            }
            $role = [];
            $role[] = $roles;
            $user->setRoles($role);

            if ($form->isValid()) {
                // encode the plain password
                $plainPassword = $form->get('plainPassword')->getData();

                if (!empty($plainPassword)) {
                    $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                }


                try {
                    // processing the image
                    $imageFile = $form->get('image')->getData();

                    if ($imageFile) {
                        //crop adn save image
                        $filename = $postFileService->processFile($imageFile, "AVATAR");
                        $user->setAvatar($filename);
                    }
                    $em->persist($user);
                    $em->flush();

                    //redirect user  after login
                    return $this->redirectToRoute('app_gestions_user');

                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée. Veuillez en choisir une autre.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur technique est survenue, merci de réessayer ');
                    dd($e->getMessage());
                }
            }


        }

        return $this->render('registration/index.html.twig', [
            'title' => "Modifier la fiche de l'utilisateur",
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/gestions/delete-user/{id}', name: 'app_gestions_delete_user')]
    public function deleteUser(User                   $user,
                               Security               $security,
                               EntityManagerInterface $em,
                               PostsRepository $postsRepository ,

    ): Response
    {


        $CurrentuUser = $security->getUser();

        //check autorisation
        if (!$CurrentuUser && !in_array('ROLE_ADMIN', $CurrentuUser->getRoles())) {
            $this->addFlash('failed', "Accès refusé");
            return $this->redirectToRoute('app_gestions_user');
        }


        //if article not found
        if (!$user) {
            $this->addFlash('failed', 'cet Utilisateur n\'existe pas ');
            return $this->redirectToRoute('app_gestions');
        }

        //check if user had posts
        $posts = $postsRepository->findByUser($user) ;
        if(count($posts) > 0){
            $this->addFlash('failed', "Vous ne pouvez pas  supprimer cette utilisateur car il détiens des articles");
            return $this->redirectToRoute('app_gestions_user');
        }


        $em->remove($user);
        $em->flush();

        $this->addFlash('success', "Utilisateur effacée avec success");
        return $this->redirectToRoute('app_gestions_user');
    }


}
