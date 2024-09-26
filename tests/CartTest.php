<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class CartTest extends WebTestCase
{
    public function testAddProductToCart(): void
    {
        // Crée un client de test
        $client = static::createClient();

        // Récupérer l'EntityManager pour obtenir l'utilisateur de test
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneByEmail('johndoe@example.com');

        // Vérifie que l'utilisateur existe
        $this->assertNotNull($user, "L'utilisateur de test est introuvable.");

        // Connecte l'utilisateur de test
        $client->loginUser($user);

        $client->request('POST', '/cart/add/1', [
            'size' => 'M',
        ]);
        $this->assertResponseStatusCodeSame(302); // Vérifie que l'ajout au panier redirige correctement
        $client->followRedirect(); // Suivre la redirection vers la page du panier

        // Récupérer la session après la redirection
        $session = $client->getRequest()->getSession();
        $cart = $session->get('cart');

        // Vérifie que le panier est bien initialisé et que le produit y est ajouté
        $this->assertNotNull($cart, "Le panier est vide après l'ajout du produit.");
        $this->assertArrayHasKey(1, $cart, "Le produit n'est pas présent dans le panier.");
    }
}