<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use App\Service\PromotionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $service;

    protected function setUp(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $promotionService = new PromotionService();
        $this->service = new CartService($em, $promotionService);
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

    public function testPromotionalPriceIsUsed(): void
    {
        // On dit dans le td qu'un cartService a besoin d'un PromotionService pour calculer le prix total d'un panier.
        // Donc on va créer un nouveau service avec un promotionService.

        // on reprend la logique des tests précédents pour le produit
        $user = $this->createStub(User::class);
        $cart = new Cart($user);

        $product = new Product();
        $product->setName('ProduitTest');
        $product->setPrice(50.00);
        $product->setPromoPrice(35.00);
        $product->setPromoStartsAt(new \DateTimeImmutable('2026-01-01'));
        $product->setPromoEndsAt(new \DateTimeImmutable('2099-01-01'));
        $product->setStock(10);

        $this->service->addProduct($cart, $product, 2);

        $this->assertSame(70.00, $this->service->getTotal($cart));
    }
}
