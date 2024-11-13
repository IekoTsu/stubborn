<?php

namespace App\Controller;

use App\Repository\SweatShirtsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    #[IsGranted('ROLE_USER')]
    public function index(SweatShirtsRepository $sweatShirtsRepository): Response
    {
        $products = $sweatShirtsRepository->findAllProducts();


        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
        ]);
    }
}
