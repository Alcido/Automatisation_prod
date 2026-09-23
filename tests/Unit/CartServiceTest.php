<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $this->service = new CartService($em);
    }

    public function testEmptyCartReturnsZero(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $this->assertSame(0.0, $this->service->getTotal($cart));
    }

    public function testSingleItemReturnsCorrectTotal(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(25.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(25.00);
        $cart->addItem($item);

        $this->assertSame(25.00, $this->service->getTotal($cart));
    }

    public function testMultipleItems(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(10.00);

        $item = new CartItem($product);
        $item->setQuantity(1);
        $item->setUnitPrice(10.00);
        $cart->addItem($item);

        $product2 = new Product();
        $product2->setName('Catan2');
        $product2->setPrice(10.00);

        $item2 = new CartItem($product2);
        $item2->setQuantity(1);
        $item2->setUnitPrice(10.00);
        $cart->addItem($item2);

        $product3 = new Product();
        $product3->setName('Catan3');
        $product3->setPrice(20.00);

        $item3 = new CartItem($product3);
        $item3->setQuantity(1);
        $item3->setUnitPrice(20.00);
        $cart->addItem($item3);

        $this->assertSame(40.00, $this->service->getTotal($cart));
    }

    public function testQuantityMultiplier(): void
    {
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('Catan');
        $product->setPrice(10.00);

        $item = new CartItem($product);
        $item->setQuantity(3);
        $item->setUnitPrice(10.00);
        $cart->addItem($item);

        $this->assertSame(30.00, $this->service->getTotal($cart));
    }
}
