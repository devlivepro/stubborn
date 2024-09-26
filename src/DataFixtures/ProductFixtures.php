namespace App\DataFixtures;

<?php

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des produits à insérer (nom, prix, stock, image, quantité)
        $products = [
            ['name' => 'Blackbelt', 'price' => 29.90, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => true, 'image' => 'img/1.jpeg', 'quantity' => 10],
            ['name' => 'BlueBelt', 'price' => 29.90, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => false, 'image' => 'img/2.jpeg', 'quantity' => 10],
            ['name' => 'Street', 'price' => 34.50, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => false, 'image' => 'img/3.jpeg', 'quantity' => 10],
            ['name' => 'Pokeball', 'price' => 45.00, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => true, 'image' => 'img/4.jpeg', 'quantity' => 10],
            ['name' => 'PinkLady', 'price' => 29.90, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => false, 'image' => 'img/5.jpeg', 'quantity' => 10],
            ['name' => 'Snow', 'price' => 32.00, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => false, 'image' => 'img/6.jpeg', 'quantity' => 10],
            ['name' => 'Greyback', 'price' => 28.50, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => false, 'image' => 'img/7.jpeg', 'quantity' => 10],
            ['name' => 'BlueCloud', 'price' => 45.00, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => true, 'image' => 'img/8.jpeg', 'quantity' => 10],
            ['name' => 'BornInUsa', 'price' => 59.90, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => true, 'image' => 'img/9.jpeg', 'quantity' => 10],
            ['name' => 'GreenSchool', 'price' => 42.20, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => false, 'image' => 'img/10.jpeg', 'quantity' => 10],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setPrice($data['price']);
            $product->setStock($data['stock']);
            $product->setQuantity($data['quantity']);
            $product->setHighlighted($data['highlighted']);
            $product->setImage($data['image']);

            $manager->persist($product);
        }

        // Sauvegarde des produits dans la base de données
        $manager->flush();
    }
}
