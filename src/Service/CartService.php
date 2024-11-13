<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\SweatShirtsRepository;

class CartService {
    private $session;

    public function __construct(RequestStack $requestStack) {
        $this->session = $requestStack->getSession();
    }

    public function getCartItems(SweatShirtsRepository $sweatShirtsRepository): array
    {
        $cart = $this->session->get('cart', []);
        $cartItems = [];

        foreach ($cart as $item) {
            $product = $sweatShirtsRepository->find($item['id']);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'size' => $item['size'],
                ];
            }
        }

        return $cartItems;
    }

    public function addProduct(int $productId, string $size) {

        $cart = $this->session->get('cart', []);

        $cart[] = [
            'id' => $productId,
            'size' => $size,
        ];

        $this->session->set('cart', $cart);
    }

    public function removeProduct(int $productId, string $size ) {
        $cart = $this->session->get('cart', []);

        foreach ($cart as $index => $item){
            if ($item['id'] === $productId && $item['size'] === $size){
                unset($cart[$index]);
                break;
            }
        }

        $this->session->set('cart', array_values($cart));
    }

    public function getCart(): array {
        return $this->session->get('cart', []);
    }

    public function clearCart(){
        $this->session->remove('cart');
    }

}