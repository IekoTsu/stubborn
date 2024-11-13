<?php

namespace App\Tests\Service;

use App\Repository\SweatShirtsRepository;
use App\Service\CartService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use App\Entity\SweatShirts;

class CartServiceTest extends TestCase
{
    private $cartService;
    private $session;
    private $sweatShirtsRepository;

    protected function setUp(): void
    {
        // Create a mock session with mock storage
        $session = new Session(new MockArraySessionStorage());

        // Create a new Request and assign the session to it
        $request = new Request();
        $request->setSession($session);

        // Create a RequestStack and set the session
        $requestStack = new RequestStack();
        $requestStack->push($request);
        
        // Mock SweatShirtsRepository
        $sweatShirtsRepository = $this->createMock(SweatShirtsRepository::class);
        
        $this->session = $session;
        $this->cartService = new CartService($requestStack);
        $this->sweatShirtsRepository = $sweatShirtsRepository;
    }

    public function testAddProduct(): void
    {
        // Add a product to the cart
        $this->cartService->addProduct(1, 'M');
        
        // Assert that the cart has the product
        $cart = $this->cartService->getCart();
        $this->assertCount(1, $cart);
        $this->assertEquals(1, $cart[0]['id']);
        $this->assertEquals('M', $cart[0]['size']);
    }

    public function testGetCartItems(): void
    {
        // Create a mock product
        $mockProduct = new SweatShirts();
        $mockProduct->setName('Test Sweatshirt');

        // Mock the find method to return the mock product
        $this->sweatShirtsRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mockProduct);

        // Add a product to the cart
        $this->cartService->addProduct(1, 'M');

        // Get cart items
        $cartItems = $this->cartService->getCartItems($this->sweatShirtsRepository);

        // Assert that cart items are correct
        $this->assertCount(1, $cartItems);
        $this->assertEquals($mockProduct, $cartItems[0]['product']);
        $this->assertEquals('M', $cartItems[0]['size']);
    }

    public function testRemoveProduct(): void
    {
        // Add products to the cart
        $this->cartService->addProduct(1, 'M');
        $this->cartService->addProduct(2, 'L');
        
        // Remove one product
        $this->cartService->removeProduct(1, 'M');
        
        // Assert that only one product remains
        $cart = $this->cartService->getCart();
        $this->assertCount(1, $cart);
        $this->assertEquals(2, $cart[0]['id']);
        $this->assertEquals('L', $cart[0]['size']);
    }

    public function testClearCart(): void
    {
        // Add a product to the cart
        $this->cartService->addProduct(1, 'M');
        
        // Clear the cart
        $this->cartService->clearCart();
        
        // Assert that the cart is empty
        $cart = $this->cartService->getCart();
        $this->assertEmpty($cart);
    }
}
