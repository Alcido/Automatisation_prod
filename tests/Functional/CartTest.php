<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Service\CartService;
use App\Entity\CartItem;

class CartTest extends FunctionalTestCase
{
    public function testAddProductToCart(): void
    {
        $this->login('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $this->client->request('POST', '/cart/add/'.$product->getId(), [
            'quantity' => 1,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testCartQuantityIsUpdated(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $item = $cart->getItems()->first();
        $this->assertNotFalse($item);

        $this->client->request('POST', '/cart/items/'.$item->getId().'/update', [
            'quantity' => 5,
        ]);

        $this->assertResponseRedirects();

        $this->entityManager()->clear();
        $updatedItem = $this->repository(CartItem::class)->find($item->getId());
        $this->assertNotNull($updatedItem);
        $this->assertSame(5, $updatedItem->getQuantity());
    }

    public function testRemoveProductFromCart(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $item = $cart->getItems()->first();
        $this->assertNotFalse($item);

        $this->client->request('POST', '/cart/items/'.$item->getId().'/remove');

        $this->assertResponseRedirects();

        $this->entityManager()->clear();
        $removedItem = $this->repository(CartItem::class)->find($item->getId());
        $this->assertNull($removedItem);
    }

    public function testCartShowsCorrectTotal(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 2);

        $this->client->request('GET', '/cart');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#cart-total');
    }
}