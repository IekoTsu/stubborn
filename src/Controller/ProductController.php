<?php

namespace App\Controller;

use App\Entity\SweatShirts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_product')]
    #[IsGranted('ROLE_USER')]
    public function index(SweatShirts $sweatShirts): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $sweatShirts,
        ]);
    }
}
