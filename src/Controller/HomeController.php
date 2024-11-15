<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SweatShirtsRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SweatShirtsRepository $sweatShirtsRepository): Response
    {   
        $featuredSweatshirts = $sweatShirtsRepository->findFeaturedSweatshirts();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'featuredSweatshirts' => $featuredSweatshirts,
        ]);
    }
}
