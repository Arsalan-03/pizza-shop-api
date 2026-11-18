<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Fixtures;

use App\Product\Domain\Model\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Dev/test sample products. Bundle is registered only for dev/test,
 * so this class is never required in production.
 */
final class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            new Product(
                name: 'Margherita',
                price: 45000,
                weight: 450,
                category: 'pizza',
                description: 'Tomato sauce, mozzarella, basil',
            ),
            new Product(
                name: 'Pepperoni',
                price: 55000,
                weight: 500,
                category: 'pizza',
                description: 'Tomato sauce, mozzarella, pepperoni',
            ),
            new Product(
                name: 'Cola 0.5L',
                price: 12000,
                weight: 500,
                category: 'drink',
                description: null,
            ),
        ];

        foreach ($products as $product) {
            $manager->persist($product);
        }

        $manager->flush();
    }
}
