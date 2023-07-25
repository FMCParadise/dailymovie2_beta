<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionsController extends AbstractController
{
    #[Route('/gestions', name: 'app_gestions')]
    public function index(): Response
    {
        return $this->render('gestions/index.html.twig', [
            'controller_name' => 'GestionsController',
        ]);
    }

    #[Route('/gestions/edit/{slug}' , name:'app_edit_post')]
    public function edit() : Response {

    }
}
