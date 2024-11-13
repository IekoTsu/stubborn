<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Service\CartService;
use App\Repository\SweatShirtsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

class PaymentController extends AbstractController
{
    private CartService $cartService;
    private EntityManagerInterface $entityManager;
    public function __construct(CartService $cartService, EntityManagerInterface $entityManager)
    {
        $this->cartService = $cartService;
        $this->entityManager = $entityManager;
    }

    #[Route('/payment', name: 'app_payment')]
    #[IsGranted('ROLE_USER')]
    public function stripCheckout(SweatShirtsRepository $sweatShirtsRepository): RedirectResponse
    {   
        $stripProducts = [];

        $cartProducts = $this->cartService->getCartItems($sweatShirtsRepository);

        foreach ($cartProducts as $product) {
            $cProduct = $product['product'];
            $stripProducts[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $cProduct->getPrice()*100,
                    'product_data' => [
                        'name' => $cProduct->getName(),
                    ],
                ],
                'quantity' => 1,
            ];
        }  

        if(empty($stripProducts)) {
            return $this->redirectToRoute('app_cart');
        }

        Stripe::setApiKey('sk_test_51PEF7pIsveLmPyFvpxpP4XOMHQeobKc9HrlwT5b5pMSntpXaCYAEmoRAHk9TJAZBOcipp1pAsrfjYtJPZCDrgaYx00eQRbju1B');

        $YOUR_DOMAIN = 'http://localhost:8000';

        $checkout_session = \Stripe\Checkout\Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [[
                $stripProducts
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/payment/success',
            'cancel_url' => $YOUR_DOMAIN . '/payment/cancel',
        ]);

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/payment/success', name: 'payment_success')]
    #[IsGranted('ROLE_USER')]
    public function success(SweatShirtsRepository $sweatShirtsRepository): Response 
    {
        // Retrieve cart items
        $cartProducts = $this->cartService->getCartItems($sweatShirtsRepository);
    
        // Loop through the cart items to update stock
        foreach ($cartProducts as $item) {
            $product = $item['product']; // This should be an instance of SweatShirts
            $quantity = 1;
            $size = $item['size']; // Assuming size is captured in the cart item (e.g., xs, s, m, l, xl)
    
            // Adjust the stock based on size
            switch ($size) {
                case 'xs':
                    $product->setXsStock(max(0, $product->getXsStock() - $quantity));
                    break;
                case 's':
                    $product->setSStock(max(0, $product->getSStock() - $quantity));
                    break;
                case 'm':
                    $product->setMStock(max(0, $product->getMStock() - $quantity));
                    break;
                case 'l':
                    $product->setLStock(max(0, $product->getLStock() - $quantity));
                    break;
                case 'xl':
                    $product->setXlStock(max(0, $product->getXlStock() - $quantity));
                    break;
            }
    
            // Persist changes to the database
            $this->entityManager->persist($product);
        }
    
        // Save changes to the database
        $this->entityManager->flush();
    
        // Clear the cart after successful payment
        $this->cartService->clearCart();
    
        // Add a success flash message
        $this->addFlash('success', 'Paiement réussi ! Votre transaction a été effectuée avec succès. Un reçu de paiement vous a été envoyé par e-mail. Merci pour votre confiance !');
    
        // Redirect to the cart page
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    #[IsGranted('ROLE_USER')]
    public function cancel(): Response {

         $this->addFlash('success', "Paiement annulé. Votre transaction n'a pas été complétée.");
        return $this->redirectToRoute('app_cart');
    }
}

