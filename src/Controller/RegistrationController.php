<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Services\PostFilesService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $em,
        TokenStorageInterface       $tokenStorage,
        EventDispatcherInterface    $eventDispatcher,
        SessionInterface            $session,
        PostFilesService            $postFileService,

    ): Response
    {

        //if user is connected redirect him
        if ($this->getUser())
            return $this->redirectToRoute('app_home');

        $user = new User();
        $user->setRoles(["ROLE_AUTHOR"]);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

                // Automatic login
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);
                $session->set('_security_main', serialize($token));

                //dispatch
                $event = new InteractiveLoginEvent($request, $token);
                $eventDispatcher->dispatch($event);
                //redirect user  after login
                return $this->redirectToRoute('_app_home');

            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette adresse e-mail est déjà utilisée. Veuillez en choisir une autre.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur technique est survenue, merci de réessayer ');
                dd($e->getMessage());
            }
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
