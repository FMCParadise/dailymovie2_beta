<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();
        $user->setCreatedAt(new \DateTimeImmutable()) ;
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            try {
                $em->persist($user);
                $em->flush();

                // Automatic login
                // $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                // $this->get('security.token_storage')->setToken($token);
                // $this->get('session')->set('_security_main', serialize($token));
                // dispatch event
                // $event = new InteractiveLoginEvent($request, $token);
                // $this->get('event_dispatcher')->dispatch($event);

                return $this->redirectToRoute('app_homepage');
            } catch (\Exception $e) {

                dd($e->getMessage()) ;
                // Add flash message for exception
//                $this->addFlash('error', 'Une erreur est survenue pendant l\'enregistrement : ' . $e->getMessage());
            }
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
