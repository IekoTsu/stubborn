<?php

namespace App\Controller;

use App\Service\CartService;
use App\Entity\SweatShirts;
use App\Repository\SweatShirtsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/cart', name: 'app_cart')]
    #[IsGranted('ROLE_USER')]
    public function index(SweatShirtsRepository $sweatShirtsRepository): Response
    {
        $cartItems = $this->cartService->getCartItems($sweatShirtsRepository);

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    #[IsGranted('ROLE_USER')]
    public function addToCart(int $id, Request $request): Response {

        $size = $request->query->get('size');

        if (!$size){
            $this->addFlash('error', "Veuillez sélectionner une taille avant d'ajouter au panier.");
            return $this->redirectToRoute('app_product', ['id' => $id]);
        }

        $this->cartService->addProduct($id, $size);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}/{size}', name: 'cart_remove')]
    #[IsGranted('ROLE_USER')]
    public function removeProductFromCart(int $id, string $size): Response {

        $this->cartService->removeProduct($id, $size);

        return $this->redirectToRoute('app_cart');
    }
}
