<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setName('Produit Test');
        
        // Utilisez un entier si vous stockez le prix en centimes
        $product->setPrice(1000); // Prix de 1000 centimes (10.00 EUR)

        // Stock pour chaque taille [XS, S, M, L, XL]
        $stock = [10, 10, 10, 10, 10]; 
        $product->setStock($stock);

        // Calculer automatiquement la quantité totale
        $product->setQuantity(array_sum($stock)); 

        // Définir le chemin de l'image
        $product->setImagePath('assets/img/default.jpeg');

        // Persist le produit dans la base de données
        $manager->persist($product);
        
        // Effectuer l'enregistrement en base de données
        $manager->flush();
    }
}