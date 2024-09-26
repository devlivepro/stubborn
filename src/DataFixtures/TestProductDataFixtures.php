<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestProductDataFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des produits à insérer (nom, prix, stock, image, quantité)
        $products = [
            ['name' => 'Produit Test', 'price' => 58.90, 'stock' => [2, 2, 2, 2, 2], 'highlighted' => true, 'image' => 'img/default.jpeg', 'quantity' => 10],
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