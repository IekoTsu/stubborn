<?php

namespace App\Tests\Controller;

use App\Controller\PaymentController;
use App\Entity\SweatShirts;
use App\Entity\User;
use App\Repository\SweatShirtsRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentControllerTest extends WebTestCase
{
    private CartService $cartService;
    private EntityManagerInterface $entityManager;
    private SweatShirtsRepository $sweatShirtsRepository;
    private KernelBrowser $client;

    /**
     * @runInSeparateProcess
     */
    protected function setUp(): void
    {   
        

        // Boot the client with the kernel to avoid starting sessions too early
        $this->client = static::createClient();
        
        // Get container services only after booting the client to avoid pre-initialization
        $container = $this->client->getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->cartService = $this->createMock(CartService::class);
        $this->sweatShirtsRepository = $this->createMock(SweatShirtsRepository::class);
    }

    /**
     * @runInSeparateProcess
     */
    private function logInUser(): void
    {       
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'testuser']);

    if(!$existingUser){
        // Create the User entity
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setUsername('testuser'); 
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('password');
        $user->setDeliveryAddress('Fake address');

        // Persist the user entity to the database, so Doctrine auto-generates the ID
        
        $this->entityManager->persist($user);
        $this->entityManager->flush(); // Doctrine will now assign the ID
    } else {
        $user = $existingUser;
    }

    // Log the user in
    $this->client->loginUser($user);
    }


    /**
     * @runInSeparateProcess
     */
    public function testStripeCheckoutRedirectsToSessionUrl(): void
    {
        $this->logInUser();
        $cartService = $this->createMock(CartService::class);
        // Mock the cartService to return specific products
        $product = (new SweatShirts())
            ->setName('Test Sweatshirt')
            ->setPrice(50.0)
            ->setXsStock(10)
            ->setSStock(10)
            ->setMStock(10)
            ->setLStock(10)
            ->setXlStock(10); 

        // Set up the mock CartService response
        $cartService->method('getCartItems')->willReturn([['product' => $product, 'size' => 'm']]);

        $crawler = $this->client->request('GET', '/payment'); 
        $response = $this->client->getResponse();

        // Assert the redirect response to Stripe checkout session
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSuccessUpdatesStockAndClearsCart(): void
    {
        $this->logInUser();
        $cartService = $this->createMock(CartService::class);

        // Set up the mock CartService to return specific products
        $product = (new SweatShirts())
            ->setName('Test Sweatshirt')
            ->setPrice(50.0)
            ->setXsStock(10)
            ->setSStock(10)
            ->setMStock(10)
            ->setLStock(10)
            ->setXlStock(10);

        $cartService->method('getCartItems')->willReturn([['product' => $product, 'size' => 'm']]);

        // Mock the clearCart method
        $cartService->method('clearCart')->willReturn([]);

        // Make the request to the controller (Symfony will handle DI)
        $crawler = $this->client->request('GET', '/payment/success');  // Adjust the route if needed
        $response = $this->client->getResponse();

        // Assert the response to confirm cart is cleared and stock is updated
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/cart', $response->headers->get('location'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCancelAddsFlashMessageAndRedirects(): void
    {
        $this->logInUser();

        // Request the cancel route
        $this->client->request('GET', '/payment/cancel');
        $response = $this->client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/cart', $response->headers->get('Location'));
        
        // Follow the redirect
        $this->client->followRedirect();
        
        // Check the flash message
        $crawler = $this->client->getCrawler();
        $flashMessage = $crawler->filter('.alert-success')->text();
    
        $this->assertStringContainsString("Paiement annulé. Votre transaction n'a pas été complétée.", $flashMessage);
    }
}
